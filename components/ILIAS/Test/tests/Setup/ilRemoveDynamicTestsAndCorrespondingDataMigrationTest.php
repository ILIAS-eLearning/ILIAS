<?php

namespace Setup;

use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ILIAS\Setup\Environment;
use ILIAS\Test\Setup\ilRemoveDynamicTestsAndCorrespondingDataMigration;
use ilTestBaseTestCase;

class ilRemoveDynamicTestsAndCorrespondingDataMigrationTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilRemoveDynamicTestsAndCorrespondingDataMigration = new ilRemoveDynamicTestsAndCorrespondingDataMigration();
        $this->assertInstanceOf(ilRemoveDynamicTestsAndCorrespondingDataMigration::class, $ilRemoveDynamicTestsAndCorrespondingDataMigration);
    }

    public function testGetLabel(): void
    {
        $ilRemoveDynamicTestsAndCorrespondingDataMigration = new ilRemoveDynamicTestsAndCorrespondingDataMigration();
        $this->assertEquals('Delete All Data of Dynamic Tests from Database.', $ilRemoveDynamicTestsAndCorrespondingDataMigration->getLabel());
    }

    public function testGetDefaultAmountOfStepsPerRun(): void
    {
        $ilRemoveDynamicTestsAndCorrespondingDataMigration = new ilRemoveDynamicTestsAndCorrespondingDataMigration();
        $this->assertEquals(10000, $ilRemoveDynamicTestsAndCorrespondingDataMigration->getDefaultAmountOfStepsPerRun());
    }

    public function testGetPreconditions(): void
    {
        $ilRemoveDynamicTestsAndCorrespondingDataMigration = new ilRemoveDynamicTestsAndCorrespondingDataMigration();
        $result = $ilRemoveDynamicTestsAndCorrespondingDataMigration->getPreconditions($this->createMock(Environment::class));
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ilDatabaseInitializedObjective::class, $result[0]);
        $this->assertInstanceOf(ilDatabaseUpdatedObjective::class, $result[1]);
    }
}