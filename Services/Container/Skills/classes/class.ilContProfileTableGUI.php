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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Skill\Service\SkillProfileService;

/**
 * TableGUI class for competence profiles in containers
 *
 * @author Thomas Famula <famula@leifos.de>
 *
 * @ingroup ServicesContainer
 */
class ilContProfileTableGUI extends ilTable2GUI
{
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilContainerGlobalProfiles $container_global_profiles;
    protected ilContainerLocalProfiles $container_local_profiles;
    protected ilSkillManagementSettings $skmg_settings;
    protected SkillProfileService $profile_service;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        ilContainerGlobalProfiles $a_cont_glb_profiles,
        ilContainerLocalProfiles $a_cont_lcl_profiles
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->container_global_profiles = $a_cont_glb_profiles;
        $this->container_local_profiles = $a_cont_lcl_profiles;
        $this->skmg_settings = new ilSkillManagementSettings();
        $this->profile_service = $DIC->skills()->profile();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getProfiles());
        $this->setTitle($this->lng->txt("cont_skill_profiles"));

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("cont_skill_profile"), "", "1");
        $this->addColumn($this->lng->txt("context"), "", "1");
        $this->addColumn($this->lng->txt("actions"), "", "1");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.cont_profile_row.html", "Services/Container/Skills");
        $this->setSelectAllCheckbox("id");

        if ($this->skmg_settings->getLocalAssignmentOfProfiles()) {
            $this->addMultiCommand("confirmRemoveSelectedGlobalProfiles", $this->lng->txt("remove"));
        }
        if ($this->skmg_settings->getAllowLocalProfiles()) {
            $this->addMultiCommand("confirmDeleteSelectedLocalProfiles", $this->lng->txt("delete"));
        }
    }

    public function getProfiles() : array
    {
        $profiles = [];
        if ($this->skmg_settings->getLocalAssignmentOfProfiles()) {
            foreach ($this->container_global_profiles->getProfiles() as $gp) {
                $profiles[$gp["profile_id"]] = [
                    "profile_id" => $gp["profile_id"],
                    "title" => $this->profile_service->lookupTitle($gp["profile_id"])
                ];
            }
        }
        if ($this->skmg_settings->getAllowLocalProfiles()) {
            foreach ($this->container_local_profiles->getProfiles() as $lp) {
                $profiles[$lp["profile_id"]] = [
                    "profile_id" => $lp["profile_id"],
                    "title" => $this->profile_service->lookupTitle($lp["profile_id"])
                ];
            }
        }
        ksort($profiles);

        return $profiles;
    }

    protected function fillRow(array $a_set) : void
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $ui_factory = $this->ui_factory;
        $ui_renderer = $this->ui_renderer;

        $tpl->setVariable("TITLE", $a_set["title"]);
        $tpl->setVariable("ID", $a_set["profile_id"]);

        if ($this->profile_service->lookupRefId($a_set["profile_id"]) > 0) {
            $tpl->setVariable("CONTEXT", $lng->txt("skmg_context_local"));
        } else {
            $tpl->setVariable("CONTEXT", $lng->txt("skmg_context_global"));
        }

        $ctrl->setParameter($this->parent_obj, "profile_id", $a_set["profile_id"]);
        $ctrl->setParameterByClass("ilskillprofilegui", "sprof_id", $a_set["profile_id"]);
        $ctrl->setParameterByClass("ilskillprofilegui", "local_context", true);

        if ($this->profile_service->lookupRefId($a_set["profile_id"]) > 0) {
            $items = [
                $ui_factory->link()->standard(
                    $lng->txt("edit"),
                    $ctrl->getLinkTargetByClass("ilskillprofilegui", "showLevelsWithLocalContext")
                ),
                $ui_factory->link()->standard(
                    $lng->txt("delete"),
                    $ctrl->getLinkTarget($this->parent_obj, "confirmDeleteSingleLocalProfile")
                )
            ];
        } else {
            $items = [
                $ui_factory->link()->standard(
                    $lng->txt("remove"),
                    $ctrl->getLinkTarget($this->parent_obj, "confirmRemoveSingleGlobalProfile")
                )
            ];
        }

        $dropdown = $this->ui_factory->dropdown()->standard($items)->withLabel($lng->txt("actions"));
        $tpl->setVariable("ACTIONS", $ui_renderer->render($dropdown));
    }
}
