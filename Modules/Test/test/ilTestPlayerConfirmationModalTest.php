<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPlayerConfirmationModalTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPlayerConfirmationModalTest extends ilTestBaseTestCase
{
    private ilTestPlayerConfirmationModal $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestPlayerConfirmationModal();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestPlayerConfirmationModal::class, $this->testObj);
    }

    public function testModalId() : void
    {
        $this->testObj->setModalId("12345");
        $this->assertEquals("12345", $this->testObj->getModalId());
    }

    public function testHeaderText() : void
    {
        $this->testObj->setHeaderText("testString");
        $this->assertEquals("testString", $this->testObj->getHeaderText());
    }

    public function testConfirmationText() : void
    {
        $this->testObj->setConfirmationText("testString");
        $this->assertEquals("testString", $this->testObj->getConfirmationText());
    }

    public function testConfirmationCheckboxName() : void
    {
        $this->testObj->setConfirmationCheckboxName("testString");
        $this->assertEquals("testString", $this->testObj->getConfirmationCheckboxName());
    }

    public function testConfirmationCheckboxLabel() : void
    {
        $this->testObj->setConfirmationCheckboxLabel("testString");
        $this->assertEquals("testString", $this->testObj->getConfirmationCheckboxLabel());
    }

    public function testAddButton() : void
    {
        $this->addGlobal_lng();
        $expected = [];

        foreach ([51, 291, 15, 681] as $id) {
            $button = ilLinkButton::getInstance();
            $button->setId((string) $id);
            $expected[] = $button;
        }

        foreach ($expected as $button) {
            $this->testObj->addButton($button);
        }

        $this->assertEquals($expected, $this->testObj->getButtons());
    }

    public function testAddParameter() : void
    {
        $this->addGlobal_ilCtrl();

        $this->addGlobal_lng();
        $expected = [];

        foreach ([51, 291, 15, 681] as $id) {
            $hiddenInput = new ilHiddenInputGUI("postVar" . "_" . $id);
            $expected[] = $hiddenInput;
        }

        foreach ($expected as $hiddenInput) {
            $this->testObj->addParameter($hiddenInput);
        }

        $this->assertEquals($expected, $this->testObj->getParameters());
    }

    public function testIsConfirmationCheckboxRequired() : void
    {
        $this->assertFalse($this->testObj->isConfirmationCheckboxRequired());

        $this->testObj->setConfirmationCheckboxName("testName");
        $this->testObj->setConfirmationCheckboxLabel("testLabel");
        $this->assertTrue($this->testObj->isConfirmationCheckboxRequired());
    }

    public function testBuildModalButtonInstance() : void
    {
        $this->addGlobal_lng();

        $result = $this->testObj->buildModalButtonInstance("201");
        $this->assertInstanceOf(ilLinkButton::class, $result);
        $this->assertEquals("201", $result->getId());
    }
}
