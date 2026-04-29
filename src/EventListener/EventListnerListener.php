<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class EventListnerListener
{
    public const NAME = 'app.sample_event';

    #[AsEventListener(event: self::NAME)]
    public function onAppSampleEvent($event): void
    {
        dump($event->getValue());
    }
}
