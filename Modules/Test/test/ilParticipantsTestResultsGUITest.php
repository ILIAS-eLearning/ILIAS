<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilParticipantsTestResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilParticipantsTestResultsGUITest extends ilTestBaseTestCase
{
    private ilParticipantsTestResultsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilParticipantsTestResultsGUI();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilParticipantsTestResultsGUI::class, $this->testObj);
    }
}