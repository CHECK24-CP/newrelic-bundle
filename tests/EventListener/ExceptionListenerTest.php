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

use Check24\NewRelicBundle\EventListener\ExceptionListener;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tests\Check24\NewRelicBundle\DummyException;

class ExceptionListenerTest extends TestCase
{
    private ExceptionListener $listener;
    private NewRelicInteractorInterface&MockObject $interactor;
    private Security&MockObject $security;

    protected function setUp(): void
    {
        $this->listener = new ExceptionListener(
            [DummyException::class],
            $this->interactor = $this->createMock(NewRelicInteractorInterface::class),
            $this->security = $this->createMock(Security::class),
        );
    }

    public function testItSendErrorToNewRelic(): void
    {
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            HttpKernelInterface::MAIN_REQUEST,
            $e = new \RuntimeException(),
        );

        $this->interactor->expects(self::once())
            ->method('noticeThrowable')
            ->with($e);

        $this->listener->__invoke($event);
    }

    public function testItAttachesUserId(): void
    {
        $this->security->method('getUser')->willReturn(new InMemoryUser('foo@bar.baz', null));
        $this->interactor->expects(self::once())->method('addCustomParameter')->with('enduser.id', '3260418537');

        $this->listener->__invoke(new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException(),
        ));
    }

    public function testItIgnoreExcludedErrors(): void
    {
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            HttpKernelInterface::MAIN_REQUEST,
            new DummyException(),
        );

        $this->interactor->expects(self::never())
            ->method('noticeThrowable');

        $this->listener->__invoke($event);
    }
}
