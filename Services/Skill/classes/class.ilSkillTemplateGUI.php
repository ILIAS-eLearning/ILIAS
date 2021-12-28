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
 ********************************************************************
 */

use ILIAS\Skill\Tree;

/**
 * Skill template GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilSkillTemplateGUI: ilObjSkillManagementGUI, ilObjSkillTreeGUI
 */
class ilSkillTemplateGUI extends ilSkillTreeNodeGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;

    public function __construct(Tree\SkillTreeNodeManager $node_manager, int $a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "node_id");
        
        parent::__construct($node_manager, $a_node_id);
    }

    public function getType() : string
    {
        return "stmp";
    }

    public function executeCommand() : void
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

    public function setTabs() : void
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
}
