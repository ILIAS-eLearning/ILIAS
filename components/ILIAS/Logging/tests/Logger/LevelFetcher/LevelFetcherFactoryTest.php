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

namespace ILIAS\Logging\Logger\LevelFetcher;

use PHPUnit\Framework\TestCase;
use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfigInterface;
use ILIAS\Logging\Config\ByComponent\ConfigInterface as ByComponentConfigInterface;
use ILIAS\Logging\ILIASLogLevel;

class LevelFetcherFactoryTest extends TestCase
{
    public function testDefaultLevelFetcher(): void
    {
        $expected_level = ILIASLogLevel::ERROR;

        $basic_config = $this->createMock(BasicConfigInterface::class);
        $basic_config
            ->expects($this->atLeastOnce())
            ->method('defaultLevel')
            ->willReturn($expected_level);

        $factory = new LevelFetcherFactory();
        $fetcher = $factory->defaultLevelFetcher($basic_config);
        $actual_level = $fetcher->fetchLevel();

        $this->assertSame($expected_level, $actual_level);
    }

    public function testDefaultLevelFetcherIsLazy(): void
    {
        $basic_config = $this->createMock(BasicConfigInterface::class);
        $basic_config
            ->expects($this->never())
            ->method($this->anything());

        $factory = new LevelFetcherFactory();
        $fetcher = $factory->defaultLevelFetcher($basic_config);
    }

    public function testComponentLevelFetcher(): void
    {
        $expected_level = ILIASLogLevel::ALERT;
        $expected_component = 'comp_id';

        $by_component_config = $this->createMock(ByComponentConfigInterface::class);
        $by_component_config
            ->expects($this->atLeastOnce())
            ->method('level')
            ->with($expected_component)
            ->willReturn($expected_level);

        $factory = new LevelFetcherFactory();
        $fetcher = $factory->componentLevelFetcher($by_component_config, $expected_component);
        $actual_level = $fetcher->fetchLevel();

        $this->assertSame($expected_level, $actual_level);
    }

    public function testComponentLevelFetcherIsLazy(): void
    {
        $by_component_config = $this->createMock(ByComponentConfigInterface::class);
        $by_component_config
            ->expects($this->never())
            ->method($this->anything());

        $factory = new LevelFetcherFactory();
        $fetcher = $factory->componentLevelFetcher($by_component_config, 'comp_id');
    }
}
