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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener(ExceptionEvent::class)]
readonly class ExceptionListener
{
    /**
     * @param array<class-string<\Throwable>> $excludedExceptions
     */
    public function __construct(
        private array $excludedExceptions,
        private NewRelicInteractorInterface $interactor,
        private ?Security $security = null,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        // link users to transactions (anonymously), so that we can monitor # of users impacted by errors in error inbox
        // see https://docs.newrelic.com/docs/errors-inbox/error-users-impacted/#attributes
        if ($user = $this->security?->getUser()) {
            $this->interactor->addCustomParameter('enduser.id', crc32($user->getUserIdentifier()));
        }

        foreach ($this->excludedExceptions as $excludedException) {
            if ($exception instanceof $excludedException) {
                return;
            }
        }

        $this->interactor->noticeThrowable($exception);
    }
}
