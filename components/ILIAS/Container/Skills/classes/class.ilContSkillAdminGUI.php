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

use ILIAS\Skill\Service\SkillTreeService;
use ILIAS\Skill\Access\SkillTreeAccess;
use ILIAS\Skill\Service\SkillProfileService;
use ILIAS\Container\Skills as ContainerSkills;

/**
 * Container skills administration
 *
 * @author Alex Killing <killing@leifos.de>
 * @ilCtrl_Calls ilContSkillAdminGUI: ilSkillProfileGUI
 */
class ilContSkillAdminGUI
{
    protected \ILIAS\Container\InternalGUIService $gui;
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilContainerGUI $container_gui;
    protected ilContainer $container;
    protected ilSkillManagementSettings $skmg_settings;
    protected ilToolbarGUI $toolbar;
    protected ilAccessHandler $access;
    protected int $ref_id = 0;
    protected int $cont_member_role_id = 0;
    protected SkillTreeService $tree_service;
    protected SkillTreeAccess $tree_access_manager;
    protected SkillProfileService $profile_service;
    protected ContainerSkills\ContainerSkillManager $cont_skill_manager;
    protected array $params = [];
    protected ContainerSkills\SkillContainerGUIRequest $container_gui_request;
    protected int $requested_usr_id = 0;
    protected array $requested_usr_ids = [];
    protected string $requested_selected_skill = "";
    protected array $requested_combined_skill_ids = [];
    protected int $requested_selected_profile_id = 0;
    protected array $requested_profile_ids = [];

    public function __construct(ilContainerGUI $a_container_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->access = $DIC->access();

        $this->container_gui = $a_container_gui;
        /* @var $obj ilContainer */
        $obj = $this->container_gui->getObject();
        $this->container = $obj;
        $this->ref_id = $this->container->getRefId();
        $this->cont_member_role_id = ilParticipants::getDefaultMemberRole($this->ref_id);

        $this->tree_service = $DIC->skills()->tree();
        $this->tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager($this->ref_id);
        $this->profile_service = $DIC->skills()->profile();
        $this->cont_skill_manager = $DIC->skills()->internalContainer()->manager()->getSkillManager(
            $this->container->getId(),
            $this->container->getRefId()
        );

        $this->skmg_settings = new ilSkillManagementSettings();
        $this->container_gui_request = $DIC->skills()->internalContainer()->gui()->request();

        $this->ctrl->saveParameter($this, "profile_id");
        $this->params = $this->ctrl->getParameterArray($this);

        $this->requested_usr_id = $this->container_gui_request->getUserId();
        $this->requested_usr_ids = $this->container_gui_request->getUserIds();
        $this->requested_selected_skill = $this->container_gui_request->getSelectedSkill();
        $this->requested_combined_skill_ids = $this->container_gui_request->getCombinedSkillIds();
        $this->requested_selected_profile_id = $this->container_gui_request->getSelectedProfileId();
        $this->requested_profile_ids = $this->container_gui_request->getProfileIds();

        $this->lng->loadLanguageModule("skmg");
        $this->lng->loadLanguageModule("error");
        $this->gui = $DIC->container()->internal()->gui();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("listMembers");

        switch ($next_class) {
            case "ilskillprofilegui":
                $profile_gui = new ilSkillProfileGUI($this->tree_access_manager);
                $this->ctrl->setReturn($this, "listProfiles");
                $ret = $this->ctrl->forwardCommand($profile_gui);
                break;
            default:
                if (
                    ($this->access->checkAccess("write", "", $this->ref_id) &&
                        in_array($cmd, [
                            "listCompetences", "settings", "saveSettings", "selectSkill",
                            "saveSelectedSkill", "confirmRemoveSelectedSkill", "removeSelectedSkill",
                            "listProfiles", "saveSelectedProfile", "confirmRemoveSelectedGlobalProfiles",
                            "removeSelectedGlobalProfiles", "confirmRemoveSingleGlobalProfile", "removeSingleGlobalProfile",
                            "confirmDeleteSingleLocalProfile", "deleteSingleLocalProfile",
                            "confirmDeleteSelectedLocalProfiles", "deleteSelectedLocalProfiles"
                        ]))
                    ||
                    ($this->access->checkAccess("grade", "", $this->ref_id) &&
                        in_array($cmd, [
                            "listMembers", "assignCompetences",
                            "saveCompetenceAssignment", "publishAssignments", "deassignCompetencesConfirm", "deassignCompetences"
                        ]))
                ) {
                    $this->$cmd();
                }
        }
    }

