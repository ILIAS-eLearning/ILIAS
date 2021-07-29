<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for skill profiles
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfileTableGUI extends ilTable2GUI
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
     * @var \ILIAS\Skill\Access\SkillTreeAccess
     */
    protected $skill_tree_access_manager;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $requested_ref_id;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_skill_tree_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->request = $DIC->http()->request();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $params = $this->request->getQueryParams();
        $this->requested_ref_id = (int) ($params["ref_id"] ?? 0);

        $this->skill_tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager($this->requested_ref_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        if ($a_skill_tree_id == 0) {
            $this->setData($this->getProfiles());
        } else {
            $this->setData($this->getProfilesForSkillTree($a_skill_tree_id));
        }

        $this->setTitle($lng->txt("skmg_skill_profiles"));

        if ($this->skill_tree_access_manager->hasManageProfilesPermission()) {
            $this->addColumn("", "", "1px", true);
        }
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("context"));
        $this->addColumn($this->lng->txt("users"));
        $this->addColumn($this->lng->txt("roles"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_profile_row.html", "Services/Skill");

        if ($this->skill_tree_access_manager->hasManageProfilesPermission()) {
            $this->addMultiCommand("exportProfiles", $lng->txt("export"));
            $this->addMultiCommand("confirmDeleteProfiles", $lng->txt("delete"));
        }
        //$this->addCommandButton("", $lng->txt(""));
    }

    /**
     * Get profiles
     *
     * @return array array of skill profiles
     */
    public function getProfiles()
    {
        return ilSkillProfile::getProfilesForAllSkillTrees();
    }

    /**
     * Get profiles for a specific skill tree
     *
     * @return array array of skill profiles
     */
    public function getProfilesForSkillTree(int $a_skill_tree_id)
    {
        return ilSkillProfile::getProfilesForSkillTree($a_skill_tree_id);
    }


    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setCurrentBlock("cmd");
        if ($this->skill_tree_access_manager->hasManageProfilesPermission()) {
            $this->tpl->setVariable("CMD", $lng->txt("edit"));
        }
        else {
            $this->tpl->setVariable("CMD", $lng->txt("show"));
        }
        $ilCtrl->setParameter($this->parent_obj, "sprof_id", $a_set["id"]);
        $this->tpl->setVariable("CMD_HREF", $ilCtrl->getLinkTarget($this->parent_obj, "showLevels"));
        $ilCtrl->setParameter($this->parent_obj, "sprof_id", $_GET["sprof_id"]);
        $this->tpl->parseCurrentBlock();
        if ($this->skill_tree_access_manager->hasManageProfilesPermission()) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("TITLE", $a_set["title"]);

        $profile_ref_id = ilSkillProfile::lookupRefId($a_set["id"]);
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

        $this->tpl->setVariable("NUM_USERS", ilSkillProfile::countUsers($a_set["id"]));
        $this->tpl->setVariable("NUM_ROLES", ilSkillProfile::countRoles($a_set["id"]));
    }
}
