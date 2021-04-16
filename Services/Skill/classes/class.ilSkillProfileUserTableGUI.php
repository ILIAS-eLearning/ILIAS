<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for skill profile user assignment
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfileUserTableGUI extends ilTable2GUI
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
     * @var ilSkillProfile
     */
    protected $profile;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_profile, $a_write_permission = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        $this->profile = $a_profile;
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->profile->getAssignments());
        $this->setTitle($lng->txt("skmg_assigned_users"));

        if (!$this->profile->getRefId() > 0) {
            $this->addColumn("", "", "1px", true);
            $this->setSelectAllCheckbox("id[]");
        }
        $this->addColumn($this->lng->txt("type"), "type");
        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("object"), "object");
        //		$this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.profile_user_row.html", "Services/Skill");

        if ($a_write_permission && !$this->profile->getRefId() > 0) {
            $this->addMultiCommand("confirmUserRemoval", $lng->txt("remove"));
        }
        //$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;

        $this->tpl->setVariable("TYPE", $a_set["type"]);
        $this->tpl->setVariable("NAME", $a_set["name"]);
        $this->tpl->setVariable("OBJECT", $a_set["object_title"]);
        if (!$this->profile->getRefId() > 0) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
    }
}
