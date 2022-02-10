<?php declare(strict_types=0);
/* (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * test result overview
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_isCalledBy ilLOMemberTestResultGUI: ilObjCourseGUI
 */
class ilLOMemberTestResultGUI
{
    private ilObject $container;
    private ilObjectGUI $container_gui;
    private int $user_id;

    protected ilCtrlInterface $ctrl;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct(ilObjectGUI $parent_gui, ilObject $parent, int $a_user_id)
    {
        global $DIC;

        $this->container_gui = $parent_gui;
        $this->container = $parent;
        $this->user_id = $a_user_id;

        $this->ctrl = $DIC->ctrl();
        $this->tpl  = $DIC->ui()->mainTemplate();
    }
    
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        switch ($next_class) {
            
            default:
                if (!$cmd) {
                    $cmd = 'viewResult';
                }
                $this->$cmd();
                break;
        }
    }

    public function getParentObject() : ilObject
    {
        return $this->container;
    }
    
    public function getParentGUI() : ilObjectGUI
    {
        return $this->container_gui;
    }
    
    
    public function getUserId() : int
    {
        return $this->user_id;
    }

    /**
     * View results
     */
    protected function viewResult()
    {
        $result_table = new ilLOMemberTestResultTableGUI($this, $this->getParentObject(), 'viewResult');
        $result_table->setUserId($this->getUserId());
        $result_table->init();
        $result_table->parse();
        
        $this->tpl->setContent($result_table->getHTML());
    }
}
