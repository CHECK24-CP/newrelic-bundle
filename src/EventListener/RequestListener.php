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

use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceId;
use Check24\NewRelicBundle\TransactionNaming\Request\TransactionNameStrategyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class RequestListener
{
    /**
     * @param array<string> $excludedRoutes
     * @param array<string> $excludedPaths
     */
    public function __construct(
        private array $excludedRoutes,
        private array $excludedPaths,
        private NewRelicInteractorInterface $interactor,
        private Config $config,
        private TransactionNameStrategyInterface $transactionNameStrategy,
        private LoggerInterface $logger,
        private TraceId $traceId,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->interactor->setApplicationName(
            $this->config->appname,
            $this->config->license,
            $this->config->xmit,
        );

        $request = $event->getRequest();
        if (
            \in_array($request->attributes->get('_route'), $this->excludedRoutes, true)
            || \in_array($request->getPathInfo(), $this->excludedPaths, true)
        ) {
            $this->interactor->ignoreTransaction();
        } else {
            $this->interactor->setTransactionName(
                $this->transactionNameStrategy->getName($request),
            );
            // This is needed to be able to link logs to the current transaction
            $this->interactor->addCustomParameter('traceId', $this->traceId->__toString());

            $this->logger->debug('Request info details', [
                'headers' => $request->headers->all(),
                'request' => $request->request->all(),
                'queries' => $request->query->all(),
            ]);
        }
    }
}
