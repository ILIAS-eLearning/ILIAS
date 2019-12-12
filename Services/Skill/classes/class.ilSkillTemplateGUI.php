<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNodeGUI.php");

/**
 * Skill template GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilSkillCategoryGUI: ilObjSkillManagementGUI
 * @ingroup ServicesSkill
 */
class ilSkillTemplateGUI extends ilSkillTreeNodeGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct($a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "obj_id");
        
        parent::__construct($a_node_id);
    }

    /**
     * Get Node Type
     */
    public function getType()
    {
        return "stmp";
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        //$tpl->getStandardTemplate();
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }
    }
    
    /**
     * output tabs
     */
    public function setTabs()
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $tpl->setTitleIcon(ilUtil::getImagePath("icon_skmg.svg"));
        $tpl->setTitle(
            $lng->txt("skmg_skill_template") . ": " . $this->node_object->getTitle()
        );
    }


    /**
     * Perform drag and drop action
     */
    public function proceedDragDrop()
    {
        $ilCtrl = $this->ctrl;

        //		$this->slm_object->executeDragDrop($_POST["il_hform_source_id"], $_POST["il_hform_target_id"],
//			$_POST["il_hform_fc"], $_POST["il_hform_as_subitem"]);
//		$ilCtrl->redirect($this, "showOrganization");
    }
}
