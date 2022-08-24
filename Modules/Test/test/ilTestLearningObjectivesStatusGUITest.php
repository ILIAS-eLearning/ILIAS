<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestLearningObjectivesStatusGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestLearningObjectivesStatusGUITest extends ilTestBaseTestCase
{
    private ilTestLearningObjectivesStatusGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestLearningObjectivesStatusGUI(
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestLearningObjectivesStatusGUI::class, $this->testObj);
    }

    public function testCrsObjId(): void
    {
        $this->testObj->setCrsObjId(1240);
        $this->assertEquals(1240, $this->testObj->getCrsObjId());
    }

    public function testUsrId(): void
    {
        $this->testObj->setUsrId(1240);
        $this->assertEquals(1240, $this->testObj->getUsrId());
    }
}
