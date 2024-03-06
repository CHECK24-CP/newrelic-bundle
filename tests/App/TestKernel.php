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

namespace Tests\Check24\NewRelicBundle\App;

use Check24\NewRelicBundle\Check24NewRelicBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Tests\Check24\NewRelicBundle\App\DependencyInjection\MarkServicesPublicPass;

class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new MonologBundle(),
            new Check24NewRelicBundle(),
            new SecurityBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(static function (ContainerBuilder $container) {
            $container->addCompilerPass(new MarkServicesPublicPass());

            $container->loadFromExtension('framework', [
                'secret' => 'this is a simple secret',
                'messenger' => [
                    'buses' => [
                        'default' => [
                            'middleware' => ['check24.new_relic.messenger_middleware'],
                        ],
                    ],
                ],
                'router' => ['resource' => 'kernel::loadRoutes', 'type' => 'service', 'utf8' => true],
            ]);

            $container->loadFromExtension('check24_new_relic', [
                'appname' => 'myapp',
                'license' => 'dummyLicense',
                'logging' => [
                    'buffer_size' => 30,
                ],
            ]);

            $container->loadFromExtension('monolog', [
                'handlers' => [
                    'main' => [
                        'type' => 'fingers_crossed',
                        'action_level' => 'error',
                        'handler' => 'nested',
                        'excluded_http_codes' => [
                            404,
                            405,
                        ],
                        'channels' => [
                            '!event',
                        ],
                    ],
                    'nested' => [
                        'type' => 'service',
                        'id' => 'check24.new_relic.monolog_handler',
                    ],
                ],
            ]);
            $container->loadFromExtension('security', [
                'providers' => ['users_in_memory' => ['memory' => null]],
                'firewalls' => ['main' => ['provider' => 'users_in_memory']],
            ]);
        });
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/cache/' . $this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/cache/' . $this->getEnvironment();
    }
}
