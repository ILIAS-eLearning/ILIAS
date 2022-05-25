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

use ILIAS\Skill\Access\SkillTreeAccess;
use ILIAS\Skill\Service\SkillAdminGUIRequest;

/**
 * TableGUI class for skill profile user assignment
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfileUserTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilSkillProfile $profile;
    protected SkillTreeAccess $tree_access_manager;
    protected ilSkillProfileManager $profile_manager;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_ref_id = 0;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        ilSkillProfile $a_profile
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $this->requested_ref_id = $this->admin_gui_request->getRefId();

        $this->tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager($this->requested_ref_id);
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();

        $this->profile = $a_profile;
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->profile_manager->getAssignments($this->profile->getId()));
        $this->setTitle($lng->txt("skmg_assigned_users"));

        if ($this->tree_access_manager->hasManageProfilesPermission() && !$this->profile->getRefId() > 0) {
            $this->addColumn("", "", "1px", true);
            $this->setSelectAllCheckbox("id[]");
        }
        $this->addColumn($this->lng->txt("type"), "type");
        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("object"), "object");
        //		$this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.profile_user_row.html", "Services/Skill");

        if ($this->tree_access_manager->hasManageProfilesPermission() && !$this->profile->getRefId() > 0) {
            $this->addMultiCommand("confirmUserRemoval", $lng->txt("remove"));
        }
        //$this->addCommandButton("", $lng->txt(""));
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $this->tpl->setVariable("TYPE", $a_set["type"]);
        $this->tpl->setVariable("NAME", $a_set["name"]);
        $this->tpl->setVariable("OBJECT", $a_set["object_title"]);
        if ($this->tree_access_manager->hasManageProfilesPermission() && !$this->profile->getRefId() > 0) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
    }
}
