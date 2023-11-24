<?php

namespace Setup;

use ilDatabaseInitializedObjective;
use ILIAS\Setup\Environment;
use ILIAS\Test\Setup\ilManScoringSettingsToOwnDbTableMigration;
use ilTestBaseTestCase;

class ilManScoringSettingsToOwnDbTableMigrationTest extends  ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilManScoringSettingsToOwnDbTableMigration = new ilManScoringSettingsToOwnDbTableMigration();
        $this->assertInstanceOf(ilManScoringSettingsToOwnDbTableMigration::class, $ilManScoringSettingsToOwnDbTableMigration);
    }

    public function testGetLabel(): void
    {
        $ilManScoringSettingsToOwnDbTableMigration = new ilManScoringSettingsToOwnDbTableMigration();
        $this->assertEquals('Migrate manual scoring done setting from ilSettings db table to own table for improved performance', $ilManScoringSettingsToOwnDbTableMigration->getLabel());
    }

    public function testGetDefaultAmountOfStepsPerRun(): void
    {
        $ilManScoringSettingsToOwnDbTableMigration = new ilManScoringSettingsToOwnDbTableMigration();
        $this->assertEquals(10, $ilManScoringSettingsToOwnDbTableMigration->getDefaultAmountOfStepsPerRun());
    }

    public function testGetPreconditions(): void
    {
        $ilManScoringSettingsToOwnDbTableMigration = new ilManScoringSettingsToOwnDbTableMigration();
        $result = $ilManScoringSettingsToOwnDbTableMigration->getPreconditions($this->createMock(Environment::class));
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ilDatabaseInitializedObjective::class, $result[0]);
    }
}