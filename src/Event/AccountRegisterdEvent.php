<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Account;

class AccountRegisterdEvent
{
    public function __construct(
        public readonly Account $account
    ) {
    }
}
