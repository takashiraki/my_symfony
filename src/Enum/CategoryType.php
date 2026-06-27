<?php

declare(strict_types=1);

namespace App\Enum;

enum CategoryType: string
{
    case INCOME = 'income';
    case EXPENDITURE = 'expenditure';
}
