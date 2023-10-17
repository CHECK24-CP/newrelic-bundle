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

namespace Tests\Check24\NewRelicBundle\Monolog\Processor;

use Check24\NewRelicBundle\Monolog\Processor\TraceIdProcessor;
use Check24\NewRelicBundle\Trace\TraceId;
use Monolog\Test\TestCase;

class TraceIdProcessorTest extends TestCase
{
    public function testItAddTraceIdToLogExtra(): void
    {
        self::assertSame(
            ['trace.id' => 'some-id'],
            (new TraceIdProcessor(new TraceId('some-id')))->__invoke($this->getRecord())->extra,
        );
    }
}
