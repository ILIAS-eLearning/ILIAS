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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrl_Calls ilTestPasswordProtectionGUI: ilPropertyFormGUI
 */
class ilTestPasswordProtectionGUI
{
    public const CMD_SHOW_PASSWORD_FORM = 'showPasswordForm';
    public const CMD_SAVE_ENTERED_PASSWORD = 'saveEnteredPassword';
    public const CMD_BACK_TO_INFO_SCREEN = 'backToInfoScreen';
    private \ILIAS\Test\InternalRequestService $testrequest;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTestPlayerAbstractGUI
     */
    protected $parentGUI;

    /**
     * @var ilTestPasswordChecker
     */
    protected $passwordChecker;

    /**
     * @var string
     */
    private $nextCommandClass;

    /**
     * @var string
     */
    private $nextCommandCmd;

    public function __construct(ilCtrl $ctrl, ilGlobalTemplateInterface $tpl, ilLanguage $lng, ilTestPlayerAbstractGUI $parentGUI, ilTestPasswordChecker $passwordChecker)
    {
        global $DIC;
        $this->testrequest = $DIC->test()->internal()->request();
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->parentGUI = $parentGUI;
        $this->passwordChecker = $passwordChecker;
    }

    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'nextCommand');
        $nextCommand = explode('::', $this->testrequest->getNextCommand());
        $this->setNextCommandClass($nextCommand[0]);
        $this->setNextCommandCmd($nextCommand[1]);

        $this->ctrl->saveParameter($this->parentGUI, 'lock');

        switch ($this->ctrl->getNextClass()) {
            default:

                $cmd = $this->ctrl->getCmd() . 'Cmd';
                $this->$cmd();
        }
    }

    protected function buildPasswordMsg(): string
    {
        if (!$this->passwordChecker->wrongUserEnteredPasswordExist()) {
            return '';
        }

        return ilUtil::getSystemMessageHTML(
            $this->lng->txt('tst_password_entered_wrong_password'),
            'failure'
        );
    }

    /**
     * @return ilPropertyFormGUI
     */
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

    private function showPasswordFormCmd()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        require_once 'Services/Form/classes/class.ilPasswordInputGUI.php';

        $msg = $this->buildPasswordMsg();
        $form = $this->buildPasswordForm();

        $this->tpl->setVariable(
            $this->parentGUI->getContentBlockName(),
            $msg . $this->ctrl->getHTML($form)
        );
    }

    private function saveEnteredPasswordCmd()
    {
        $this->passwordChecker->setUserEnteredPassword($_POST["password"]);

        if (!$this->passwordChecker->isUserEnteredPasswordCorrect()) {
            $this->passwordChecker->logWrongEnteredPassword();
        }

        $this->ctrl->redirectByClass($this->getNextCommandClass(), $this->getNextCommandCmd());
    }

    private function backToInfoScreenCmd()
    {
        $this->ctrl->redirectByClass('ilObjTestGUI', 'infoScreen');
    }

    private function setNextCommandClass($nextCommandClass)
    {
        $this->nextCommandClass = $nextCommandClass;
    }

    private function getNextCommandClass(): string
    {
        return $this->nextCommandClass;
    }

    private function setNextCommandCmd($nextCommandCmd)
    {
        $this->nextCommandCmd = $nextCommandCmd;
    }

    private function getNextCommandCmd(): string
    {
        return $this->nextCommandCmd;
    }
}
