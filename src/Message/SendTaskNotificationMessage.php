<?php

declare(strict_types=1);

namespace App\Message;

class SendTaskNotificationMessage
{
    public function __construct(
        private int $taskId,
    ) {
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }
}
