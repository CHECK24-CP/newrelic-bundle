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

namespace Tests\Check24\NewRelicBundle\Monolog\Handler;

use Check24\NewRelicBundle\Monolog\Handler\NewRelicHandler;
use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractor;
use Monolog\Level;
use Monolog\Test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class NewRelicHandlerTest extends TestCase
{
    private NewRelicHandler&MockObject $handler;

    public function testSendNewRelicRequestOnlyWhenReachBufferSize(): void
    {
        $this->createHandler(3, 0);
        $this->handler->handle($this->getRecord());

        $this->createHandler(3, 1);

        $this->handler->handle($this->getRecord());
        $this->handler->handle($this->getRecord());
        $this->handler->handle($this->getRecord());

        $this->createHandler(2, 2);

        $this->handler->handle($this->getRecord());
        $this->handler->handle($this->getRecord());
        $this->handler->handle($this->getRecord());
        $this->handler->handle($this->getRecord());
    }

    private function createHandler(int $bufferSize, int $requiredInvocations): void
    {
        $constructorArgs = [new Config('appname', 'license'), new NewRelicInteractor(), $bufferSize, 'endpoint', Level::Debug, true];

        $this->handler = $this->getMockBuilder(NewRelicHandler::class)
            ->setConstructorArgs($constructorArgs)
            ->onlyMethods(['flushBuffer'])
            ->getMock();

        $this->handler->expects($this->atLeast($requiredInvocations))
            ->method('flushBuffer');
    }
}
