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
 * TableGUI class for skill usages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillUsageTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilSkillTreeRepository $tree_repo;
    protected Tree\SkillTreeFactory $tree_factory;
    protected Tree\SkillTreeManager $tree_manager;
    protected int $skill_id = 0;
    protected int $tref_id = 0;

    public function __construct($a_parent_obj, string $a_parent_cmd, string $a_cskill_id, array $a_usage, $a_mode = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();

        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->tree_factory = $DIC->skills()->internal()->factory()->tree();
        $this->tree_manager = $DIC->skills()->internal()->manager()->getTreeManager();

        $id_parts = explode(":", $a_cskill_id);
        $this->skill_id = (int) $id_parts[0];
        $this->tref_id = (int) $id_parts[1];

        $data = [];
        foreach ($a_usage as $k => $v) {
            $data[] = array("type" => $k, "usages" => $v);
        }

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($data);

        $tree = $this->tree_repo->getTreeForNodeId($this->skill_id);
        if ($a_mode == "tree") {
            $tree_obj = $this->tree_manager->getTree($tree->getTreeId());
            $title = $tree_obj->getTitle() . " > " . ilSkillTreeNode::_lookupTitle($this->skill_id, $this->tref_id);
            $this->setTitle($title);
        } else {
            $this->setTitle(ilSkillTreeNode::_lookupTitle($this->skill_id, $this->tref_id));
        }

        $path = $tree->getSkillTreePathAsString($this->skill_id, $this->tref_id);
        $this->setDescription($path);

        $this->addColumn($this->lng->txt("skmg_type"), "", "50%");
        $this->addColumn($this->lng->txt("skmg_number"), "", "50%");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_usage_row.html", "Services/Skill");
        $this->setEnableNumInfo(false);

        //		$this->addMultiCommand("", $lng->txt(""));
//		$this->addCommandButton("", $lng->txt(""));
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $this->tpl->setVariable("TYPE_INFO", ilSkillUsage::getTypeInfoString($a_set["type"]));
        $this->tpl->setVariable("NUMBER", count($a_set["usages"]));
        $this->tpl->setVariable("OBJ_TYPE", ilSkillUsage::getObjTypeString($a_set["type"]));
    }
}
