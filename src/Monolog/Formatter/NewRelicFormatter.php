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

namespace Check24\NewRelicBundle\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class NewRelicFormatter extends NormalizerFormatter
{
    public function __construct()
    {
        parent::__construct(\DateTimeInterface::RFC3339_EXTENDED);
    }

    protected function normalizeRecord(LogRecord $record): array
    {
        $normalized = parent::normalizeRecord($record);

        // Re-key timestamp/traceId for NR logging
        $normalized['trace.id'] = $record->extra['trace.id'] ?? null;
        $normalized['timestamp'] = $normalized['datetime'];

        // Remove keys that are not used by NR
        unset($normalized['extra']['trace.id'], $normalized['datetime']);

        return $normalized;
    }
}
