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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class Check24NewRelicExtension extends AbstractExtension
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();

        if (!$rootNode instanceof ArrayNodeDefinition) {
            return;
        }

        $rootNode
            ->children()
                ->scalarNode('appname')
                    ->defaultNull()
                    ->info('The application name')
                ->end()
                ->scalarNode('license')
                    ->defaultNull()
                    ->info('NewRelic license')
                ->end()
                ->booleanNode('xmit')
                    ->defaultFalse()
                ->end()
                ->scalarNode('interactor')
                    ->defaultValue('check24.new_relic.interactor')
                    ->info('The NewRelic interactor used to communicate with agent')
                ->end()
                ->arrayNode('logging')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('buffer_size')
                            ->min(1)->max(1000)
                            ->defaultValue(100)
                            ->info('The logs buffer size used to send logs to NewRelic in bulk')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('transaction_naming')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('messenger')
                            ->cannotBeEmpty()
                            ->defaultValue('check24.new_relic.transaction_name.messenger.message_name')
                            ->info('Transaction naming strategy used to capture messenger messages')
                        ->end()
                        ->scalarNode('request')
                            ->cannotBeEmpty()
                            ->defaultValue('check24.new_relic.transaction_name.request.route_name')
                            ->info('Transaction naming strategy used to capture HTTP requests')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('excluded_transactions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('commands')
                            ->scalarPrototype()->end()
                            ->info('Command transactions to exclude')
                        ->end()
                        ->arrayNode('routes')
                            ->scalarPrototype()->end()
                            ->info('Routes transactions to exclude')
                        ->end()
                        ->arrayNode('paths')
                            ->scalarPrototype()->end()
                            ->info('HTTP paths transactions to exclude')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('excluded_exceptions')
                    ->scalarPrototype()->end()
                    ->info('Exceptions to exclude from errors in NewRelic)')
                ->end()
                ->scalarNode('trace_id_factory')
                    ->cannotBeEmpty()
                    ->defaultValue('check24.new_relic.trace_id.uuid_factory')
                    ->info('Trace ID factory to create a unique Trace ID per request/message')
                ->end()
            ->end()
        ->end();
    }

    /**
     * @param array{
     *     appname: ?string,
     *     license: ?string,
     *     xmit: bool,
     *     interactor: string,
     *     logging: array{buffer_size: int},
     *     transaction_naming: array{messenger: string, request: string},
     *     excluded_transactions: array{commands: array<string>, routes: array<string>, paths: array<string>},
     *     excluded_exceptions: array<string>,
     *     trace_id_factory: string
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $loader = new PhpFileLoader($builder, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.php');

        $builder->register(Config::class)
            ->setArguments([
                '$appname' => $config['appname'],
                '$license' => $config['license'],
                '$xmit' => $config['xmit'],
            ]);

        $builder->setAlias(NewRelicInteractorInterface::class, $config['interactor']);
        $builder->setAlias(RequestTransactionNameStrategyInterface::class, $config['transaction_naming']['request']);
        $builder->setAlias(MessengerTransactionNameStrategyInterface::class, $config['transaction_naming']['messenger']);
        $builder->setAlias(TraceIdFactoryInterface::class, $config['trace_id_factory']);

        $builder->register(TraceId::class)
            ->setFactory(new Reference(TraceIdFactoryInterface::class));

        $builder->register('check24.new_relic.monolog_handler', NewRelicHandler::class)
            ->setArguments(['$bufferSize' => $config['logging']['buffer_size']])
            ->setAutowired(true);

        $builder->getDefinition(RequestListener::class)
            ->setArguments([
                '$excludedRoutes' => $config['excluded_transactions']['routes'],
                '$excludedPaths' => $config['excluded_transactions']['paths'],
            ]);

        $builder->getDefinition(ExceptionListener::class)
            ->setArguments(['$excludedExceptions' => $config['excluded_exceptions']]);

        $builder->getDefinition(ConsoleCommandListener::class)
            ->setArguments(['$excludedCommands' => $config['excluded_transactions']['commands']]);

        $builder->getDefinition(ConsoleErrorListener::class)
            ->setArguments(['$excludedExceptions' => $config['excluded_exceptions']]);
    }
}
