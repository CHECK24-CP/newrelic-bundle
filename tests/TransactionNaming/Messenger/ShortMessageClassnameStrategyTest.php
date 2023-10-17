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

namespace Tests\Check24\NewRelicBundle\TransactionNaming\Messenger;

use Check24\NewRelicBundle\TransactionNaming\Messenger\MessageNameStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

class ShortMessageClassnameStrategyTest extends TestCase
{
    public function testItReturnsShortMessageClassname(): void
    {
        $strategy = new MessageNameStrategy();

        self::assertSame('DummyMessage', $strategy->getName(new Envelope(new DummyMessage())));
    }
}
