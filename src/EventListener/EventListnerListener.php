<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class EventListnerListener
{
    #[AsEventListener(event: 'app.sample_event')]
    public function onAppSampleEvent($event): void
    {
        echo $event->getValue();
    }
}
