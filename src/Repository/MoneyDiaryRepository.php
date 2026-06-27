<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MoneyDiary;
use App\Entity\User;
use App\Enum\MoneyDiartType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MoneyDiary>
 */
class MoneyDiaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoneyDiary::class);
    }

    /**
     * @return MoneyDiary[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user)
            ->orderBy('m.date', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sum of amounts for a user filtered by type (income / expenditure).
     */
    public function sumByType(User $user, MoneyDiartType $type): int
    {
        return (int)$this->createQueryBuilder('m')
            ->select('COALESCE(SUM(m.amount), 0)')
            ->andWhere('m.user = :user')
            ->andWhere('m.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
