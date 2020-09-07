<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateMigrationUIElementsTest extends PHPUnit_Framework_TestCase
{
    public function testTaskFailedWillDisplayedFailureMessageBox()
    {
        $user = $this->getMockBuilder('ilObjUser')
            ->disableOriginalConstructor()
            ->getMock();

        $ui = $this->getMockBuilder('\ILIAS\DI\UIServices')
            ->disableOriginalConstructor()
            ->setMethods(array('messageBox', 'factory', 'renderer'))
            ->getMock();

        $factory = $this->getMockBuilder('\ILIAS\UI\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $button = $this->getMockBuilder('\ILIAS\UI\Component\Button')
            ->disableOriginalConstructor()
            ->setMethods(array('standard'))
            ->getMock();

        $standard = $this->getMockBuilder('\ILIAS\UI\Component\Button\Standard')
            ->disableOriginalConstructor()
            ->getMock();

        $button->method('standard')
            ->willReturn($standard);

        $factory->method('button')
            ->willReturn($button);

        $ui->method('factory')
            ->willReturn($factory);

        $renderer = $this->getMockBuilder('\ILIAS\UI\Renderer')
            ->disableOriginalConstructor()
            ->getMock();

        $ui->method('renderer')
            ->willReturn($renderer);

        $messageBox = $this->getMockBuilder('\ILIAS\UI\Component\MessageBox\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $failure = $this->getMockBuilder('\ILIAS\UI\Component\MessageBox\MessageBox')
            ->disableOriginalConstructor()
            ->getMock();

        $withButton = $this->getMockBuilder('MessageBox')
            ->disableOriginalConstructor()
            ->getMock();

        $failure->method('withButtons')
            ->willReturn($withButton);

        $messageBox->method('failure')
            ->willReturn($failure);

        $factory->method('messageBox')
            ->willReturn($messageBox);

        $language = $this->getMockBuilder('\ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $migrationUiElements = $this->getMockBuilder('\ilCertificateMigration')
            ->disableOriginalConstructor()
            ->getMock();

        $migrationUiElements->method('isTaskFailed')
            ->willReturn(true);

        $renderer->method('render')
            ->with($withButton)
            ->willReturn('<alert></alert>');

        $migrationUiElements = new ilCertificateMigrationUIElements(
            $user,
            $ui,
            $language,
            $migrationUiElements
        );

        $html = $migrationUiElements->getMigrationMessageBox('someLink');

        $this->assertSame('<alert></alert>', $html);
    }

    public function testTaskIsNotFailedWillDisplayConfirmMessageBox()
    {
        $user = $this->getMockBuilder('ilObjUser')
            ->disableOriginalConstructor()
            ->getMock();

        $ui = $this->getMockBuilder('\ILIAS\DI\UIServices')
            ->disableOriginalConstructor()
            ->setMethods(array('messageBox', 'factory', 'renderer'))
            ->getMock();

        $factory = $this->getMockBuilder('\ILIAS\UI\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $button = $this->getMockBuilder('\ILIAS\UI\Component\Button')
            ->disableOriginalConstructor()
            ->setMethods(array('standard'))
            ->getMock();

        $standard = $this->getMockBuilder('\ILIAS\UI\Component\Button\Standard')
            ->disableOriginalConstructor()
            ->getMock();

        $button->method('standard')
            ->willReturn($standard);

        $factory->method('button')
            ->willReturn($button);

        $ui->method('factory')
            ->willReturn($factory);

        $renderer = $this->getMockBuilder('\ILIAS\UI\Renderer')
            ->disableOriginalConstructor()
            ->getMock();

        $ui->method('renderer')
            ->willReturn($renderer);

        $messageBox = $this->getMockBuilder('\ILIAS\UI\Component\MessageBox\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $confirm = $this->getMockBuilder('\ILIAS\UI\Component\MessageBox\MessageBox')
            ->disableOriginalConstructor()
            ->getMock();

        $withButton = $this->getMockBuilder('MessageBox')
            ->disableOriginalConstructor()
            ->getMock();

        $confirm->method('withButtons')
            ->willReturn($withButton);

        $messageBox->method('confirmation')
            ->willReturn($confirm);

        $factory->method('messageBox')
            ->willReturn($messageBox);

        $language = $this->getMockBuilder('\ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $migrationUiElements = $this->getMockBuilder('\ilCertificateMigration')
            ->disableOriginalConstructor()
            ->getMock();

        $migrationUiElements->method('isTaskFailed')
            ->willReturn(false);

        $renderer->method('render')
            ->with($withButton)
            ->willReturn('<confirm></confirm>');

        $migrationUiElements = new ilCertificateMigrationUIElements(
            $user,
            $ui,
            $language,
            $migrationUiElements
        );

        $html = $migrationUiElements->getMigrationMessageBox('someLink');

        $this->assertSame('<confirm></confirm>', $html);
    }
}