    //// MANAGE MEMBERS

    public function listMembers(): void
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("members");

        // table
        $tab = new ilContSkillMemberTableGUI($this, "listMembers", $this->container);

        $tpl->setContent($tab->getHTML());
    }

    public function assignCompetences(): void
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;
        $ctrl = $this->ctrl;

        $ctrl->saveParameter($this, "usr_id");
        $tabs->activateSubTab("members");

        $form = $this->initCompetenceAssignmentForm();
        $tpl->setContent($form->getHTML());
    }

    public function initCompetenceAssignmentForm(): ilPropertyFormGUI
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();

        // user name
        $name = ilObjUser::_lookupName($this->requested_usr_id);
        $ne = new ilNonEditableValueGUI($this->lng->txt("obj_user"), "");
        $ne->setValue($name["lastname"] . ", " . $name["firstname"] . " [" . $name["login"] . "]");
        $form->addItem($ne);

        if (empty($this->cont_skill_manager->getSkillsForContainerOrdered())) {
            $tpl->setOnScreenMessage('info', $lng->txt("cont_skill_no_skills_selected"), true);
            $ctrl->redirect($this, "listMembers");
        }

        foreach ($this->cont_skill_manager->getSkillsForContainerOrdered() as $sk) {
            $skill = new ilBasicSkill($sk->getBaseSkillId());

            // skill level options
            $options = [
                "-1" => $this->lng->txt("cont_skill_do_not_set"),
            ];
            foreach ($skill->getLevelData() as $l) {
                $options[$l["id"]] = $l["title"];
            }
            $si = new ilSelectInputGUI(
                ilBasicSkill::_lookupTitle($sk->getBaseSkillId(), $sk->getTrefId()),
                "skill_" . $sk->getBaseSkillId() . "_" . $sk->getTrefId()
            );
            $si->setOptions($options);
            $si->setInfo($this->getPathString($sk->getBaseSkillId(), $sk->getTrefId()));
            $mem_level = $this->cont_skill_manager->getMemberSkillLevel(
                $this->requested_usr_id,
                $sk->getBaseSkillId(),
                $sk->getTrefId()
            );
            if ($mem_level) {
                $si->setValue($mem_level);
            }
            $form->addItem($si);
        }

        // save and cancel commands
        $form->addCommandButton("saveCompetenceAssignment", $this->lng->txt("save"));
        $form->addCommandButton("listMembers", $this->lng->txt("cancel"));

        $form->setTitle($this->lng->txt("cont_assign_skills"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    public function getPathString(int $a_skill_id, int $a_tref_id = 0): string
    {
        $path = $this->tree_service->getSkillTreePath($a_skill_id, $a_tref_id);
        $titles = [];
        foreach ($path as $v) {
            if ($v["type"] !== "skrt" && !($v["skill_id"] == $a_skill_id && $v["tref_id"] == $a_tref_id)) {
                $titles[] = $v["title"];
            }
        }

        return implode(" > ", $titles);
    }

    public function saveCompetenceAssignment(): void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $form = $this->initCompetenceAssignmentForm();
        $form->checkInput();

        foreach ($this->cont_skill_manager->getSkillsForContainer() as $sk) {
            $l = (int) $form->getInput("skill_" . $sk->getBaseSkillId() . "_" . $sk->getTrefId());
            if ($l === -1) {
                $this->cont_skill_manager->removeMemberSkillFromContainer(
                    $this->requested_usr_id,
                    $sk->getBaseSkillId(),
                    $sk->getTrefId()
                );
            } else {
                $this->cont_skill_manager->addMemberSkillForContainer(
                    $this->requested_usr_id,
                    $sk->getBaseSkillId(),
                    $sk->getTrefId(),
                    $l
                );
            }
        }

        if (!ilContainer::_lookupContainerSetting($this->container->getId(), "cont_skill_publish", '0')) {
            $this->cont_skill_manager->publishMemberSkills($this->requested_usr_id);
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ctrl->redirect($this, "listMembers");
    }

    public function publishAssignments(): void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $user_ids = $this->requested_usr_ids;
        if (empty($this->requested_usr_ids) && $this->requested_usr_id > 0) {
            $user_ids[] = $this->requested_usr_id;
        }

        $not_changed = [];
        foreach ($user_ids as $user_id) {
            if (!$this->cont_skill_manager->publishMemberSkills($user_id)) {
                $not_changed[] = $user_id;
            }
        }

        if (count($not_changed) === 0) {
            $this->tpl->setOnScreenMessage('success', $lng->txt("cont_skll_published"), true);
        } else {
            $names = array_map(static function (int $id) {
                return ilUserUtil::getNamePresentation($id, false, false, "", true);
            }, $not_changed);
            $this->tpl->setOnScreenMessage('info', $lng->txt("cont_skll_published_some_not") . " (" . implode("; ", $names) . ")", true);
        }

        $ctrl->redirect($this, "listMembers");
    }

    public function deassignCompetencesConfirm(): void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("members");

        $user_ids = $this->requested_usr_ids;
        if (empty($this->requested_usr_ids) && $this->requested_usr_id > 0) {
            $user_ids[] = $this->requested_usr_id;
        }

        if (!is_array($user_ids) || count($user_ids) === 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ctrl->redirect($this, "listMembers");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ctrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_really_deassign_skills"));
            $cgui->setCancel($lng->txt("cancel"), "listMembers");
            $cgui->setConfirm($lng->txt("cont_deassign_competence"), "deassignCompetences");

            foreach ($user_ids as $i) {
                $name = ilUserUtil::getNamePresentation($i, false, false, "", true);
                $cgui->addItem("usr_ids[]", (string) $i, $name);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function deassignCompetences(): void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        foreach ($this->requested_usr_ids as $user_id) {
            $this->cont_skill_manager->removeAllMemberSkillsFromContainer($user_id);
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ctrl->redirect($this, "listMembers");
    }


    //// MANAGE COMPETENCES

    public function listCompetences(): void
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;
        $toolbar = $this->toolbar;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $tabs->activateSubTab("competences");

        $toolbar->addButton(
            $lng->txt("cont_add_skill"),
            $ctrl->getLinkTarget($this, "selectSkill")
        );

        // table
        $tab = new ilContSkillTableGUI(
            $this,
            "listCompetences",
            $this->container
        );

        $tpl->setContent($tab->getHTML());
    }

    public function selectSkill(): void
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("competences");

        $sel = new ilSkillSelectorGUI($this, "selectSkill", $this, "saveSelectedSkill");
        if (!$sel->handleCommand()) {
            $tpl->setContent($sel->getHTML());
        }
    }

    public function saveSelectedSkill(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $s = explode(":", ($this->requested_selected_skill));

        $this->cont_skill_manager->addSkillForContainer((int) $s[0], (int) $s[1]);
        ilSkillUsage::setUsage($this->container->getId(), (int) $s[0], (int) $s[1]);

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "listCompetences");
    }

    public function confirmRemoveSelectedSkill(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("competences");

        if (empty($this->requested_combined_skill_ids)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ctrl->redirect($this, "listCompetences");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ctrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_really_remove_skill_from_course"));
            $cgui->setCancel($lng->txt("cancel"), "listCompetences");
            $cgui->setConfirm($lng->txt("remove"), "removeSelectedSkill");

            foreach ($this->requested_combined_skill_ids as $i) {
                $s = explode(":", $i);
                $cgui->addItem("id[]", $i, ilBasicSkill::_lookupTitle((int) $s[0], (int) $s[1]));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function removeSelectedSkill(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        if (!empty($this->requested_combined_skill_ids)) {
            foreach ($this->requested_combined_skill_ids as $id) {
                $s = explode(":", $id);
                $this->cont_skill_manager->removeSkillFromContainer((int) $s[0], (int) $s[1]);
                ilSkillUsage::setUsage($this->container->getId(), (int) $s[0], (int) $s[1], false);
            }
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "listCompetences");
    }


    //// MANAGE PROFILES

    public function listProfiles(): void
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;
        $toolbar = $this->toolbar;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $tabs->activateSubTab("profiles");

        $options = [];
        $options[0] = $lng->txt("please_select");

        $selectable_profiles = [];
        $all_profiles = $this->profile_service->getAllGlobalProfiles();
        $selected_profiles = $this->profile_service->getGlobalProfilesOfRole($this->cont_member_role_id);
        foreach ($all_profiles as $profile) {
            if (!array_key_exists($profile->getId(), $selected_profiles)) {
                $selectable_profiles[$profile->getId()] = $profile;
            }
        }

        foreach ($selectable_profiles as $profile) {
            $tree = $this->tree_service->getObjSkillTreeById($profile->getSkillTreeId());
            $options[$profile->getId()] = $tree->getTitle() . ": " . $profile->getTitle();
        }

        if ($this->skmg_settings->getLocalAssignmentOfProfiles()) {
            $select = new ilSelectInputGUI($lng->txt("skmg_profile"), "p_id");
            $select->setOptions($options);
            $select->setValue(0);
            $toolbar->addInputItem($select, true);

            $this->gui->button(
                $this->lng->txt("cont_add_global_profile"),
                "saveSelectedProfile"
            )->submit()->toToolbar();
        }

        if ($this->skmg_settings->getLocalAssignmentOfProfiles()
            && $this->skmg_settings->getAllowLocalProfiles()) {
            $toolbar->addSeparator();
        }

        if ($this->skmg_settings->getAllowLocalProfiles()) {
            $this->gui->link(
                $this->lng->txt("cont_add_local_profile"),
                $ctrl->getLinkTargetByClass("ilskillprofilegui", "createLocal")
            )->emphasised()->toToolbar();
        }

        $toolbar->setFormAction($ctrl->getFormAction($this));

        // table
        $tab = new ilContProfileTableGUI(
            $this,
            "listProfiles",
            $this->container->getRefId()
        );

        $tpl->setContent($tab->getHTML());
    }

    public function saveSelectedProfile(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $profile_id = $this->requested_selected_profile_id;

        if (!($profile_id > 0)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("cont_skill_no_profile_selected"), true);
            $ctrl->redirect($this, "listProfiles");
        }

        $this->profile_service->addRoleToProfile($profile_id, $this->cont_member_role_id);

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "listProfiles");
    }

    public function confirmRemoveSelectedGlobalProfiles(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("profiles");

        if (empty($this->requested_profile_ids)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ctrl->redirect($this, "listProfiles");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ctrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_skill_really_remove_profiles_from_list"));
            $cgui->setCancel($lng->txt("cancel"), "listProfiles");
            $cgui->setConfirm($lng->txt("remove"), "removeSelectedGlobalProfiles");

            foreach ($this->requested_profile_ids as $i) {
                if ($this->profile_service->lookupProfileRefId($i) > 0) {
                    $this->tpl->setOnScreenMessage('info', $lng->txt("cont_skill_removal_not_possible"), true);
                    $ctrl->redirect($this, "listProfiles");
                }
                $cgui->addItem("id[]", $i, $this->profile_service->lookupProfileTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function removeSelectedGlobalProfiles(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        foreach ($this->requested_profile_ids as $id) {
            $this->profile_service->removeRoleFromProfile($id, $this->cont_member_role_id);
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "listProfiles");
    }

    public function confirmRemoveSingleGlobalProfile(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("profiles");

        $profile_id = (int) $this->params["profile_id"];

        if (!($profile_id > 0)) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("error_sry_error"), true);
            $ctrl->redirect($this, "listProfiles");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ctrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_skill_really_remove_profile_from_list"));
            $cgui->setCancel($lng->txt("cancel"), "listProfiles");
            $cgui->setConfirm($lng->txt("remove"), "removeSingleGlobalProfile");
            $cgui->addItem("", (string) $profile_id, $this->profile_service->lookupProfileTitle($profile_id));

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function removeSingleGlobalProfile(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $profile_id = (int) $this->params["profile_id"];

        if ($profile_id > 0) {
            $this->profile_service->removeRoleFromProfile($profile_id, $this->cont_member_role_id);
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "listProfiles");
    }

    public function confirmDeleteSelectedLocalProfiles(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("profiles");

        if (empty($this->requested_profile_ids)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ctrl->redirect($this, "listProfiles");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ctrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_skill_really_delete_profiles_from_list"));
            $cgui->setCancel($lng->txt("cancel"), "listProfiles");
            $cgui->setConfirm($lng->txt("delete"), "deleteSelectedLocalProfiles");

            foreach ($this->requested_profile_ids as $i) {
                if (!($this->profile_service->lookupProfileRefId($i) > 0)) {
                    $this->tpl->setOnScreenMessage('info', $lng->txt("cont_skill_deletion_not_possible"), true);
                    $ctrl->redirect($this, "listProfiles");
                }
                $cgui->addItem("id[]", $i, $this->profile_service->lookupProfileTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function deleteSelectedLocalProfiles(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        if (!empty($this->requested_profile_ids)) {
            foreach ($this->requested_profile_ids as $id) {
                if ($this->profile_service->lookupProfileRefId($id) > 0) {
                    $this->profile_service->deleteProfile($id);
                }
            }
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "listProfiles");
    }

    public function confirmDeleteSingleLocalProfile(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("profiles");

        $profile_id = (int) $this->params["profile_id"];

        if (!($profile_id > 0)) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("error_sry_error"), true);
            $ctrl->redirect($this, "listProfiles");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ctrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_skill_really_delete_profile_from_list"));
            $cgui->setCancel($lng->txt("cancel"), "listProfiles");
            $cgui->setConfirm($lng->txt("delete"), "deleteSingleLocalProfile");
            $cgui->addItem("", (string) $profile_id, $this->profile_service->lookupProfileTitle($profile_id));

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function deleteSingleLocalProfile(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $profile_id = (int) $this->params["profile_id"];

        if ($profile_id > 0) {
            $this->profile_service->deleteProfile($profile_id);
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "listProfiles");
    }


    //// SETTINGS

    public function settings(): void
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("settings");

        $form = $this->initSettingsForm();

        $tpl->setContent($form->getHTML());
    }

    public function initSettingsForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        // publish
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_skill_publish"), "cont_skill_publish");
        $op1 = new ilRadioOption($lng->txt("cont_skill_publish_auto"), '0', $lng->txt("cont_skill_publish_auto_info"));
        $radg->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("cont_skill_publish_manual"), '1', $lng->txt("cont_skill_publish_manual_info"));
        $radg->addOption($op2);
        $form->addItem($radg);
        $radg->setValue(ilContainer::_lookupContainerSetting($this->container->getId(), "cont_skill_publish", '0'));

        $form->addCommandButton("saveSettings", $lng->txt("save"));

        $form->setTitle($lng->txt("settings"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    public function saveSettings(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $form = $this->initSettingsForm();
        $form->checkInput();
        ilContainer::_writeContainerSetting($this->container->getId(), "cont_skill_publish", $form->getInput("cont_skill_publish"));

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "settings");
    }
}
