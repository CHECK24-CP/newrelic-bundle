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

namespace Check24\NewRelicBundle\EventSubscriber;

use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceId;
use Check24\NewRelicBundle\TransactionNaming\Request\TransactionNameStrategyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class RequestSubscriber implements EventSubscriberInterface
{
    /**
     * @param array<string> $excludedRoutes
     * @param array<string> $excludedPaths
     */
    public function __construct(
        private array $excludedRoutes,
        private array $excludedPaths,
        private Config $config,
        private NewRelicInteractorInterface $interactor,
        private TransactionNameStrategyInterface $transactionNameStrategy,
        private TraceId $traceId,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => [
                ['initializeApplication', 255],
                ['configureTransaction', 31],
            ],
        ];
    }

    public function initializeApplication(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->interactor->setApplicationName(
            $this->config->appname,
            $this->config->license,
            $this->config->xmit,
        );
    }

    public function configureTransaction(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        /** @var string $route */
        $route = $request->attributes->get('_route', '');

        if (
            \in_array($route, $this->excludedRoutes, true)
            || \in_array($request->getPathInfo(), $this->excludedPaths, true)
        ) {
            $this->interactor->ignoreTransaction();
        } else {
            $this->interactor->setTransactionName(
                $this->transactionNameStrategy->getName($request),
            );
            // This is needed to be able to link logs to the current transaction
            $this->interactor->addCustomParameter('traceId', $this->traceId->__toString());
        }
    }
}
