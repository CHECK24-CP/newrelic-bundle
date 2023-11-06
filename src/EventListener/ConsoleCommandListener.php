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

namespace Check24\NewRelicBundle\EventListener;

use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceId;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(ConsoleCommandEvent::class)]
readonly class ConsoleCommandListener
{
    /**
     * @param array<string> $excludedCommands
     */
    public function __construct(
        private array $excludedCommands,
        private Config $config,
        private NewRelicInteractorInterface $interactor,
        private TraceId $traceId,
    ) {
    }

    public function __invoke(ConsoleCommandEvent $event): void
    {
        if (!$command = $event->getCommand()) {
            return;
        }

        $this->interactor->setApplicationName(
            $this->config->appname,
            $this->config->license,
            $this->config->xmit,
        );

        if (\in_array($command->getName(), $this->excludedCommands, true)) {
            $this->interactor->ignoreTransaction();
        } else {
            $this->interactor->setTransactionName($command->getName() ?? 'Unknown command');
            $this->interactor->enableBackgroundJob();

            // This is needed to be able to link logs to the current transaction
            $this->interactor->addCustomParameter('traceId', $this->traceId->__toString());

            $input = $event->getInput();

            $this->addCustomParameters($input->getArguments());
            $this->addCustomParameters($input->getOptions(), '--');
        }
    }

    /**
     * @param array<scalar|array<scalar>|null> $parameters
     */
    private function addCustomParameters(array $parameters, string $keyPrefix = ''): void
    {
        foreach ($parameters as $key => $value) {
            $key = $keyPrefix . $key;
            if (\is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->interactor->addCustomParameter($key . "[$k]", $v);
                }
            } else {
                $this->interactor->addCustomParameter($key, (string) $value);
            }
        }
    }
}
