<?php

declare(strict_types=1);

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
use ILIAS\Skill\Access\SkillManagementAccess;
use ILIAS\Skill\Service\SkillInternalManagerService;

/**
 * Skill management main GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilObjSkillManagementGUI: ilPermissionGUI, SkillTreeAdminGUI
 * @ilCtrl_isCalledBy ilObjSkillManagementGUI: ilAdministrationGUI
 */
class ilObjSkillManagementGUI extends ilObjectGUI
{
    protected ilErrorHandling $error;
    protected ilTabsGUI $tabs;
    protected Factory $ui_fac;
    protected Renderer $ui_ren;
    protected SkillInternalManagerService $skill_manager;
    protected SkillManagementAccess $management_access_manager;

    /**
     * @param string|array $a_data
     * @param int          $a_id
     * @param bool         $a_call_by_reference
     * @param bool         $a_prepare_output
     */
    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $ilCtrl = $DIC->ctrl();

        $this->type = 'skmg';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('skmg');

        $ilCtrl->saveParameter($this, "node_id");
        $this->skill_manager = $DIC->skills()->internal()->manager();
        $this->management_access_manager = $this->skill_manager->getManagementAccessManager($this->object->getRefId());
    }

    public function executeCommand(): void
    {
        $ilErr = $this->error;
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$this->management_access_manager->hasReadManagementPermission()) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case "skilltreeadmingui":
                $this->prepareOutput();
                $ilTabs->activateTab("skill_trees");
                $gui = new SkillTreeAdminGUI($this->skill_manager);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab('permissions');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $this->prepareOutput();
                if (!$cmd || $cmd == 'view') {
                    $cmd = "listTrees";
                }

                $this->$cmd();

                break;
        }
    }

    public function getAdminTabs(): void
    {
        $ilAccess = $this->access;
        $lng = $this->lng;

        if ($this->management_access_manager->hasReadManagementPermission()) {
            $this->tabs_gui->addTab(
                "skill_trees",
                $lng->txt("skmg_skill_trees"),
                $this->ctrl->getLinkTargetByClass("skilltreeadmingui", "")
            );

            $this->tabs_gui->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );
        }

        if ($this->management_access_manager->hasEditManagementPermissionsPermission()) {
            $this->tabs_gui->addTab(
                "permissions",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    public function editSettings(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $ilTabs->activateTab("settings");

        $skmg_set = new ilSkillManagementSettings();

        // Enable skill management
        $check_enable = $this->ui_fac->input()->field()->checkbox($lng->txt("skmg_enable_skmg"))
            ->withValue($skmg_set->isActivated());

        // Hide Competence Profile Data before Self-Assessment
        $check_hide_prof = $this->ui_fac->input()->field()->checkbox(
            $lng->txt("skmg_hide_profile_self_eval"),
            $lng->txt("skmg_hide_profile_self_eval_info")
        )->withValue($skmg_set->getHideProfileBeforeSelfEval());

        // Allow local assignment of global profiles
        $check_loc_ass_prof = $this->ui_fac->input()->field()->checkbox($lng->txt("skmg_local_assignment_profiles"))
                               ->withValue($skmg_set->getLocalAssignmentOfProfiles());

        // Allow creation of local profiles
        $check_create_loc_prof = $this->ui_fac->input()->field()->checkbox(
            $lng->txt("skmg_allow_local_profiles"),
            $lng->txt("skmg_allow_local_profiles_info")
        )->withValue($skmg_set->getAllowLocalProfiles());

        //section
        $section_settings = $this->ui_fac->input()->field()->section(
            ["check_enable" => $check_enable,
             "check_hide_prof" => $check_hide_prof,
             "check_loc_ass_prof" => $check_loc_ass_prof,
             "check_create_loc_prof" => $check_create_loc_prof],
            $lng->txt("skmg_settings")
        );

        // form and form action handling
        $ilCtrl->setParameterByClass(
            'ilobjskillmanagementgui',
            'skill_settings',
            'skill_settings_config'
        );

        $form = $this->ui_fac->input()->container()->form()->standard(
            $ilCtrl->getFormAction($this, "editSettings"),
            ["section_settings" => $section_settings]
        );

        if ($this->request->getMethod() == "POST"
            && $this->request->getQueryParams()["skill_settings"] == "skill_settings_config") {
            if (!$this->management_access_manager->hasEditManagementSettingsPermission()) {
                return;
            }

            $form = $form->withRequest($this->request);
            $result = $form->getData();

            $skmg_set->activate($result["section_settings"]["check_enable"]);
            $skmg_set->setHideProfileBeforeSelfEval($result["section_settings"]["check_hide_prof"]);
            $skmg_set->setLocalAssignmentOfProfiles($result["section_settings"]["check_loc_ass_prof"]);
            $skmg_set->setAllowLocalProfiles($result["section_settings"]["check_create_loc_prof"]);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "editSettings");
        }

        $this->tpl->setContent($this->ui_ren->render([$form]));
    }

    public function listTrees(): void
    {
        $this->ctrl->clearParameterByClass(get_class($this), "node_id");
        $this->ctrl->redirectByClass("skilltreeadmingui", "listTrees");
    }
}
