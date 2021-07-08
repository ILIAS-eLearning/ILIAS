<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use \ILIAS\Skill\Tree;

/**
 * TableGUI class for skill usages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillUsageTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilBasicSkillTreeRepository
     */
    protected $tree_repo;

    /**
     * @var Tree\SkillTreeFactory
     */
    protected $tree_factory;

    /**
     * @var Tree\SkillTreeManager
     */
    protected $tree_manager;


    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_cskill_id, $a_usage, $a_mode = "")
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
        $this->skill_id = $id_parts[0];
        $this->tref_id = $id_parts[1];

        $data = array();
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


    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        //var_dump($a_set);
        $this->tpl->setVariable("TYPE_INFO", ilSkillUsage::getTypeInfoString($a_set["type"]));
        $this->tpl->setVariable("NUMBER", count($a_set["usages"]));
        $this->tpl->setVariable("OBJ_TYPE", ilSkillUsage::getObjTypeString($a_set["type"]));
    }
}
