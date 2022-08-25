<?php

declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestAnswerOptionalQuestionsConfirmationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestAnswerOptionalQuestionsConfirmationGUITest extends ilTestBaseTestCase
{
    protected $lng_mock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lng_mock = $this->createMock(ilLanguage::class);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new ilTestAnswerOptionalQuestionsConfirmationGUI($this->lng_mock);

        $this->assertInstanceOf(ilTestAnswerOptionalQuestionsConfirmationGUI::class, $instance);
    }

    public function testGetAndSetCancelCmd(): void
    {
        $expect = "testCancelCmd";

        $gui = new ilTestAnswerOptionalQuestionsConfirmationGUI($this->lng_mock);

        $gui->setCancelCmd($expect);

        $this->assertEquals($expect, $gui->getCancelCmd());
    }

    public function testGetAndSetConfirmCmd(): void
    {
        $expect = "testConfirmCmd";

        $gui = new ilTestAnswerOptionalQuestionsConfirmationGUI($this->lng_mock);

        $gui->setConfirmCmd($expect);

        $this->assertEquals($expect, $gui->getConfirmCmd());
    }
}
