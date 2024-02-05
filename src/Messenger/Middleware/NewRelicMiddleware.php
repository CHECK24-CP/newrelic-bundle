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

namespace Check24\NewRelicBundle\Messenger\Middleware;

use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceId;
use Check24\NewRelicBundle\TransactionNaming\Messenger\TransactionNameStrategyInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\WrappedExceptionsInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

readonly class NewRelicMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Config $config,
        private TraceId $traceId,
        private NewRelicInteractorInterface $interactor,
        private TransactionNameStrategyInterface $transactionNameStrategy,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->interactor->startTransaction(
            $this->config->appname,
            $this->config->license,
        );

        $this->interactor->setTransactionName(
            $this->transactionNameStrategy->getName($envelope),
        );

        $this->interactor->enableBackgroundJob();

        // This is needed to be able to link logs to the current transaction
        $this->interactor->addCustomParameter('traceId', $this->traceId->__toString());

        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (\Throwable $exception) {
            $wrappedExceptions = $exception instanceof WrappedExceptionsInterface ? $exception->getWrappedExceptions() : [$exception];
            // Mark current transaction as error, but ensure NR groups it by OG exception and not wrapped one when possible
            $this->interactor->noticeThrowable(1 === \count($wrappedExceptions) ? reset($wrappedExceptions) : $exception);

            throw $exception;
        } finally {
            $this->interactor->endTransaction();
        }
    }
}
