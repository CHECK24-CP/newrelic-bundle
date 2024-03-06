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

use Check24\NewRelicBundle\EventListener\ExceptionListener;
use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractor;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceIdFactoryInterface;
use Check24\NewRelicBundle\Trace\TraceUuidFactory;
use Check24\NewRelicBundle\TransactionNaming\Messenger\MessageNameStrategy;
use Check24\NewRelicBundle\TransactionNaming\Messenger\TransactionNameStrategyInterface as MessengerTransactionNameStrategyInterface;
use Check24\NewRelicBundle\TransactionNaming\Request\RouteNameStrategy;
use Check24\NewRelicBundle\TransactionNaming\Request\TransactionNameStrategyInterface as RequestTransactionNameStrategyInterface;
use PHPUnit\Framework\TestCase;
use Tests\Check24\NewRelicBundle\App\TestKernel;

class FunctionalTest extends TestCase
{
    public function testKernelIsBootableAndServicesAreUsable(): void
    {
        $kernel = new TestKernel('dev', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        self::assertInstanceOf(NewRelicInteractor::class, $container->get(NewRelicInteractorInterface::class));
        self::assertInstanceOf(TraceUuidFactory::class, $container->get(TraceIdFactoryInterface::class));
        self::assertInstanceOf(RouteNameStrategy::class, $container->get(RequestTransactionNameStrategyInterface::class));
        self::assertInstanceOf(MessageNameStrategy::class, $container->get(MessengerTransactionNameStrategyInterface::class));
        self::assertInstanceOf(ExceptionListener::class, $container->get(ExceptionListener::class));

        $config = $container->get(Config::class);

        /* @phpstan-ignore-next-line */
        self::assertSame(['myapp', 'dummyLicense'], [$config->appname, $config->license]);
    }
}
