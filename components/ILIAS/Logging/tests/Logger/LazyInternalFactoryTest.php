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

class LazyInternalFactoryTest extends TestCase
{
    public function testGetLazyGhostIsLazy(): void
    {
        $basic_config = $this->createMock(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher = $this->createMock(LevelFetcherInterface::class);

        $monolog_factory
            ->expects($this->never())
            ->method($this->anything());
        $basic_config
            ->expects($this->never())
            ->method($this->anything());
        $level_fetcher
            ->expects($this->never())
            ->method($this->anything());

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config);
        $logger = $lazy_factory->getLazyGhost('name', $level_fetcher);
    }

    public function testGetLazyGhostIsInitializedAfterCallWithCorrectParameters(): void
    {
        $expected_name = 'name';
        $expected_log_level = ILIASLogLevel::EMERGENCY;
        $expected_path = '/path/to/logfile';

        $basic_config = $this->createMock(BasicConfigInterface::class);
        $monolog_factory = $this->createMock(MonologFactoryInterface::class);
        $level_fetcher = $this->createMock(LevelFetcherInterface::class);

        $basic_config
            ->expects($this->atLeastOnce())
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $basic_config
            ->expects($this->atLeastOnce())
            ->method('pathToLogFile')
            ->willReturn($expected_path);
        $monolog_factory
            ->expects($this->atLeastOnce())
            ->method('logger')
            ->with($expected_name, $expected_log_level, $expected_path)
            ->willReturn($this->createStub(MonologLogger::class));
        $level_fetcher
            ->expects($this->atLeastOnce())
            ->method('fetchLevel')
            ->willReturn($expected_log_level);

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config);
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

        $monolog_factory
            ->expects($this->exactly(2))
            ->method('logger')
            ->willReturn($this->createStub(MonologLogger::class));
        $basic_config
            ->expects($this->atLeastOnce())
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $level_fetcher_1
            ->expects($this->atLeastOnce())
            ->method('fetchLevel')
            ->willReturn(ILIASLogLevel::INFO);
        $level_fetcher_2
            ->expects($this->atLeastOnce())
            ->method('fetchLevel')
            ->willReturn(ILIASLogLevel::INFO);
        $level_fetcher_3
            ->expects($this->never())
            ->method($this->anything());

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config);
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

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config);
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

        $basic_config
            ->method('isLoggingEnabled')
            ->willReturn(true);
        $monolog_factory
            ->expects($this->atLeastOnce())
            ->method('nullLogger')
            ->with($expected_name)
            ->willReturn($this->createStub(MonologLogger::class));
        $monolog_factory
            ->expects($this->never())
            ->method('logger');
        $level_fetcher
            ->expects($this->atLeastOnce())
            ->method('fetchLevel')
            ->willReturn(ILIASLogLevel::OFF);

        $lazy_factory = new LazyInternalFactory($monolog_factory, $basic_config);
        $logger = $lazy_factory->getLazyGhost($expected_name, $level_fetcher);
        // actually initialize the logger
        $logger->info('test');
    }
}
