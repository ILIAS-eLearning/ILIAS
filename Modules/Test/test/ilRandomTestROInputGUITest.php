<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRandomTestROInputGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilRandomTestROInputGUITest extends ilTestBaseTestCase
{
    private ilRandomTestROInputGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();

        $this->testObj = new ilRandomTestROInputGUI();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilRandomTestROInputGUI::class, $this->testObj);
    }

    public function testSetValues(): void
    {
        $expected = [
            "test" => "test2",
            "hello" => "world"
        ];
        $this->testObj->setValues($expected);
        $this->assertEquals($this->testObj->getValues(), $expected);
    }
}
