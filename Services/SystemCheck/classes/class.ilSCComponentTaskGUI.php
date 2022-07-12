<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
