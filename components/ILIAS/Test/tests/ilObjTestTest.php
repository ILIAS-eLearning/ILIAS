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
 * Class ilObjTestTest
 *
 * @runTestsInSeparateProcesses
 *
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestTest extends ilTestBaseTestCase
{
    private ilObjTest $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilBench();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();
        $this->addGlobal_ilias();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilErr();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilSetting();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();
        $this->addGlobal_filesystem();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_ilComponentFactory();
        $this->addGlobal_ilAccess();

        $this->testObj = new ilObjTest();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjTest::class, $this->testObj);
    }

    public function testTmpCopyWizardCopyId(): void
    {
        $tmpCopyWizardCopyId = 12;
        $this->testObj->setTmpCopyWizardCopyId($tmpCopyWizardCopyId);
        $this->assertEquals($tmpCopyWizardCopyId, $this->testObj->getTmpCopyWizardCopyId());
    }

    public function testTestId(): void
    {
        $a_id = 15;
        $this->testObj->setTestId($a_id);
        $this->assertEquals($a_id, $this->testObj->getTestId());
    }
}
