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
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

class MessageNameStrategyTest extends TestCase
{
    #[TestWith(['DummyMessage', new DummyMessage()])]
    #[TestWith(['stdClass', new \stdClass()])]
    public function testItReturnsShortMessageClassname(string $expectedName, object $message): void
    {
        self::assertSame($expectedName, (new MessageNameStrategy())->getName(new Envelope($message)));
    }
}
