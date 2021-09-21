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

use Psr\Http\Message\ServerRequestInterface;

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
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ServerRequestInterface $request;
    protected int $requested_ref_id;
    protected int $requested_sprof_id;

    public function __construct($a_parent_obj, string $a_parent_cmd, bool $a_write_permission = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->request = $DIC->http()->request();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $params = $this->request->getQueryParams();
        $this->requested_ref_id = (int) ($params["ref_id"] ?? 0);
        $this->requested_sprof_id = (int) ($params["sprof_id"] ?? 0);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getProfiles());
        $this->setTitle($lng->txt("skmg_skill_profiles"));

        $this->addColumn("", "", "1px", true);
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("context"));
        $this->addColumn($this->lng->txt("users"));
        $this->addColumn($this->lng->txt("roles"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_profile_row.html", "Services/Skill");

        $this->addMultiCommand("exportProfiles", $lng->txt("export"));
        if ($a_write_permission) {
            $this->addMultiCommand("confirmDeleteProfiles", $lng->txt("delete"));
        }
        //$this->addCommandButton("", $lng->txt(""));
    }

    public function getProfiles() : array
    {
        return ilSkillProfile::getProfiles();
    }

    protected function fillRow($a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setCurrentBlock("cmd");
        if ($this->rbacsystem->checkAccess('write', $this->requested_ref_id)) {
            $this->tpl->setVariable("CMD", $lng->txt("edit"));
            $ilCtrl->setParameter($this->parent_obj, "sprof_id", $a_set["id"]);
            $this->tpl->setVariable("CMD_HREF", $ilCtrl->getLinkTarget($this->parent_obj, "showLevels"));
            $ilCtrl->setParameter($this->parent_obj, "sprof_id", $this->requested_sprof_id);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("ID", $a_set["id"]);
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
