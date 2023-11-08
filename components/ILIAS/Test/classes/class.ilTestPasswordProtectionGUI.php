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

use ILIAS\Test\InternalRequestService;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 *
 * @ilCtrl_Calls ilTestPasswordProtectionGUI: ilPropertyFormGUI
 */
class ilTestPasswordProtectionGUI
{
    public const CMD_SHOW_PASSWORD_FORM = 'showPasswordForm';
    public const CMD_SAVE_ENTERED_PASSWORD = 'saveEnteredPassword';
    public const CMD_BACK_TO_INFO_SCREEN = 'backToInfoScreen';

    private string $next_command_class;
    private string $next_command_cmd;

    public function __construct(
        private ilCtrl $ctrl,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private ilTestPlayerAbstractGUI $parent_gui,
        private ilTestPasswordChecker $password_checker,
        private InternalRequestService $testrequest
    ) {
    }

    public function executeCommand(): void
    {
        $this->ctrl->saveParameter($this, 'nextCommand');
        $next_cmd = explode('::', $this->testrequest->getNextCommand());
        $this->setNextCommandClass($next_cmd[0]);
        $this->setNextCommandCmd($next_cmd[1]);

        $this->ctrl->saveParameter($this->parent_gui, 'lock');

        switch ($this->ctrl->getNextClass()) {
            default:

                $cmd = $this->ctrl->getCmd() . 'Cmd';
                $this->$cmd();
        }
    }

    protected function buildPasswordMsg(): string
    {
        if (!$this->password_checker->wrongUserEnteredPasswordExist()) {
            return '';
        }

        return ilUtil::getSystemMessageHTML(
            $this->lng->txt('tst_password_entered_wrong_password'),
            'failure'
        );
    }

    protected function buildPasswordForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("tst_password_form"));
        $form->setDescription($this->lng->txt("tst_password_introduction"));

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_ENTERED_PASSWORD, $this->lng->txt("submit"));
        $form->addCommandButton(self::CMD_BACK_TO_INFO_SCREEN, $this->lng->txt("cancel"));

        $inp = new ilPasswordInputGUI($this->lng->txt("tst_password"), 'password');
        $inp->setRequired(true);
        $inp->setRetype(false);
        $form->addItem($inp);
        return $form;
    }

    private function showPasswordFormCmd(): void
    {
        $msg = $this->buildPasswordMsg();
        $form = $this->buildPasswordForm();

        $this->tpl->setVariable(
            $this->parent_gui->getContentBlockName(),
            $msg . $this->ctrl->getHTML($form)
        );
    }

    private function saveEnteredPasswordCmd(): void
    {
        $this->password_checker->setUserEnteredPassword($_POST["password"]);

        if (!$this->password_checker->isUserEnteredPasswordCorrect()) {
            $this->password_checker->logWrongEnteredPassword();
        }

        $this->ctrl->redirectByClass($this->getNextCommandClass(), $this->getNextCommandCmd());
    }

    private function backToInfoScreenCmd(): void
    {
        $this->ctrl->redirectByClass('ilObjTestGUI', 'infoScreen');
    }

    private function setNextCommandClass(string $next_command_class): void
    {
        $this->next_command_class = $next_command_class;
    }

    private function getNextCommandClass(): string
    {
        return $this->next_command_class;
    }

    private function setNextCommandCmd(string $next_command_cmd): void
    {
        $this->next_command_cmd = $next_command_cmd;
    }

    private function getNextCommandCmd(): string
    {
        return $this->next_command_cmd;
    }
}
