<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\PaymentSource;
use App\Enum\PaymentType;
use App\Event\AccountRegisterdEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: AccountRegisterdEvent::class)]
class InitializePaymentSourceListener
{
    private const array INITIAL_PAYMENT_SOURCE_NAME = [
        'cash',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        AccountRegisterdEvent $event,
    ): void {
        foreach (self::INITIAL_PAYMENT_SOURCE_NAME as $item) {
            $paymentSource = new PaymentSource();

            $paymentSource->setName($item);
            $paymentSource->setType(PaymentType::CASH);
            $paymentSource->setUser($event->account->getUser());

            $this->entityManager->persist($paymentSource);
            $this->entityManager->flush();
        }
    }
}
