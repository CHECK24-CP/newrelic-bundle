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

use Symfony\Component\Uid\Factory\UuidFactory;

readonly class TraceUuidFactory implements TraceIdFactoryInterface
{
    public function __construct(private UuidFactory $uuidFactory)
    {
    }

    public function __invoke(): TraceId
    {
        return new TraceId($this->uuidFactory->create()->toRfc4122());
    }
}
