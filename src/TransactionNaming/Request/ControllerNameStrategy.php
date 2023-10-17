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

namespace Check24\NewRelicBundle\TransactionNaming\Request;

use Symfony\Component\HttpFoundation\Request;

readonly class ControllerNameStrategy implements TransactionNameStrategyInterface
{
    public function getName(Request $request): string
    {
        if (!$controller = $request->attributes->get('_controller')) {
            return 'Unknown controller';
        }

        if (\is_string($controller)) {
            return $controller;
        }

        if (\is_array($controller)) {
            return ($controller[0] ?? 'undefined') . '::' . ($controller[1] ?? 'undefined');
        }

        return 'n/a';
    }
}
