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

readonly class Config
{
    public string $appname;
    public string $license;

    /**
     * @param string|null $appname Name(s) of app metrics should be reported under in New Relic user interface. Uses the same format as newrelic.appname and can set multiple application names by separating each with a semicolon ;
     * @param bool $xmit If false or omitted, the agent discards the current transaction and all data captured up to this call is lost. If true, the agent sends the data that was gathered right before executing this call. The data is associated with the old app name. This has a very slight performance impact as it takes a few milliseconds for the agent to dump its data.
     */
    public function __construct(
        ?string $appname,
        #[\SensitiveParameter]
        ?string $license,
        public bool $xmit = false,
    ) {
        $this->appname = $appname ?? \ini_get('newrelic.appname') ?: '';
        $this->license = $license ?? \ini_get('newrelic.license') ?: '';
    }
}
