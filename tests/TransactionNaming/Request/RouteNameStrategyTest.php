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

use Check24\NewRelicBundle\TransactionNaming\Request\RouteNameStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RouteNameStrategyTest extends TestCase
{
    public function testItReturnsRouteName(): void
    {
        $strategy = new RouteNameStrategy();

        self::assertSame('test-route', $strategy->getName(new Request(attributes: ['_route' => 'test-route'])));
        self::assertSame('Unknown route', $strategy->getName(new Request()));
    }
}
