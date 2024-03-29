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

namespace Check24\NewRelicBundle\TransactionNaming\Messenger;

use Symfony\Component\Messenger\Envelope;

readonly class MessageNameStrategy implements TransactionNameStrategyInterface
{
    public function getName(Envelope $envelope): string
    {
        return substr((string) strrchr('\\' . $envelope->getMessage()::class, '\\'), 1);
    }
}
