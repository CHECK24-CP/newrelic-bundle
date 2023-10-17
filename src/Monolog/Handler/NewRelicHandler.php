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

namespace Check24\NewRelicBundle\Monolog\Handler;

use Check24\NewRelicBundle\Monolog\Formatter\NewRelicFormatter;
use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Utils;

class NewRelicHandler extends AbstractProcessingHandler
{
    /**
     * @var array<LogRecord>
     */
    private array $buffer = [];

    public function __construct(
        private readonly Config $config,
        private readonly NewRelicInteractorInterface $interactor,
        private readonly int $bufferSize = 100,
        private readonly string $endpoint = 'https://log-api.eu.newrelic.com/log/v1',
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $this->buffer[] = $record;

        if (\count($this->buffer) >= $this->bufferSize) {
            $this->flushBuffer();
        }
    }

    public function close(): void
    {
        $this->flushBuffer();
    }

    public function reset(): void
    {
        $this->flushBuffer();

        $this->resetProcessors();
    }

    protected function flushBuffer(): void
    {
        if (!$this->buffer) {
            return;
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            \CURLOPT_URL => $this->endpoint,
            \CURLOPT_POST => true,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Api-Key: ' . $this->config->license],
            \CURLOPT_POSTFIELDS => Utils::jsonEncode([[
                'common' => ['entity.name' => $this->config->appname] + $this->interactor->getLinkingMetadata(),
                'logs' => $this->getFormatter()->formatBatch($this->buffer),
            ]]),
        ]);

        Curl\Util::execute($ch);

        $this->buffer = [];
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new NewRelicFormatter();
    }
}
