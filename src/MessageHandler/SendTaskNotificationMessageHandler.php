<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SendTaskNotificationMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendTaskNotificationMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        SendTaskNotificationMessage $message,
    ): void {
        // Here you would implement the logic to send a notification about the task.
        // For example, you could fetch the task from the database using the task ID,
        // and then send an email or push notification to the user.

        $taskId = $message->getTaskId();
        $this->logger->info("Handling SendTaskNotificationMessage for task ID: {$taskId}");
        // Fetch the task from the database using $taskId
        // Send notification about the task
    }
}
