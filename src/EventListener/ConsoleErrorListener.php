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

use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(ConsoleErrorEvent::class)]
readonly class ConsoleErrorListener
{
    /**
     * @param array<class-string<\Throwable>> $excludedExceptions
     */
    public function __construct(
        private array $excludedExceptions,
        private NewRelicInteractorInterface $interactor,
    ) {
    }

    public function __invoke(ConsoleErrorEvent $event): void
    {
        $exception = $event->getError();

        foreach ($this->excludedExceptions as $excludedException) {
            if ($exception instanceof $excludedException) {
                return;
            }
        }

        $this->interactor->noticeThrowable($exception);
    }
}
