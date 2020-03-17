<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Abstract class for component tasks
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
abstract class ilSCComponentTaskGUI
{
    protected $ctrl;
    protected $lng;
    
    protected $task = null;
    
    
    /**
     *
     */
    public function __construct(ilSCTask $task = null)
    {
        $this->task = $task;
        
        $this->ctrl = $GLOBALS['DIC']['ilCtrl'];
        $this->lng = $GLOBALS['DIC']['lng'];
    }
    
    /**
     * Get actions for task table gui
     * array(
     *	'txt' => $lng->txt('sysc_action_repair')
     *	'command' => 'repairTask'
     * );
     *
     * @return array
     */
    abstract public function getActions();
    
    /**
     * Get title of task
     */
    abstract public function getTitle();
    
    /**
     * get description of task
     */
    abstract public function getDescription();
    
    
    /**
     * Get title of group
     */
    abstract public function getGroupTitle();
    
    /**
     * Get description of group
     */
    abstract public function getGroupDescription();
    
    /**
     * Get language
     * @return ilLanguage
     */
    protected function getLang()
    {
        return $this->lng;
    }
    
    /**
     * Get ctrl
     * @return ilCtrl
     */
    protected function getCtrl()
    {
        return $this->ctrl;
    }
    
    /**
     * @return ilSCTask
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->getCtrl()->getNextClass($this);
        $cmd = $this->getCtrl()->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }
    
    /**
     * Show simple confirmation
     */
    protected function showSimpleConfirmation($a_text, $a_btn_text, $a_cmd)
    {
        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->getCtrl()->getFormAction($this));
        $confirm->setConfirm($a_btn_text, $a_cmd);
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');
        $confirm->setHeaderText($a_text);
        
        $GLOBALS['DIC']['tpl']->setContent($confirm->getHTML());
    }
    
    /**
     * Cancel and return to task list
     */
    protected function cancel()
    {
        $this->getCtrl()->returnToParent($this);
    }
}
