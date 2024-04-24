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

/**
 * Class ilMyTestSolutionsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMyTestSolutionsGUITest extends ilTestBaseTestCase
{
    private ilMyTestSolutionsGUI $testObj;
    private ilObjTest $test;
    private ilTestAccess $access;
    private ilTestObjectiveOrientedContainer $objective_parent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->test = $this->createMock(ilObjTest::class);
        $this->access = $this->createMock(ilTestAccess::class);
        $this->objective_parent = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj = new ilMyTestSolutionsGUI(
            $this->test,
            $this->access,
            $this->objective_parent,
            $this->createMock(ilLanguage::class),
            $this->createMock(ilCtrlInterface::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class),
            $this->createMock(\ILIAS\TestQuestionPool\RequestDataCollector::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilMyTestSolutionsGUI::class, $this->testObj);
    }
}
