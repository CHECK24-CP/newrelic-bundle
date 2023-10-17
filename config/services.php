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

use Check24\NewRelicBundle\EventListener\ConsoleCommandListener;
use Check24\NewRelicBundle\EventListener\ConsoleErrorListener;
use Check24\NewRelicBundle\EventListener\ExceptionListener;
use Check24\NewRelicBundle\EventListener\RequestListener;
use Check24\NewRelicBundle\Messenger\Middleware\NewRelicMiddleware;
use Check24\NewRelicBundle\Monolog\Processor\TraceIdProcessor;
use Check24\NewRelicBundle\NewRelic\LoggingInteractorDecorator;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractor;
use Check24\NewRelicBundle\Trace\TraceUuidFactory;
use Check24\NewRelicBundle\TransactionNaming\Messenger\MessageNameStrategy;
use Check24\NewRelicBundle\TransactionNaming\Request\ControllerNameStrategy;
use Check24\NewRelicBundle\TransactionNaming\Request\RouteNameStrategy;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Event listeners
    $listeners = [
        RequestListener::class,
        ExceptionListener::class,
        ConsoleCommandListener::class,
        ConsoleErrorListener::class,
    ];

    foreach ($listeners as $id) {
        $services->set($id)->tag('kernel.event_listener')->autowire();
    }

    // Interactor
    $services->set('check24.new_relic.interactor', NewRelicInteractor::class);
    $services->set('check24.new_relic.logging_interactor', LoggingInteractorDecorator::class)
        ->arg('$interactor', service('check24.new_relic.interactor'))
        ->tag('monolog.logger', ['channel' => 'app']);

    // Transaction naming
    $services->set('check24.new_relic.transaction_name.request.route_name', RouteNameStrategy::class);
    $services->set('check24.new_relic.transaction_name.request.controller_name', ControllerNameStrategy::class);
    $services->set('check24.new_relic.transaction_name.messenger.message_name', MessageNameStrategy::class);

    // Messenger
    $services->set('check24.new_relic.messenger_middleware', NewRelicMiddleware::class)->autowire();

    // Trace ID
    $services->set('check24.new_relic.trace_id.uuid_factory', TraceUuidFactory::class)
        ->arg('$uuidFactory', service('uuid.factory'));

    // Monolog
    $services->set(TraceIdProcessor::class)->autowire()->tag('monolog.processor');
};
