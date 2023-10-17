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

use Check24\NewRelicBundle\EventListener\ConsoleErrorListener;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\Check24\NewRelicBundle\DummyException;

class ConsoleErrorListenerTest extends TestCase
{
    private ConsoleErrorListener $listener;
    private NewRelicInteractorInterface&MockObject $interactor;

    protected function setUp(): void
    {
        $this->listener = new ConsoleErrorListener(
            [DummyException::class],
            $this->interactor = $this->createMock(NewRelicInteractorInterface::class),
        );
    }

    public function testItSendErrorToNewRelic(): void
    {
        $event = new ConsoleErrorEvent(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
            $e = new \RuntimeException(),
        );

        $this->interactor->expects(self::once())
            ->method('noticeThrowable')
            ->with($e);

        $this->listener->__invoke($event);
    }

    public function testItIgnoreExcludedErrors(): void
    {
        $event = new ConsoleErrorEvent(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
            new DummyException(),
        );

        $this->interactor->expects(self::never())
            ->method('noticeThrowable');

        $this->listener->__invoke($event);
    }
}
