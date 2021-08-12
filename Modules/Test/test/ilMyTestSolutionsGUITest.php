<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMyTestSolutionsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMyTestSolutionsGUITest extends ilTestBaseTestCase
{
    private ilMyTestSolutionsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilMyTestSolutionsGUI();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilMyTestSolutionsGUI::class, $this->testObj);
    }
}