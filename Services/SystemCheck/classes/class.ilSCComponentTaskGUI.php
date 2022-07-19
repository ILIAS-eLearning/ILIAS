<?php declare(strict_types=1);
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
 * Abstract class for component tasks
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
abstract class ilSCComponentTaskGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    protected ?ilSCTask $task;

    public function __construct(ilSCTask $task = null)
    {
        global $DIC;
        $this->task = $task;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * array(
     *    'txt' => $lng->txt('sysc_action_repair')
     *    'command' => 'repairTask'
     * );
     */
    abstract public function getActions() : array;

    abstract public function getTitle() : string;

    abstract public function getDescription() : string;

    abstract public function getGroupTitle() : string;

    abstract public function getGroupDescription() : string;

    protected function getLang() : ilLanguage
    {
        return $this->lng;
    }

    protected function getCtrl() : ilCtrl
    {
        return $this->ctrl;
    }

    public function getTask() : ilSCTask
    {
        return $this->task;
    }

    public function executeCommand() : void
    {
        $next_class = $this->getCtrl()->getNextClass($this);
        $cmd = $this->getCtrl()->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    protected function showSimpleConfirmation(string $a_text, string $a_btn_text, string $a_cmd) : void
    {
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->getCtrl()->getFormAction($this));
        $confirm->setConfirm($a_btn_text, $a_cmd);
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');
        $confirm->setHeaderText($a_text);

        $this->tpl->setContent($confirm->getHTML());
    }

    protected function cancel() : void
    {
        $this->getCtrl()->returnToParent($this);
    }
}
