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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('check24_new_relic');

        $treeBuilder->getRootNode()
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

        return $treeBuilder;
    }
}
