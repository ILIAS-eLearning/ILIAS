<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for assigned objects of skills
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillAssignedObjectsTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_ass_objects)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $data = array();
        foreach ($a_ass_objects as $obj) {
            if (ilObject::_hasUntrashedReference($obj)) {
                $data[] = array("obj_id" => $obj);
            }
        }

        $this->setData($data);
        $this->setTitle($this->lng->txt("skmg_assigned_objects"));

        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("path"), "path");

        $this->setRowTemplate("tpl.skill_assigned_objects_row.html", "Services/Skill");
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $obj_type = ilObject::_lookupType($a_set["obj_id"]);
        $this->tpl->setVariable(
            "OBJECT_IMG",
            ilUtil::img(ilObject::_getIcon(
                $a_set["obj_id"]),
                $this->lng->txt("icon") . " " . $this->lng->txt($obj_type)
            )
        );
        $this->tpl->setVariable("OBJECT_TITLE", ilObject::_lookupTitle($a_set["obj_id"]));

        $obj_ref_id = ilObject::_getAllReferences($a_set["obj_id"]);
        $obj_ref_id = end($obj_ref_id);
        $obj_ref_id_parent = $this->tree->getParentId($obj_ref_id);

        $path = new ilPathGUI();

        $this->tpl->setVariable(
            "PATH",
            $path->getPath($this->tree->getParentId($obj_ref_id_parent), $obj_ref_id)
        );
    }
}
