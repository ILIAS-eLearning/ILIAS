<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMyTestResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMyTestResultsGUITest extends ilTestBaseTestCase
{
    private ilMyTestResultsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilMyTestResultsGUI();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilMyTestResultsGUI::class, $this->testObj);
    }
}