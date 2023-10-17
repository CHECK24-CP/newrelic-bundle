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

namespace Tests\Check24\NewRelicBundle\EventListener;

use Check24\NewRelicBundle\EventListener\RequestListener;
use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceId;
use Check24\NewRelicBundle\TransactionNaming\Request\TransactionNameStrategyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListenerTest extends TestCase
{
    private RequestListener $listener;
    private NewRelicInteractorInterface&MockObject $interactor;
    private TransactionNameStrategyInterface&MockObject $transactionNameStrategy;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->listener = new RequestListener(
            ['route.1'],
            ['/path/1'],
            $this->interactor = $this->createMock(NewRelicInteractorInterface::class),
            new Config('app', 'license'),
            $this->transactionNameStrategy = $this->createMock(TransactionNameStrategyInterface::class),
            $this->logger = $this->createMock(LoggerInterface::class),
            new TraceId('some-id'),
        );
    }

    public function testItSendTransactionToNewRelicAndLogRequestData(): void
    {
        $event = $this->createMock(RequestEvent::class);
        $event->expects(self::once())
            ->method('isMainRequest')
            ->willReturn(true);
        $event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request = Request::create('/path?foo=bar', 'GET'));

        $this->interactor->expects(self::once())
            ->method('setApplicationName')
            ->with('app', 'license', false);

        $this->transactionNameStrategy->expects(self::once())
            ->method('getName')
            ->willReturn('transaction_name');

        $this->interactor->expects(self::once())
            ->method('setTransactionName')
            ->with('transaction_name');

        $this->interactor->expects(self::once())
            ->method('addCustomParameter')
            ->with('traceId', 'some-id');

        $this->logger->expects(self::once())
            ->method('debug')
            ->with('Request info details', [
                'headers' => $request->headers->all(),
                'request' => $request->request->all(),
                'queries' => $request->query->all(),
            ]);

        $this->listener->__invoke($event);
    }

    public function testItIgnoresRoutes(): void
    {
        $event = $this->createMock(RequestEvent::class);
        $event->expects(self::once())
            ->method('isMainRequest')
            ->willReturn(true);
        $event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request = new Request(attributes: ['_route' => 'route.1']));

        $this->interactor->expects(self::once())
            ->method('setApplicationName')
            ->with('app', 'license', false);

        $this->interactor->expects(self::once())
            ->method('ignoreTransaction');

        $this->interactor->expects(self::never())
            ->method('setTransactionName');

        $this->listener->__invoke($event);
    }

    public function testItIgnoresPaths(): void
    {
        $event = $this->createMock(RequestEvent::class);
        $event->expects(self::once())
            ->method('isMainRequest')
            ->willReturn(true);
        $event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request = Request::create('/path/1?foo=bar', 'GET'));

        $this->interactor->expects(self::once())
            ->method('setApplicationName')
            ->with('app', 'license', false);

        $this->interactor->expects(self::once())
            ->method('ignoreTransaction');

        $this->interactor->expects(self::never())
            ->method('setTransactionName');

        $this->listener->__invoke($event);
    }
}
