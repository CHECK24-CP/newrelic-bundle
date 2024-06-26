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

namespace Check24\NewRelicBundle\Trace;

class TraceId implements \Stringable
{
    public function __construct(private string $fallbackValue)
    {
    }

    public function __toString(): string
    {
        return newrelic_get_trace_metadata()['trace_id'] ?? $this->fallbackValue;
    }

    public function refresh(self $new): void
    {
        $this->fallbackValue = $new->fallbackValue;
    }
}
