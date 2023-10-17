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

namespace Check24\NewRelicBundle\Monolog\Handler\Curl;

/**
 * @internal
 *
 * @see \Monolog\Handler\Curl\Util::execute()
 */
class Util
{
    /** @var array<int> */
    private static array $retriableErrorCodes = [
        \CURLE_COULDNT_RESOLVE_HOST,
        \CURLE_COULDNT_CONNECT,
        \CURLE_HTTP_NOT_FOUND,
        \CURLE_READ_ERROR,
        \CURLE_OPERATION_TIMEOUTED,
        \CURLE_HTTP_POST_ERROR,
        \CURLE_SSL_CONNECT_ERROR,
    ];

    /**
     * Executes a CURL request with optional retries and exception on failure.
     *
     * @param \CurlHandle $ch curl handler
     *
     * @see curl_exec
     */
    public static function execute(\CurlHandle $ch, int $retries = 5): bool|string
    {
        while ($retries--) {
            $curlResponse = curl_exec($ch);
            if (false === $curlResponse) {
                $curlErrno = curl_errno($ch);

                if (false === \in_array($curlErrno, self::$retriableErrorCodes, true) || 0 === $retries) {
                    $curlError = curl_error($ch);

                    curl_close($ch);

                    throw new \RuntimeException(sprintf('Curl error (code %d): %s', $curlErrno, $curlError));
                }

                continue;
            }

            curl_close($ch);

            return $curlResponse;
        }

        return false;
    }
}
