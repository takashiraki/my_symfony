<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

#[AsCommand(
    name: 'app:migrate:sqlite-to-mysql',
    description: '旧SQLiteのデータを現行MySQLへコピーする（スキーマはマイグレーション済み前提）',
)]
final class SqliteToMysqlCommand extends Command
{
    private const BATCH_SIZE = 500;

    /**
     * マイグレーション管理テーブルと非同期メッセージのキューはコピー対象外
     */
    private const SKIP_TABLES = ['doctrine_migration_versions', 'messenger_messages'];

    public function __construct(
        #[Autowire(service: 'doctrine.dbal.legacy_connection')]
        private readonly Connection $sqlite,
        #[Autowire(service: 'doctrine.dbal.default_connection')]
        private readonly Connection $mysql,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, '実際には書き込まず、コピー予定の件数だけ表示する');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $io = new SymfonyStyle($input, $output);

        // 両方に存在するテーブルだけを対象にする
        $tables = array_values(array_intersect(
            $this->mysql->createSchemaManager()->listTableNames(),
            $this->sqlite->createSchemaManager()->listTableNames(),
        ));
        $tables = array_diff($tables, self::SKIP_TABLES);

        if ([] === $tables) {
            $io->warning('コピー対象のテーブルが見つかりませんでした。');

            return Command::FAILURE;
        }

        if ($dryRun) {
            $io->note('dry-run: 実際の書き込みは行いません。');

            foreach ($tables as $table) {
                $count = (int) $this->sqlite->fetchOne(sprintf('SELECT COUNT(*) FROM "%s"', $table));
                $io->writeln(sprintf('  <info>%-40s</info> %d rows (予定)', $table, $count));
            }

            return Command::SUCCESS;
        }

        // 途中で失敗した場合に中途半端な状態を残さないよう、全体を1トランザクションにする
        $this->mysql->beginTransaction();

        // 外部キーの依存順を気にしなくて済むよう、セッション内だけ制約を外す
        $this->mysql->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $this->mysql->executeStatement('SET UNIQUE_CHECKS = 0');

        try {
            foreach ($tables as $table) {
                $copied = $this->copyTable($table);
                $io->writeln(sprintf('  <info>%-40s</info> %d rows', $table, $copied));
            }

            $this->mysql->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
            $this->mysql->executeStatement('SET UNIQUE_CHECKS = 1');
            $this->mysql->commit();
        } catch (Throwable $e) {
            $this->mysql->rollBack();
            $io->error(sprintf('移行中にエラーが発生したため全件ロールバックしました: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $io->success('移行が完了しました。件数の突き合わせを行ってください。');

        return Command::SUCCESS;
    }

    private function copyTable(string $table): int
    {
        $copied = 0;
        $batch = [];

        $sql = sprintf('SELECT * FROM "%s"', $table);

        foreach ($this->sqlite->iterateAssociative($sql) as $row) {
            $batch[] = $row;

            if (self::BATCH_SIZE <= \count($batch)) {
                $copied += $this->insertBatch($table, $batch);
                $batch = [];
            }
        }

        if ([] !== $batch) {
            $copied += $this->insertBatch($table, $batch);
        }

        return $copied;
    }

    /**
     * @param non-empty-list<array<string, mixed>> $rows
     */
    private function insertBatch(string $table, array $rows): int
    {
        $columns = array_keys($rows[0]);

        $columnList = implode(', ', array_map(
            static fn (string $c): string => sprintf('`%s`', $c),
            $columns,
        ));

        $rowPlaceholder = '(' . implode(', ', array_fill(0, \count($columns), '?')) . ')';

        $params = [];
        foreach ($rows as $row) {
            foreach ($columns as $column) {
                $params[] = $row[$column];
            }
        }

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES %s',
            $table,
            $columnList,
            implode(', ', array_fill(0, \count($rows), $rowPlaceholder)),
        );

        return (int) $this->mysql->executeStatement($sql, $params);
    }
}
