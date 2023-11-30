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