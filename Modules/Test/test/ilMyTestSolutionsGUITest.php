<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMyTestSolutionsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMyTestSolutionsGUITest extends ilTestBaseTestCase
{
    private ilMyTestSolutionsGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilMyTestSolutionsGUI();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilMyTestSolutionsGUI::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $obj_mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($obj_mock);

        $this->assertEquals($obj_mock, $this->testObj->getTestObj());
    }

    public function testTestAccess(): void
    {
        $obj_mock = $this->createMock(ilTestAccess::class);
        $this->testObj->setTestAccess($obj_mock);

        $this->assertEquals($obj_mock, $this->testObj->getTestAccess());
    }

    public function testObjectiveParent(): void
    {
        $obj_mock = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj->setObjectiveParent($obj_mock);

        $this->assertEquals($obj_mock, $this->testObj->getObjectiveParent());
    }
}
