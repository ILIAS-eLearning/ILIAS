<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillEvaluationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillEvaluationGUITest extends ilTestBaseTestCase
{
    private ilTestSkillEvaluationGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillEvaluationGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillEvaluationGUI::class, $this->testObj);
    }
}