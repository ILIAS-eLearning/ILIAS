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

namespace ILIAS\Logging\Config\ByComponent;

use PHPUnit\Framework\TestCase;
use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfigInterface;
use ILIAS\Logging\ILIASLogLevel;
use PHPUnit\Framework\Attributes\TestWith;

class ConfigTest extends TestCase
{
    public function testLazyReading(): void
    {
        $repo = $this->createMock(RepositoryInterface::class);
        $basic_config = $this->createMock(BasicConfigInterface::class);

        $repo
            ->expects($this->never())
            ->method($this->anything());
        $basic_config
            ->expects($this->never())
            ->method($this->anything());

        $config = new Config($repo, $basic_config);
    }

    public function testLevel(): void
    {
        $expected_component = 'comp_id';
        $expected_level = ILIASLogLevel::WARNING;

        $repo = $this->createMock(RepositoryInterface::class);
        $basic_config = $this->createStub(BasicConfigInterface::class);

        $repo
            ->expects($this->once())
            ->method('getAllLevelsForComponents')
            ->willReturn([
                'other_component' => ILIASLogLevel::CRITICAL,
                'comp_id' => $expected_level
            ]);

        $config = new Config($repo, $basic_config);
        $actual_level = $config->level($expected_component);

        $this->assertSame($expected_level, $actual_level);
    }

    public function testLevelWithDefaultBecauseNoValue(): void
    {
        $expected_component = 'comp_id';
        $expected_level = ILIASLogLevel::EMERGENCY;

        $repo = $this->createMock(RepositoryInterface::class);
        $basic_config = $this->createMock(BasicConfigInterface::class);

        $repo
            ->expects($this->once())
            ->method('getAllLevelsForComponents')
            ->willReturn(array_merge(['other_component' => ILIASLogLevel::CRITICAL]));
        $basic_config
            ->expects($this->atLeastOnce())
            ->method('defaultLevel')
            ->willReturn($expected_level);

        $config = new Config($repo, $basic_config);
        $actual_level = $config->level($expected_component);

        $this->assertSame(
            $expected_level,
            $expected_level,
            'When there is nothing set explicitly, level should fall back to the default.'
        );
    }

    public function testLevelOnlyReadOutOnce(): void
    {
        $repo = $this->createMock(RepositoryInterface::class);
        $basic_config = $this->createStub(BasicConfigInterface::class);

        $expected_component_1 = 'comp_1';
        $expected_component_2 = 'comp_2';
        $expected_level_1 = ILIASLogLevel::ALERT;
        $expected_level_2 = ILIASLogLevel::CRITICAL;

        $repo
            ->expects($this->once())
            ->method('getAllLevelsForComponents')
            ->willReturn([
                $expected_component_1 => $expected_level_1,
                $expected_component_2 => $expected_level_2
            ]);

        $config = new Config($repo, $basic_config);
        $level_1 = $config->level('comp_1');
        $level_2 = $config->level('comp_2');
        $level_1_again = $config->level('comp_1');


        $this->assertSame(
            $level_1,
            $level_1_again,
            'Repeated reads on the same component should give the same level.'
        );
        $this->assertNotSame(
            $level_1,
            $level_2,
            'Repeated reads on the different component should give the same level.'
        );
    }
}
