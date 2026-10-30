<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Logging\Logger;

use PHPUnit\Framework\TestCase;
use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfigInterface;
use ILIAS\Logging\Logger\Monolog\FactoryInterface as MonologFactoryInterface;
use Monolog\Logger as MonologLogger;
use ILIAS\Logging\ILIASLogLevel;
use ILIAS\Logging\Logger\LevelFetcher\LevelFetcherInterface;
use ILIAS\Logging\Logger\Monolog\Handler\ProviderInterface as MonologHandlerProviderInterface;
use Monolog\Handler\HandlerInterface as MonologHandlerInterface;

class LazyInternalFactoryTest extends TestCase
{
    public function testGetLazyGhostIsLazy(): void
    {
        $basic_config = $this->createMock(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher = $this->createMock(LevelFetcherInterface::class);
        $handler_provider = $this->createMock(MonologHandlerProviderInterface::class);

        $monolog_factory
            ->expects($this->never())
            ->method($this->anything());
        $basic_config
            ->expects($this->never())
            ->method($this->anything());
        $level_fetcher
            ->expects($this->never())
            ->method($this->anything());
        $handler_provider
            ->expects($this->never())
            ->method($this->anything());

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config, [$handler_provider]);
        $logger = $lazy_factory->getLazyGhost('name', $level_fetcher);
    }

    public function testGetLazyGhostIsInitializedAfterCallWithCorrectParameters(): void
    {
        $expected_name = 'name';
        $expected_log_level = ILIASLogLevel::EMERGENCY;
        $expected_handler = $this->createStub(MonologHandlerInterface::class);

        $basic_config = $this->createMock(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher = $this->createMock(LevelFetcherInterface::class);
        $handler_provider = $this->createMock(MonologHandlerProviderInterface::class);

        $basic_config
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $level_fetcher
            ->method('fetchLevel')
            ->willReturn($expected_log_level);

        $monolog_factory
            ->expects($this->atLeastOnce())
            ->method('logger')
            ->with($expected_name, $expected_handler)
            ->willReturn($this->createStub(MonologLogger::class));
        $handler_provider
            ->expects($this->atLeastOnce())
            ->method('appliesTo')
            ->with($expected_name)
            ->willReturn(true);
        $handler_provider
            ->expects($this->atLeastOnce())
            ->method('getHandler')
            ->with($expected_name, $expected_log_level)
            ->willReturn($expected_handler);

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config, [$handler_provider]);
        $logger = $lazy_factory->getLazyGhost($expected_name, $level_fetcher);
        // actually initialize the logger
        $logger->info('test');
    }

    public function testGetLazyGhostIsCached(): void
    {
        $basic_config = $this->createMock(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher_1 = $this->createMock(LevelFetcherInterface::class);
        $level_fetcher_2 = $this->createMock(LevelFetcherInterface::class);
        $level_fetcher_3 = $this->createMock(LevelFetcherInterface::class);
        $handler_provider = $this->createMock(MonologHandlerProviderInterface::class);

        $basic_config
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $monolog_factory
            ->method('logger')
            ->willReturn($this->createStub(MonologLogger::class));
        $level_fetcher_1
            ->method('fetchLevel')
            ->willReturn(ILIASLogLevel::INFO);
        $level_fetcher_2
            ->method('fetchLevel')
            ->willReturn(ILIASLogLevel::INFO);
        $handler_provider
            ->method('appliesTo')
            ->willReturn(true);
        $handler_provider
            ->method('getHandler')
            ->willReturn($this->createStub(MonologHandlerInterface::class));

        $level_fetcher_3
            ->expects($this->never())
            ->method($this->anything());

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config, [$handler_provider]);
        $logger_1 = $lazy_factory->getLazyGhost('name', $level_fetcher_1);
        $logger_2 = $lazy_factory->getLazyGhost('another name', $level_fetcher_2);
        $logger_3 = $lazy_factory->getLazyGhost('name', $level_fetcher_3);

        // actually initialize the loggers
        $logger_1->info('test');
        $logger_2->info('test');
        $logger_3->info('test');

        $this->assertSame($logger_1, $logger_3, 'There should only be one logger per component ID.');
        $this->assertNotSame($logger_1, $logger_2, 'Loggers with different component ID should be different.');
    }

    public function testGetLazyGhostWhenLoggingIsDisabled(): void
    {
        $expected_name = 'name';

        $basic_config = $this->createStub(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher = $this->createMock(LevelFetcherInterface::class);
        $handler_provider = $this->createMock(MonologHandlerProviderInterface::class);

        $basic_config
            ->method('isLoggingEnabled')
            ->willReturn(false);

        $monolog_factory
            ->expects($this->atLeastOnce())
            ->method('nullLogger')
            ->with($expected_name)
            ->willReturn($this->createStub(MonologLogger::class));
        $monolog_factory
            ->expects($this->never())
            ->method('logger');
        $level_fetcher
            ->expects($this->never())
            ->method($this->anything());
        $handler_provider
            ->expects($this->never())
            ->method($this->anything());

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config, [$handler_provider]);
        $logger = $lazy_factory->getLazyGhost($expected_name, $level_fetcher);
        // actually initialize the logger
        $logger->info('test');
    }

