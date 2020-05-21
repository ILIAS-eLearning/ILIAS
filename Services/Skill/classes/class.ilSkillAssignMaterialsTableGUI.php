<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Assign materials to skill levels table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillAssignMaterialsTableGUI extends ilTable2GUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    
    /**
     * Constructor
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_top_skill_id,
        $a_tref_id,
        $a_basic_skill_id
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $ilUser = $DIC->user();

        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
        $this->ws_tree = new ilWorkspaceTree($ilUser->getId());
        $this->ws_access = new ilWorkspaceAccessHandler();

        $this->top_skill_id = $a_top_skill_id;
        $this->tref_id = (int) $a_tref_id;
        $this->basic_skill_id = $a_basic_skill_id;
        
        // workspace tree
        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
        $this->ws_tree = new ilWorkspaceTree($ilUser->getId());


        // build title
        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $stree = new ilSkillTree();
        $path = $stree->getPathFull($this->basic_skill_id);
        $title = $sep = "";
        foreach ($path as $p) {
            if ($p["type"] != "skrt") {
                $title .= $sep . $p["title"];
                $sep = " > ";
            }
        }

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getLevels());
        $this->setTitle($title);
        $this->setLimit(9999);
        
        $this->addColumn($this->lng->txt("skmg_skill_level"));
        $this->addColumn($this->lng->txt("description"));
        $this->addColumn($this->lng->txt("skmg_materials"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setEnableHeader(true);
        //		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_materials_row.html", "Services/Skill");
        $this->disable("footer");
        $this->setEnableTitle(true);
        
        //		$this->addMultiCommand("", $lng->txt(""));
//		$this->addCommandButton("", $lng->txt(""));
    }

    /**
     * Get levels
     *
     * @param
     * @return
     */
    public function getLevels()
    {
        include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");
        $this->skill = ilSkillTreeNodeFactory::getInstance($this->basic_skill_id);
        foreach ($this->skill->getLevelData() as $k => $v) {
            $levels[] = $v;
        }

        return $levels;
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        include_once("./Services/Skill/classes/class.ilPersonalSkill.php");
        $mat = ilPersonalSkill::getAssignedMaterial($ilUser->getId(), $this->tref_id, $a_set["id"]);
        $ilCtrl->setParameter($this->parent_obj, "level_id", $a_set["id"]);
        foreach ($mat as $m) {
            $this->tpl->setCurrentBlock("mat");
            $obj_id = $this->ws_tree->lookupObjectId($m["wsp_id"]);
            $this->tpl->setVariable(
                "MAT_TITLE",
                ilObject::_lookupTitle($obj_id)
            );
            $this->tpl->setVariable(
                "MAT_IMG",
                ilUtil::img(ilUtil::getImagePath("icon_" . ilObject::_lookupType($obj_id) . ".svg"))
            );
            $this->tpl->setVariable("TXT_REMOVE", $lng->txt("remove"));
            $ilCtrl->setParameter($this->parent_obj, "wsp_id", $m["wsp_id"]);
            $this->tpl->setVariable("HREF_REMOVE", $ilCtrl->getLinkTarget($this->parent_obj, "removeMaterial"));

            $obj_id = $this->ws_tree->lookupObjectId($m["wsp_id"]);
            $url = $this->ws_access->getGotoLink($m["wsp_id"], $obj_id);
            $this->tpl->setVariable("HREF_MAT", $url);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget(
            $this->parent_obj,
            "assignMaterial"
        ));
        $this->tpl->setVariable("TXT_CMD", $lng->txt("skmg_assign_materials"));
        $this->tpl->parseCurrentBlock();
        $ilCtrl->setParameter($this->parent_obj, "level_id", "");
        
        $this->tpl->setVariable("LEVEL_ID", $a_set["id"]);
        $this->tpl->setVariable("SKILL_ID", $this->basic_skill_id);
        $this->tpl->setVariable("TXT_SKILL", $a_set["title"]);
        $this->tpl->setVariable("TXT_SKILL_DESC", $a_set["description"]);
    }
}
