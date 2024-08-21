<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Class ilTestPlayerConfirmationModalTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPlayerConfirmationModalTest extends ilTestBaseTestCase
{
    private ilTestPlayerConfirmationModal $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;
        $ui = $DIC->ui();

        $this->testObj = new ilTestPlayerConfirmationModal($ui->renderer(), $ui->factory());
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestPlayerConfirmationModal::class, $this->testObj);
    }

    public function testSetAndGetHeaderText(): void
    {
        $header_text = 'testString';
        $this->assertEquals($this->testObj, $this->testObj->setHeaderText($header_text));
        $this->assertEquals($header_text, $this->testObj->getHeaderText());
    }

    public function testSetAndGetConfirmationText(): void
    {
        $confirmation_text = 'testString';
        $this->assertEquals($this->testObj, $this->testObj->setConfirmationText($confirmation_text));
        $this->assertEquals($confirmation_text, $this->testObj->getConfirmationText());
    }

    public function testSetAndGetConfirmationCheckboxName(): void
    {
        $confirmation_checkbox_name = 'testString';
        $this->assertEquals($this->testObj, $this->testObj->setConfirmationCheckboxName($confirmation_checkbox_name));
        $this->assertEquals($confirmation_checkbox_name, $this->testObj->getConfirmationCheckboxName());
    }

    public function testSetAndGetConfirmationCheckboxLabel(): void
    {
        $confirmation_checkbox_label = 'testString';
        $this->assertEquals($this->testObj, $this->testObj->setConfirmationCheckboxLabel($confirmation_checkbox_label));
        $this->assertEquals($confirmation_checkbox_label, $this->testObj->getConfirmationCheckboxLabel());
    }

    public function testSetAndGetActionButtonLabel(): void
    {
        $action_button_label = 'testString';
        $this->assertEquals($this->testObj, $this->testObj->setActionButtonLabel($action_button_label));
        $this->assertEquals($action_button_label, $this->testObj->getActionButtonLabel());
    }

    public function testAddAndGetParameters(): void
    {
        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();

        foreach ([51, 291, 15, 681] as $id) {
            $hiddenInput = new ilHiddenInputGUI('postVar_' . $id);
            $expected[] = $hiddenInput;
            $this->testObj->addParameter($hiddenInput);
        }

        $this->assertEquals($expected ?? [], $this->testObj->getParameters());
    }

    public function testIsConfirmationCheckboxRequired(): void
    {
        $this->assertFalse($this->testObj->isConfirmationCheckboxRequired());

        $this->assertEquals($this->testObj, $this->testObj->setConfirmationCheckboxName('testName'));
        $this->assertEquals($this->testObj, $this->testObj->setConfirmationCheckboxLabel('testLabel'));
        $this->assertTrue($this->testObj->isConfirmationCheckboxRequired());
    }
}
