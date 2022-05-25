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
use ILIAS\Skill\Profile\SkillProfileManager;

/**
 * TableGUI class for skill profiles
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfileTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected SkillTreeAccess $tree_access_manager;
    protected SkillProfileManager $profile_manager;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_ref_id = 0;
    protected int $requested_sprof_id = 0;

    public function __construct($a_parent_obj, string $a_parent_cmd, int $a_skill_tree_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->requested_ref_id = $this->admin_gui_request->getRefId();
        $this->requested_sprof_id = $this->admin_gui_request->getSkillProfileId();

        $this->tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager($this->requested_ref_id);
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        if ($a_skill_tree_id == 0) {
            $this->setData($this->getProfiles());
        } else {
            $this->setData($this->getProfilesForSkillTree($a_skill_tree_id));
        }
        $this->setTitle($lng->txt("skmg_skill_profiles"));

        if ($this->tree_access_manager->hasManageProfilesPermission()) {
            $this->addColumn("", "", "1px", true);
        }
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("context"));
        $this->addColumn($this->lng->txt("users"));
        $this->addColumn($this->lng->txt("roles"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_profile_row.html", "Services/Skill");

        if ($this->tree_access_manager->hasManageProfilesPermission()) {
            $this->addMultiCommand("exportProfiles", $lng->txt("export"));
            $this->addMultiCommand("confirmDeleteProfiles", $lng->txt("delete"));
        }
        //$this->addCommandButton("", $lng->txt(""));
    }

    public function getProfiles() : array
    {
        return $this->profile_manager->getProfilesForAllSkillTrees();
    }

    public function getProfilesForSkillTree(int $a_skill_tree_id) : array
    {
        return $this->profile_manager->getProfilesForSkillTree($a_skill_tree_id);
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setCurrentBlock("cmd");
        if ($this->tree_access_manager->hasManageProfilesPermission()) {
            $this->tpl->setVariable("CMD", $lng->txt("edit"));
        } else {
            $this->tpl->setVariable("CMD", $lng->txt("show"));
        }
        $ilCtrl->setParameter($this->parent_obj, "sprof_id", $a_set["id"]);
        $this->tpl->setVariable("CMD_HREF", $ilCtrl->getLinkTarget($this->parent_obj, "showLevels"));
        $ilCtrl->setParameter($this->parent_obj, "sprof_id", $this->requested_sprof_id);
        $this->tpl->parseCurrentBlock();
        if ($this->tree_access_manager->hasManageProfilesPermission()) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("TITLE", $a_set["title"]);

        $profile_ref_id = $this->profile_manager->lookupRefId($a_set["id"]);
        $profile_obj_id = ilContainerReference::_lookupObjectId($profile_ref_id);
        $profile_obj_title = ilObject::_lookupTitle($profile_obj_id);
        if ($profile_ref_id > 0) {
            $this->tpl->setVariable(
                "CONTEXT",
                $lng->txt("skmg_context_local") . " (" . $profile_obj_title . ")"
            );
        } else {
            $this->tpl->setVariable("CONTEXT", $lng->txt("skmg_context_global"));
        }

        $this->tpl->setVariable("NUM_USERS", $this->profile_manager->countUsers($a_set["id"]));
        $this->tpl->setVariable("NUM_ROLES", $this->profile_manager->countRoles($a_set["id"]));
    }
}
