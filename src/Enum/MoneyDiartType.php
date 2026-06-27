<?php

declare(strict_types=1);

namespace App\Enum;

enum MoneyDiartType: string
{
    case EXPENDITURE = 'expenditure';
    case INCOME = 'income';
}
