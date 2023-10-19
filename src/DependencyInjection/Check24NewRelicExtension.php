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

namespace Check24\NewRelicBundle\DependencyInjection;

use Check24\NewRelicBundle\EventListener\ConsoleCommandListener;
use Check24\NewRelicBundle\EventListener\ConsoleErrorListener;
use Check24\NewRelicBundle\EventListener\ExceptionListener;
use Check24\NewRelicBundle\EventListener\RequestListener;
use Check24\NewRelicBundle\Monolog\Handler\NewRelicHandler;
use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceId;
use Check24\NewRelicBundle\Trace\TraceIdFactoryInterface;
use Check24\NewRelicBundle\TransactionNaming\Messenger\TransactionNameStrategyInterface as MessengerTransactionNameStrategyInterface;
use Check24\NewRelicBundle\TransactionNaming\Request\TransactionNameStrategyInterface as RequestTransactionNameStrategyInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class Check24NewRelicExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.php');

        /**
         * @var array{
         *      appname: ?string,
         *      license: ?string,
         *      xmit: bool,
         *      interactor: string,
         *      logging: array{buffer_size: int},
         *      transaction_naming: array{messenger: string, request: string},
         *      excluded_transactions: array{commands: array<string>, routes: array<string>, paths: array<string>},
         *      excluded_exceptions: array<string>,
         *      trace_id_factory: string
         *  } $config
         */
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->register(Config::class)
            ->setArguments([
                '$appname' => $config['appname'],
                '$license' => $config['license'],
                '$xmit' => $config['xmit'],
            ]);

        $container->setAlias(NewRelicInteractorInterface::class, $config['interactor']);
        $container->setAlias(RequestTransactionNameStrategyInterface::class, $config['transaction_naming']['request']);
        $container->setAlias(MessengerTransactionNameStrategyInterface::class, $config['transaction_naming']['messenger']);
        $container->setAlias(TraceIdFactoryInterface::class, $config['trace_id_factory']);

        $container->register(TraceId::class)
            ->setFactory(new Reference(TraceIdFactoryInterface::class));

        $container->register('check24.new_relic.monolog_handler', NewRelicHandler::class)
            ->setArguments(['$bufferSize' => $config['logging']['buffer_size']])
            ->setAutowired(true);

        $container->getDefinition(RequestListener::class)
            ->setArguments([
                '$excludedRoutes' => $config['excluded_transactions']['routes'],
                '$excludedPaths' => $config['excluded_transactions']['paths'],
            ]);

        $container->getDefinition(ExceptionListener::class)
            ->setArguments(['$excludedExceptions' => $config['excluded_exceptions']]);

        $container->getDefinition(ConsoleCommandListener::class)
            ->setArguments(['$excludedCommands' => $config['excluded_transactions']['commands']]);

        $container->getDefinition(ConsoleErrorListener::class)
            ->setArguments(['$excludedExceptions' => $config['excluded_exceptions']]);
    }
}
