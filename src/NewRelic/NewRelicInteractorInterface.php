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

interface NewRelicInteractorInterface
{
    /**
     * Sets the New Relic app name, which controls data rollup.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_set_appname}
     */
    public function setApplicationName(string $name, string $license, bool $xmit = false): bool;

    /**
     * Set custom name for current transaction.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_name_transaction}
     */
    public function setTransactionName(string $name): bool;

    /**
     * If you previously ended a transaction you many want to start a new one.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_start_transaction}
     */
    public function startTransaction(string $name, string $license): bool;

    /**
     * Stop instrumenting the current transaction immediately.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_end_transaction}
     */
    public function endTransaction(bool $ignore = false): bool;

    /**
     * Do not instrument the current transaction.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_ignore_transaction}
     */
    public function ignoreTransaction(): void;

    /**
     * Record a custom event with the given name and attributes.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_record_custom_event}
     *
     * @param array<string, scalar> $attributes
     */
    public function addCustomEvent(string $name, array $attributes): void;

    /**
     * Add a custom metric (in milliseconds) to time a component of your app not captured by default.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newreliccustommetric-php-agent-api}
     */
    public function addCustomMetric(string $name, float $value): bool;

    /**
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_add_custom_parameter}.
     */
    public function addCustomParameter(string $name, bool|float|int|string $value): bool;

    /**
     * Returns a New Relic Browser snippet to inject in the head of your HTML output.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_get_browser_timing_header}
     */
    public function getBrowserTimingHeader(bool $includeTags = true): string;

    /**
     * Returns a New Relic Browser snippet to inject at the end of the HTML output.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_get_browser_timing_footer}
     */
    public function getBrowserTimingFooter(bool $includeTags = true): string;

    /**
     * Disable automatic injection of the New Relic Browser snippet on particular pages.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_disable_autorum}
     */
    public function disableAutoRUM(): ?bool;

    /**
     * Use these calls to collect errors that the PHP agent does not collect automatically and to set the callback for
     * your own error and exception handler.
     *
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_notice_error}
     */
    public function noticeThrowable(\Throwable $e, string $message = null): void;

    /**
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_background_job}.
     */
    public function enableBackgroundJob(): void;

    /**
     * {@link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_background_job}.
     */
    public function disableBackgroundJob(): void;

    /**
     * Returns a collection of metadata necessary for linking data to a trace or an entity.
     *
     * @see https://docs.newrelic.com/docs/apm/agents/php-agent/php-agent-api/newrelicgetlinkingmetadata/
     *
     * @return array{
     *      "entity.name"?: string,
     *      "entity.type"?: string,
     *      "entity.guid"?: string,
     *      hostname?: string,
     *  }
     */
    public function getLinkingMetadata(): array;

    /**
     * Returns an associative array containing the identifiers of the current trace and the parent span.
     *
     * @see https://docs.newrelic.com/docs/apm/agents/php-agent/php-agent-api/newrelicgetlinkingmetadata/
     *
     * @return array{
     *      "trace_id"?: string,
     *      "span_id"?: string,
     *  }
     */
    public function getTraceMetadata(): array;
}
