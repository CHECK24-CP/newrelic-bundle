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

namespace Tests\Check24\NewRelicBundle\Monolog\Formatter;

use Check24\NewRelicBundle\Monolog\Formatter\NewRelicFormatter;
use Monolog\Test\TestCase;

class NewRelicFormatterTest extends TestCase
{
    public function testItAddsTraceIdAndTimestamp(): void
    {
        $formatter = new NewRelicFormatter();

        self::assertSame(
            [
                'message' => 'test',
                'context' => [],
                'level' => 300,
                'level_name' => 'WARNING',
                'channel' => 'test',
                'extra' => [],
                'trace.id' => 'some-id',
                'timestamp' => '2023-10-16T00:00:00.000+02:00',
            ],
            $formatter->format(
                $this->getRecord(datetime: new \DateTimeImmutable('2023-10-16'), extra: ['trace.id' => 'some-id']),
            ),
        );
    }
}
