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

readonly class NewRelicInteractor implements NewRelicInteractorInterface
{
    public function setApplicationName(string $name, string $license, bool $xmit = false): bool
    {
        return newrelic_set_appname($name, $license, $xmit);
    }

    public function setTransactionName(string $name): bool
    {
        return newrelic_name_transaction($name);
    }

    public function startTransaction(string $name, string $license): bool
    {
        return newrelic_start_transaction($name, $license);
    }

    public function endTransaction(bool $ignore = false): bool
    {
        return newrelic_end_transaction($ignore);
    }

    public function ignoreTransaction(): void
    {
        newrelic_ignore_transaction();
    }

    public function addCustomEvent(string $name, array $attributes): void
    {
        newrelic_record_custom_event($name, $attributes);
    }

    public function addCustomMetric(string $name, float $value): bool
    {
        return newrelic_custom_metric($name, $value);
    }

    public function addCustomParameter(string $name, bool|float|int|string $value): bool
    {
        return newrelic_add_custom_parameter($name, $value);
    }

    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        return newrelic_get_browser_timing_header($includeTags);
    }

    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        return newrelic_get_browser_timing_footer($includeTags);
    }

    public function disableAutoRUM(): ?bool
    {
        return newrelic_disable_autorum();
    }

    public function noticeThrowable(\Throwable $e, ?string $message = null): void
    {
        newrelic_notice_error($message ?: $e->getMessage(), $e);
    }

    public function enableBackgroundJob(): void
    {
        newrelic_background_job(true);
    }

    public function disableBackgroundJob(): void
    {
        newrelic_background_job(false);
    }

    public function getLinkingMetadata(): array
    {
        return newrelic_get_linking_metadata();
    }

    public function getTraceMetadata(): array
    {
        return newrelic_get_trace_metadata();
    }
}
