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

namespace Tests\Check24\NewRelicBundle\EventSubscriber;

use Check24\NewRelicBundle\EventSubscriber\RequestSubscriber;
use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceId;
use Check24\NewRelicBundle\TransactionNaming\Request\TransactionNameStrategyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestSubscriberTest extends TestCase
{
    private RequestSubscriber $subscriber;
    private NewRelicInteractorInterface&MockObject $interactor;
    private TransactionNameStrategyInterface&MockObject $transactionNameStrategy;

    protected function setUp(): void
    {
        $this->subscriber = new RequestSubscriber(
            ['route.1'],
            ['/path/1'],
            new Config('app', 'license'),
            $this->interactor = $this->createMock(NewRelicInteractorInterface::class),
            $this->transactionNameStrategy = $this->createMock(TransactionNameStrategyInterface::class),
            new TraceId('some-id'),
        );
    }

    public function testItInitializeApplication(): void
    {
        $event = $this->createMock(RequestEvent::class);
        $event->expects(self::once())
            ->method('isMainRequest')
            ->willReturn(true);

        $this->interactor->expects(self::once())
            ->method('setApplicationName')
            ->with('app', 'license', false);

        $this->subscriber->initializeApplication($event);
    }

    public function testItSendTransactionToNewRelicAndLogRequestData(): void
    {
        $event = $this->createMock(RequestEvent::class);
        $event->expects(self::once())
            ->method('isMainRequest')
            ->willReturn(true);
        $event->expects(self::once())
            ->method('getRequest')
            ->willReturn(Request::create('/path?foo=bar'));

        $this->transactionNameStrategy->expects(self::once())
            ->method('getName')
            ->willReturn('transaction_name');

        $this->interactor->expects(self::once())
            ->method('setTransactionName')
            ->with('transaction_name');

        $this->interactor->expects(self::once())
            ->method('addCustomParameter')
            ->with('traceId', 'some-id');

        $this->subscriber->configureTransaction($event);
    }

    public function testItIgnoresRoutes(): void
    {
        $event = $this->createMock(RequestEvent::class);
        $event->expects(self::once())
            ->method('isMainRequest')
            ->willReturn(true);
        $event->expects(self::once())
            ->method('getRequest')
            ->willReturn(new Request(attributes: ['_route' => 'route.1']));

        $this->interactor->expects(self::once())
            ->method('ignoreTransaction');

        $this->interactor->expects(self::never())
            ->method('setTransactionName');

        $this->subscriber->configureTransaction($event);
    }

    public function testItIgnoresPaths(): void
    {
        $event = $this->createMock(RequestEvent::class);
        $event->expects(self::once())
            ->method('isMainRequest')
            ->willReturn(true);
        $event->expects(self::once())
            ->method('getRequest')
            ->willReturn(Request::create('/path/1?foo=bar', 'GET'));

        $this->interactor->expects(self::once())
            ->method('ignoreTransaction');

        $this->interactor->expects(self::never())
            ->method('setTransactionName');

        $this->subscriber->configureTransaction($event);
    }
}
