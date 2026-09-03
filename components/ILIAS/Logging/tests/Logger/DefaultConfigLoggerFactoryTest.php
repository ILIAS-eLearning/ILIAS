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
use ILIAS\Logging\Logger\LevelFetcher\LevelFetcherInterface;
use ILIAS\Logging\Logger\LevelFetcher\LevelFetcherFactoryInterface;

class DefaultConfigLoggerFactoryTest extends TestCase
{
    public function testGetLazy(): void
    {
        $expected_component_id = 'comp_id';
        $expected_logger = $this->createStub(LoggerInterface::class);
        $expected_level_fectcher = $this->createStub(LevelFetcherInterface::class);

        $internal_factory = $this->createMock(LazyInternalFactoryInterface::class);
        $internal_factory
            ->expects($this->atLeastOnce())
            ->method('getLazyGhost')
            ->with($expected_component_id, $expected_level_fectcher)
            ->willReturn($expected_logger);

        $basic_config = $this->createMock(BasicConfigInterface::class);
        $basic_config
            ->expects($this->never())
            ->method($this->anything());

        $level_fetcher_factory = $this->createMock(LevelFetcherFactoryInterface::class);
        $level_fetcher_factory
            ->expects($this->atLeastOnce())
            ->method('defaultLevelFetcher')
            ->with($basic_config)
            ->willReturn($expected_level_fectcher);

        $factory = new DefaultConfigLoggerFactory($internal_factory, $basic_config, $level_fetcher_factory);
        $actual_logger = $factory->getLazy($expected_component_id);

        $this->assertSame(
            $expected_logger,
            $actual_logger,
            'This should return the logger from LazyInternalFactory'
        );
    }
}
