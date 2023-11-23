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
        global $DIC;
        parent::setUp();

        $this->testObj = new ilTestPlayerConfirmationModal($DIC['ui.renderer']);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPlayerConfirmationModal::class, $this->testObj);
    }

    public function testModalId(): void
    {
        $modalId = '12345';
        $this->testObj->setModalId($modalId);
        $this->assertEquals($modalId, $this->testObj->getModalId());
    }

    public function testHeaderText(): void
    {
        $headerText = 'testString';
        $this->testObj->setHeaderText($headerText);
        $this->assertEquals($headerText, $this->testObj->getHeaderText());
    }

    public function testConfirmationText(): void
    {
        $confirmationText = 'testString';
        $this->testObj->setConfirmationText($confirmationText);
        $this->assertEquals($confirmationText, $this->testObj->getConfirmationText());
    }

    public function testConfirmationCheckboxName(): void
    {
        $confirmationCheckboxName = 'testString';
        $this->testObj->setConfirmationCheckboxName($confirmationCheckboxName);
        $this->assertEquals($confirmationCheckboxName, $this->testObj->getConfirmationCheckboxName());
    }

    public function testConfirmationCheckboxLabel(): void
    {
        $confirmationCheckboxLabel = 'testString';
        $this->testObj->setConfirmationCheckboxLabel($confirmationCheckboxLabel);
        $this->assertEquals($confirmationCheckboxLabel, $this->testObj->getConfirmationCheckboxLabel());
    }

    public function testAddButton(): void
    {
        $this->addGlobal_lng();
        $this->addGlobal_uiFactory();

        $expected = [];

        foreach ([51, 291, 15, 681] as $id) {
            $button = ilLinkButton::getInstance();
            $button->setId((string) $id);
            $expected[] = $button;
        }

        $this->assertEquals([], $this->testObj->getButtons());
        foreach ($expected as $button) {
            $this->testObj->addButton($button);
        }

        $this->assertEquals($expected, $this->testObj->getButtons());
    }

    public function testAddParameter(): void
    {
        $this->addGlobal_ilCtrl();

        $this->addGlobal_lng();

        foreach ([51, 291, 15, 681] as $id) {
            $hiddenInput = new ilHiddenInputGUI('postVar_' . $id);
            $expected[] = $hiddenInput;
        }

        foreach ($expected ?? [] as $hiddenInput) {
            $this->testObj->addParameter($hiddenInput);
        }

        $this->assertEquals($expected ?? [], $this->testObj->getParameters());
    }

    public function testIsConfirmationCheckboxRequired(): void
    {
        $this->assertFalse($this->testObj->isConfirmationCheckboxRequired());

        $this->testObj->setConfirmationCheckboxName('testName');
        $this->testObj->setConfirmationCheckboxLabel('testLabel');
        $this->assertTrue($this->testObj->isConfirmationCheckboxRequired());
    }
}
