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

namespace Tests\Check24\NewRelicBundle\TransactionNaming\Request;

use Check24\NewRelicBundle\TransactionNaming\Request\ControllerNameStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ControllerNameStrategyTest extends TestCase
{
    public function testItReturnsControllerName(): void
    {
        $strategy = new ControllerNameStrategy();

        self::assertSame('test-controller', $strategy->getName(new Request(attributes: ['_controller' => 'test-controller'])));
        self::assertSame('controller::test', $strategy->getName(new Request(attributes: ['_controller' => ['controller', 'test']])));
        self::assertSame('test::undefined', $strategy->getName(new Request(attributes: ['_controller' => ['test']])));
        self::assertSame('n/a', $strategy->getName(new Request(attributes: ['_controller' => static fn () => 'callback'])));
    }
}
