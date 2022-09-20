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
 * Skills for container (course/group) (top gui class)
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesContainer
 * @ilCtrl_Calls ilContainerSkillGUI: ilContSkillPresentationGUI, ilContSkillAdminGUI
 */
class ilContainerSkillGUI
{
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilContainerGUI $container_gui;
    protected ilContainer $container;
    protected ilAccessHandler $access;
    protected ilSkillManagementSettings $skmg_settings;
    protected int $ref_id = 0;

    public function __construct(ilContainerGUI $a_container_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();

        $this->container_gui = $a_container_gui;
        /* @var $obj ilContainer */
        $obj = $this->container_gui->getObject();
        $this->container = $obj;
        $this->ref_id = $this->container->getRefId();
        $this->skmg_settings = new ilSkillManagementSettings();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        $this->addTabs();

        switch ($next_class) {
            case "ilcontskillpresentationgui":
                if ($this->access->checkAccess("read", "", $this->ref_id)) {
                    $gui = new ilContSkillPresentationGUI($this->container_gui);
                    $ctrl->forwardCommand($gui);
                }
                break;

            case "ilcontskilladmingui":
                if ($this->access->checkAccess("write", "", $this->ref_id) || $this->access->checkAccess("grade", "", $this->ref_id)) {
                    $gui = new ilContSkillAdminGUI($this->container_gui);
                    $ctrl->forwardCommand($gui);
                }
                break;

            default:
                /*if (in_array($cmd, array("show")))
                {
                    $this->$cmd();
                }*/
        }
    }

    public function addTabs(): void
    {
        $tabs = $this->tabs;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        if ($this->access->checkAccess("read", "", $this->ref_id)) {
            $tabs->addSubTab(
                "list",
                $lng->txt("cont_skill_show"),
                $ctrl->getLinkTargetByClass("ilContSkillPresentationGUI", "")
            );
        }

        if ($this->access->checkAccess("grade", "", $this->ref_id)) {
            $tabs->addSubTab(
                "members",
                $lng->txt("cont_skill_members"),
                $ctrl->getLinkTargetByClass("ilContSkillAdminGUI", "listMembers")
            );
        }

        if ($this->access->checkAccess("write", "", $this->ref_id)) {
            $tabs->addSubTab(
                "competences",
                $lng->txt("cont_skill_assigned_comp"),
                $ctrl->getLinkTargetByClass("ilContSkillAdminGUI", "listCompetences")
            );

            if ($this->skmg_settings->getLocalAssignmentOfProfiles()
                || $this->skmg_settings->getAllowLocalProfiles()) {
                $tabs->addSubTab(
                    "profiles",
                    $lng->txt("cont_skill_assigned_profiles"),
                    $ctrl->getLinkTargetByClass("ilContSkillAdminGUI", "listProfiles")
                );
            }

            $tabs->addSubTab(
                "settings",
                $lng->txt("settings"),
                $ctrl->getLinkTargetByClass("ilContSkillAdminGUI", "settings")
            );
        }
    }
}
