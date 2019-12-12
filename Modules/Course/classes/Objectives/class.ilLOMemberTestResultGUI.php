<?php
/* (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * test result overview
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ilCtrl_isCalledBy ilLOMemberTestResultGUI: ilObjCourseGUI
 */
class ilLOMemberTestResultGUI
{
    private $container = null;
    private $container_gui = null;
    private $user_id = 0;
    
    /**
     * Constructor
     * @param ilObjectGUI $parent_gui
     * @param ilObject $parent
     */
    public function __construct(ilObjectGUI $parent_gui, ilObject $parent, $a_user_id)
    {
        $this->container_gui = $parent_gui;
        $this->container = $parent;
        $this->user_id = $a_user_id;
    }
    
    /**
     * Execute command
     * @global type $ilCtrl
     * @return boolean
     */
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        
        switch ($next_class) {
            
            default:
                if (!$cmd) {
                    $cmd = 'viewResult';
                }
                $this->$cmd();

                break;
        }
        return true;
    }

    /**
     * Get container
     * @return ilObject
     */
    public function getParentObject()
    {
        return $this->container;
    }
    
    /**
     * Get parent gui
     * @return ilObjectGUI
     */
    public function getParentGUI()
    {
        return $this->container_gui;
    }
    
    
    /**
     * Get current user id
     * @return type
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * View results
     */
    protected function viewResult()
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOMemberTestResultTableGUI.php';
        $result_table = new ilLOMemberTestResultTableGUI($this, $this->getParentObject(), 'viewResult');
        $result_table->setUserId($this->getUserId());
        $result_table->init();
        $result_table->parse();
        
        $GLOBALS['DIC']['tpl']->setContent($result_table->getHTML());
    }
    
    /**
     * Set tabs
     */
    protected function setTabs()
    {
    }
}
