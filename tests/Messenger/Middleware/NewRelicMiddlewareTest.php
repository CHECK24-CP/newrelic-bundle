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

namespace Tests\Check24\NewRelicBundle\Messenger\Middleware;

use Check24\NewRelicBundle\Messenger\Middleware\NewRelicMiddleware;
use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceId;
use Check24\NewRelicBundle\TransactionNaming\Messenger\TransactionNameStrategyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Tests\Check24\NewRelicBundle\TransactionNaming\Messenger\DummyMessage;

class NewRelicMiddlewareTest extends TestCase
{
    private NewRelicMiddleware $middleware;
    private NewRelicInteractorInterface&MockObject $interactor;

    private TransactionNameStrategyInterface&MockObject $transactionNameStrategy;

    protected function setUp(): void
    {
        $this->middleware = new NewRelicMiddleware(
            new Config('app', 'license'),
            new TraceId('some-id'),
            $this->interactor = $this->createMock(NewRelicInteractorInterface::class),
            $this->transactionNameStrategy = $this->createMock(TransactionNameStrategyInterface::class),
        );
    }

    public function testItStartTransaction(): void
    {
        $envelope = new Envelope(new DummyMessage());

        $this->interactor->expects(self::once())
            ->method('startTransaction')
            ->with('app', 'license');

        $this->interactor->expects(self::once())
            ->method('enableBackgroundJob');

        $this->transactionNameStrategy->expects(self::once())
            ->method('getName')
            ->with($envelope)
            ->willReturn('dummy-name');

        $this->interactor->expects(self::once())
            ->method('setTransactionName')
            ->with('dummy-name');

        $this->interactor->expects(self::once())
            ->method('addCustomParameter')
            ->with('traceId', 'some-id');

        $this->interactor->expects(self::once())
            ->method('endTransaction');

        $this->middleware->handle($envelope, new StackMiddleware());
    }
}
