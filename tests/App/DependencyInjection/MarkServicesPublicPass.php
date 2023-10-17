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

namespace Tests\Check24\NewRelicBundle\App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MarkServicesPublicPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getAliases() as $interface => $alias) {
            if (!str_starts_with($interface, 'Check24\\NewRelicBundle\\')) {
                continue;
            }

            $alias->setPublic(true);
        }

        foreach ($container->getDefinitions() as $definition) {
            if (!str_starts_with($definition->getClass() ?? '', 'Check24\\NewRelicBundle\\')) {
                continue;
            }

            $definition->setPublic(true);
        }
    }
}
