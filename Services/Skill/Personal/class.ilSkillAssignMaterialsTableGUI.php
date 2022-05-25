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

/**
 * Assign materials to skill levels table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillAssignMaterialsTableGUI extends ilTable2GUI
{
    protected ilObjUser $user;
    protected ilWorkspaceTree $ws_tree;
    protected ilWorkspaceAccessHandler $ws_access;
    protected int $top_skill_id = 0;
    protected int $tref_id = 0;
    protected int $basic_skill_id = 0;
    protected ilSkillTreeNode $skill;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        int $a_top_skill_id,
        int $a_tref_id,
        int $a_basic_skill_id
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $ilUser = $DIC->user();

        $this->ws_tree = new ilWorkspaceTree($ilUser->getId());
        if (!$this->ws_tree->readRootId()) {
            $this->ws_tree->createTreeForUser($ilUser->getId());
        }
        $this->ws_access = new ilWorkspaceAccessHandler();

        $this->top_skill_id = $a_top_skill_id;
        $this->tref_id = $a_tref_id;
        $this->basic_skill_id = $a_basic_skill_id;


        // build title
        $tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $tree_id = $tree_repo->getTreeIdForNodeId($this->basic_skill_id);
        $node_manager = $DIC->skills()->internal()->manager()->getTreeNodeManager($tree_id);
        $title = $node_manager->getWrittenPath($this->basic_skill_id);

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

    public function getLevels() : array
    {
        $this->skill = ilSkillTreeNodeFactory::getInstance($this->basic_skill_id);
        $levels = [];
        foreach ($this->skill->getLevelData() as $k => $v) {
            $levels[] = $v;
        }

        return $levels;
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

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
