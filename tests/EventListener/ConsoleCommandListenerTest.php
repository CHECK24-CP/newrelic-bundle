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

namespace Tests\Check24\NewRelicBundle\EventListener;

use Check24\NewRelicBundle\EventListener\ConsoleCommandListener;
use Check24\NewRelicBundle\NewRelic\Config;
use Check24\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Check24\NewRelicBundle\Trace\TraceId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleCommandListenerTest extends TestCase
{
    private ConsoleCommandListener $listener;
    private NewRelicInteractorInterface&MockObject $interactor;

    protected function setUp(): void
    {
        $this->listener = new ConsoleCommandListener(
            ['excluded:command'],
            new Config('app', 'license'),
            $this->interactor = $this->createMock(NewRelicInteractorInterface::class),
            new TraceId('some-id'),
        );
    }

    public function testItSendTransactionToNewRelic(): void
    {
        $definition = new InputDefinition([
            new InputOption('foo'),
            new InputOption('foobar', 'fb', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            new InputArgument('name', InputArgument::REQUIRED),
        ]);

        $this->interactor->expects($matcher = self::exactly(5))
            ->method('addCustomParameter')
            ->willReturnCallback(function (string $key, string $value) use ($matcher) {
                match ($matcher->numberOfInvocations()) {
                    1 => self::assertSame([$key, $value], ['traceId', 'some-id']),
                    2 => self::assertSame([$key, $value], ['name', 'bar']),
                    3 => self::assertSame([$key, $value], ['--foo', '1']),
                    4 => self::assertSame([$key, $value], ['--foobar[0]', 'baz']),
                    5 => self::assertSame([$key, $value], ['--foobar[1]', 'baz_2']),
                    default => self::fail(),
                };

                return true;
            });

        $this->interactor->expects(self::once())
            ->method('setApplicationName')
            ->with('app', 'license', false);

        $this->interactor->expects(self::once())
            ->method('setTransactionName')
            ->with('test:newrelic');

        $command = new Command('test:newrelic');
        $input = new ArrayInput([
            '--foo' => true,
            '--foobar' => ['baz', 'baz_2'],
            'name' => 'bar',
        ], $definition);

        $this->listener->__invoke(
            new ConsoleCommandEvent(
                $command,
                $input,
                $this->createMock(OutputInterface::class),
            ),
        );
    }

    public function testItIgnoresCommands(): void
    {
        $definition = new InputDefinition([
            new InputOption('foo'),
            new InputOption('foobar', 'fb', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            new InputArgument('name', InputArgument::REQUIRED),
        ]);

        $this->interactor->expects(self::once())
            ->method('setApplicationName')
            ->with('app', 'license', false);

        $this->interactor->expects(self::never())
            ->method('addCustomParameter');

        $this->interactor->expects(self::never())
            ->method('setTransactionName');

        $command = new Command('excluded:command');
        $input = new ArrayInput([
            '--foo' => true,
            '--foobar' => ['baz', 'baz_2'],
            'name' => 'bar',
        ], $definition);

        $this->listener->__invoke(
            new ConsoleCommandEvent(
                $command,
                $input,
                $this->createMock(OutputInterface::class),
            ),
        );
    }
}
