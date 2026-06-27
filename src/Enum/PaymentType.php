<?php

declare(strict_types=1);

namespace App\Enum;

enum PaymentType: string
{
    case CREDIT_CARD = 'credit_card';
    case PAYPAL = 'paypal';
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
}
