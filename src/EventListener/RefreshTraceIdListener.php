<?php

declare(strict_types=1);

/*
 * This file is part of CHECK24 New Relic bundle.
 *
 * (c) CHECK24 - Radhi Guennichi <mohamed.guennichi@check24.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Check24\NewRelicBundle\EventListener;

use Check24\NewRelicBundle\Trace\TraceId;
use Check24\NewRelicBundle\Trace\TraceIdFactoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\EventListener\ResetServicesListener;

/**
 * Ensure this event listener is triggered post Messenger's ResetServicesListener.
 *
 * The ResetServicesListener is responsible for resetting monolog handlers. Invoking this listener after ensures
 * that monolog handler buffers are processed and cleared prior to the generation of a new trace ID.
 *
 * Refer to {@see ResetServicesListener} for detailed insights.
 */
#[AsEventListener(WorkerRunningEvent::class, priority: -1025)]
readonly class RefreshTraceIdListener
{
    public function __construct(
        private TraceId $traceId,
        private TraceIdFactoryInterface $traceIdFactory,
    ) {
    }

    public function __invoke(WorkerRunningEvent $event): void
    {
        if ($event->isWorkerIdle()) {
            return;
        }

        $this->traceId->refresh($this->traceIdFactory->__invoke());
    }
}
