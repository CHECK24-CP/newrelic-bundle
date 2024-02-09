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

namespace Check24\NewRelicBundle\NewRelic;

use Psr\Log\LoggerInterface;

readonly class LoggingInteractorDecorator implements NewRelicInteractorInterface
{
    public function __construct(
        private NewRelicInteractorInterface $interactor,
        private LoggerInterface $logger,
    ) {
    }

    public function setApplicationName(string $name, string $license, bool $xmit = false): bool
    {
        $this->logger->debug('Setting New Relic Application name to {name}', ['name' => $name]);

        return $this->interactor->setApplicationName($name, $license, $xmit);
    }

    public function setTransactionName(string $name): bool
    {
        $this->logger->debug('Setting New Relic Transaction name to {name}', ['name' => $name]);

        return $this->interactor->setTransactionName($name);
    }

    public function startTransaction(string $name, string $license): bool
    {
        $this->logger->debug('Starting a new New Relic transaction for app {name}', ['name' => $name]);

        return $this->interactor->startTransaction($name, $license);
    }

    public function endTransaction(bool $ignore = false): bool
    {
        $this->logger->debug('Ending a New Relic transaction');

        return $this->interactor->endTransaction($ignore);
    }

    public function ignoreTransaction(): void
    {
        $this->logger->debug('Ignoring transaction');

        $this->interactor->ignoreTransaction();
    }

    public function addCustomEvent(string $name, array $attributes): void
    {
        $this->logger->debug('Adding custom New Relic event {name}', ['name' => $name, 'attributes' => $attributes]);

        $this->interactor->addCustomEvent($name, $attributes);
    }

    public function addCustomMetric(string $name, float $value): bool
    {
        $this->logger->debug('Adding custom New Relic metric {name}: {value}', ['name' => $name, 'value' => $value]);

        return $this->interactor->addCustomMetric($name, $value);
    }

    public function addCustomParameter(string $name, float|bool|int|string $value): bool
    {
        $this->logger->debug('Adding custom New Relic parameters {name}: {value}', ['name' => $name, 'value' => $value]);

        return $this->interactor->addCustomParameter($name, $value);
    }

    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        $this->logger->debug('Getting New Relic RUM timing header');

        return $this->interactor->getBrowserTimingHeader($includeTags);
    }

    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        $this->logger->debug('Getting New Relic RUM timing footer');

        return $this->interactor->getBrowserTimingFooter($includeTags);
    }

    public function disableAutoRUM(): ?bool
    {
        $this->logger->debug('Disabling New Relic Auto-RUM');

        return $this->interactor->disableAutoRUM();
    }

    public function noticeThrowable(\Throwable $e, ?string $message = null): void
    {
        $this->logger->debug('Sending exception to New Relic', [
            'message' => $message,
            'exception' => $e,
        ]);

        $this->interactor->noticeThrowable($e, $message);
    }

    public function enableBackgroundJob(): void
    {
        $this->logger->debug('Enabling New Relic background job');

        $this->interactor->enableBackgroundJob();
    }

    public function disableBackgroundJob(): void
    {
        $this->logger->debug('Disabling New Relic background job');

        $this->interactor->disableBackgroundJob();
    }

    public function getLinkingMetadata(): array
    {
        $linkingMetadata = $this->interactor->getLinkingMetadata();

        $this->logger->debug('Getting New Relic linking metadata', $linkingMetadata);

        return $linkingMetadata;
    }

    public function getTraceMetadata(): array
    {
        $traceMetadata = $this->interactor->getTraceMetadata();

        $this->logger->debug('Getting New Relic trace metadata', $traceMetadata);

        return $traceMetadata;
    }
}
