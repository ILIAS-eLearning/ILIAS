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

namespace ILIAS\Tests\Setup\Objective;

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use PHPUnit\Framework\TestCase;

class MigrationObjectiveTest extends TestCase
{
    public function testMigrationWithoutRemainingStepsIsNotApplicableAndTellsWhy(): void
    {
        $migration = $this->getMigration(0);
        $io = $this->createMock(Setup\AdminInteraction::class);
        $io->expects($this->once())
           ->method('inform')
           ->with($this->stringContains('has no remaining steps left'));

        $objective = new Objective\MigrationObjective($migration);
        $environment = new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        $this->assertFalse($objective->isApplicable($environment));
    }

    public function testMigrationWithRemainingStepsIsApplicable(): void
    {
        $migration = $this->getMigration(3);
        $io = $this->createMock(Setup\AdminInteraction::class);
        $io->expects($this->never())
           ->method('inform');

        $objective = new Objective\MigrationObjective($migration);
        $environment = new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        $this->assertTrue($objective->isApplicable($environment));
    }

    public function testMigrationWithoutRemainingStepsAndWithoutInteraction(): void
    {
        $objective = new Objective\MigrationObjective($this->getMigration(0));

        $this->assertFalse($objective->isApplicable(new Setup\ArrayEnvironment([])));
    }

    private function getMigration(int $remaining_steps): Setup\Migration
    {
        $migration = $this->createMock(Setup\Migration::class);
        $migration->expects($this->once())
                  ->method('prepare');
        $migration->method('getRemainingAmountOfSteps')
                  ->willReturn($remaining_steps);
        $migration->method('getDefaultAmountOfStepsPerRun')
                  ->willReturn(1);

        return $migration;
    }
}
