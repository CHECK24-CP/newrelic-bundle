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

namespace Tests\Check24\NewRelicBundle;

use Check24\NewRelicBundle\Check24NewRelicBundle;
use Check24\NewRelicBundle\DependencyInjection\Check24NewRelicExtension;
use PHPUnit\Framework\TestCase;

class Check24NewRelicBundleTest extends TestCase
{
    public function testItShouldReturnNewContainerExtension(): void
    {
        self::assertInstanceOf(Check24NewRelicExtension::class, (new Check24NewRelicBundle())->getContainerExtension());
    }
}
