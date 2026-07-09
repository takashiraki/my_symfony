<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Category;
use App\Enum\CategoryType;
use App\Event\AccountRegisterdEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: AccountRegisterdEvent::class)]
class InitializeCategoryListener
{
    private const array INITIAL_EXPENSE_CATEGORY_NAME = [
        'food',
        'transport',
        'shopping',
        'daily_goods',
        'housing',
        'utilities',
        'communication',
        'health',
        'entertainment',
        'education',
        'other',
    ];

    private const array INITIAL_INCOME_CATEGORY_NAME = [
        'salary',
        'bonus',
        'side_income',
        'investment',
        'gift',
        'other',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        AccountRegisterdEvent $event,
    ): void {
        foreach (self::INITIAL_EXPENSE_CATEGORY_NAME as $name) {
            $category = new Category();

            $category->setName($name);
            $category->setUser($event->account->getUser());
            $category->setType(CategoryType::EXPENDITURE);

            $this->entityManager->persist($category);
            $this->entityManager->flush();
        }

        foreach (self::INITIAL_INCOME_CATEGORY_NAME as $name) {
            $category = new Category();
            $category->setName($name);
            $category->setUser($event->account->getUser());
            $category->setType(CategoryType::INCOME);

            $this->entityManager->persist($category);
            $this->entityManager->flush();
        }
    }
}