    public function testGetLazyGhostWhenLogLevelIsOff(): void
    {
        $expected_name = 'name';

        $basic_config = $this->createStub(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher = $this->createMock(LevelFetcherInterface::class);
        $handler_provider = $this->createMock(MonologHandlerProviderInterface::class);

        $basic_config
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $level_fetcher
            ->method('fetchLevel')
            ->willReturn(ILIASLogLevel::OFF);

        $monolog_factory
            ->expects($this->atLeastOnce())
            ->method('nullLogger')
            ->with($expected_name)
            ->willReturn($this->createStub(MonologLogger::class));
        $monolog_factory
            ->expects($this->never())
            ->method('logger');
        $handler_provider
            ->expects($this->never())
            ->method($this->anything());

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config, [$handler_provider]);
        $logger = $lazy_factory->getLazyGhost($expected_name, $level_fetcher);
        // actually initialize the logger
        $logger->info('test');
    }

    public function testGetLazyGhostWhenHandlerIsNotApplicable(): void
    {
        $expected_name = 'name';

        $basic_config = $this->createMock(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher = $this->createMock(LevelFetcherInterface::class);
        $handler_provider = $this->createMock(MonologHandlerProviderInterface::class);

        $basic_config
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $level_fetcher
            ->method('fetchLevel')
            ->willReturn(ILIASLogLevel::EMERGENCY);
        $handler_provider
            ->method('appliesTo')
            ->willReturn(false);

        $monolog_factory
            ->expects($this->atLeastOnce())
            ->method('logger')
            ->with($expected_name) // no handler!
            ->willReturn($this->createStub(MonologLogger::class));
        $handler_provider
            ->expects($this->never())
            ->method('getHandler');

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config, [$handler_provider]);
        $logger = $lazy_factory->getLazyGhost($expected_name, $level_fetcher);
        // actually initialize the logger
        $logger->info('test');
    }

    public function testGetLazyGhostWhenHandlersOnlyApplyToSomeLoggers(): void
    {
        $expected_name_1 = 'name 1';
        $expected_name_2 = 'name 2';

        $basic_config = $this->createMock(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher = $this->createMock(LevelFetcherInterface::class);
        $handler_provider_1 = $this->createMock(MonologHandlerProviderInterface::class);
        $handler_1 = $this->createStub(MonologHandlerInterface::class);
        $handler_provider_2 = $this->createMock(MonologHandlerProviderInterface::class);
        $handler_2 = $this->createStub(MonologHandlerInterface::class);

        $basic_config
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $level_fetcher
            ->method('fetchLevel')
            ->willReturn(ILIASLogLevel::EMERGENCY);
        $handler_provider_1
            ->method('appliesTo')
            ->willReturnMap([[$expected_name_1, true], [$expected_name_2, false]]);
        $handler_provider_1
            ->method('getHandler')
            ->willReturn($handler_1);
        $handler_provider_2
            ->method('appliesTo')
            ->willReturnMap([[$expected_name_1, false], [$expected_name_2, true]]);
        $handler_provider_2
            ->method('getHandler')
            ->willReturn($handler_2);

        $monolog_factory
            ->expects($this->atLeast(2))
            ->method('logger')
            ->withParameterSetsInOrder([$expected_name_1, $handler_1], [$expected_name_2, $handler_2])
            ->willReturn($this->createStub(MonologLogger::class));

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config, [$handler_provider_1, $handler_provider_2]);
        $logger_1 = $lazy_factory->getLazyGhost($expected_name_1, $level_fetcher);
        $logger_2 = $lazy_factory->getLazyGhost($expected_name_2, $level_fetcher);
        // actually initialize the logger
        $logger_1->info('test');
        $logger_2->info('test');
    }

    public function testGetLazyGhostWithMultipleHandlers(): void
    {
        $expected_name_1 = 'name 1';
        $expected_name_2 = 'name 2';

        $basic_config = $this->createMock(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher = $this->createMock(LevelFetcherInterface::class);
        $handler_provider_1 = $this->createMock(MonologHandlerProviderInterface::class);
        $handler_1 = $this->createStub(MonologHandlerInterface::class);
        $handler_provider_2 = $this->createMock(MonologHandlerProviderInterface::class);
        $handler_2 = $this->createStub(MonologHandlerInterface::class);

        $basic_config
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $level_fetcher
            ->method('fetchLevel')
            ->willReturn(ILIASLogLevel::EMERGENCY);
        $handler_provider_1
            ->method('appliesTo')
            ->willReturn(true);
        $handler_provider_1
            ->method('getHandler')
            ->willReturn($handler_1);
        $handler_provider_2
            ->method('appliesTo')
            ->willReturn(true);
        $handler_provider_2
            ->method('getHandler')
            ->willReturn($handler_2);

        $monolog_factory
            ->expects($this->atLeastOnce())
            ->method('logger')
            ->with($expected_name_1, $handler_1, $handler_2)
            ->willReturn($this->createStub(MonologLogger::class));

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config, [$handler_provider_1, $handler_provider_2]);
        $logger = $lazy_factory->getLazyGhost($expected_name_1, $level_fetcher);
        // actually initialize the logger
        $logger->info('test');
    }
}
