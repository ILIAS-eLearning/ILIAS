<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsGUITest extends ilTestBaseTestCase
{
    private ilTestResultsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_lng();

        $this->testObj = new ilTestResultsGUI(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestQuestionSetConfig::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestResultsGUI::class, $this->testObj);
    }
}