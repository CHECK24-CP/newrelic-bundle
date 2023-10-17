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

namespace Check24\NewRelicBundle\Monolog\Processor;

use Check24\NewRelicBundle\Trace\TraceId;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

readonly class TraceIdProcessor implements ProcessorInterface
{
    public function __construct(private TraceId $traceId)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['trace.id'] = $this->traceId->__toString();

        return $record;
    }
}
