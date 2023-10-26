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

use ILIAS\Skill\Access\SkillTreeAccess;
use ILIAS\Skill\Service\SkillAdminGUIRequest;
use ILIAS\Skill\Service\SkillTreeService;
use ILIAS\Skill\Service\SkillInternalManagerService;
use ILIAS\Skill\Service\SkillInternalFactoryService;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\FileUpload\MimeType;
use ILIAS\Skill\Profile;
use ILIAS\Skill\Table;

/**
 * Skill profile GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilSkillProfileGUI: ilRepositorySearchGUI
 */
class ilSkillProfileGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilHelpGUI $help;
    protected ilToolbarGUI $toolbar;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected \ILIAS\Data\Factory $df;
    protected ServerRequestInterface $request;
    protected ArrayBasedRequestWrapper $query;
    protected int $id = 0;
    protected ?Profile\SkillProfile $profile = null;
    protected SkillTreeService $tree_service;
    protected SkillTreeAccess $skill_tree_access_manager;
    protected int $skill_tree_id = 0;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_ref_id = 0;
    protected int $requested_sprof_id = 0;
    protected SkillInternalFactoryService $skill_factory;
    protected Profile\SkillProfileManager $profile_manager;
    protected Profile\SkillProfileCompletionManager $profile_completion_manager;
    protected Table\TableManager $table_manager;

    /**
     * @var int[]
     */
    protected array $requested_profile_ids = [];
    protected bool $requested_local_context = false;
    protected string $requested_cskill_id = "";

    /**
     * @var string[]
     */
    protected array $requested_level_ass_ids = [];

    /**
     * @var int[]
     */
    protected array $requested_level_order = [];
    protected string $requested_user_login = "";

    /**
     * @var int[]
     */
    protected array $requested_users = [];

    /**
     * @var int[]
     */
    protected array $requested_user_ids = [];
    protected string $requested_table_profile_action = "";

    /**
     * @var string[]
     */
    protected array $requested_table_profile_ids = [];
    protected string $requested_table_profile_level_assignment_action = "";

    /**
     * @var string[]
     */
    protected array $requested_table_profile_level_assignment_ids = [];
    protected bool $local_context = false;

    public function __construct(SkillTreeAccess $skill_tree_access_manager, int $skill_tree_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->help = $DIC["ilHelp"];
        $this->toolbar = $DIC->toolbar();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->df = new \ILIAS\Data\Factory();
        $this->request = $DIC->http()->request();
        $this->query = $DIC->http()->wrapper()->query();
        $this->tree_service = $DIC->skills()->tree();
        $this->skill_tree_access_manager = $skill_tree_access_manager;
        $this->skill_tree_id = $skill_tree_id;
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $this->skill_factory = $DIC->skills()->internal()->factory();
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();
        $this->profile_completion_manager = $DIC->skills()->internal()->manager()->getProfileCompletionManager();
        $this->table_manager = $DIC->skills()->internal()->manager()->getTableManager();

        $this->ctrl->saveParameter($this, ["sprof_id", "local_context"]);

        $this->requested_ref_id = $this->admin_gui_request->getRefId();
        $this->requested_sprof_id = $this->admin_gui_request->getSkillProfileId();
        $this->requested_profile_ids = $this->admin_gui_request->getProfileIds();
        $this->requested_local_context = $this->admin_gui_request->getLocalContext();
        $this->requested_cskill_id = $this->admin_gui_request->getCombinedSkillId();
        $this->requested_level_ass_ids = $this->admin_gui_request->getAssignedLevelIds();
        $this->requested_level_order = $this->admin_gui_request->getOrder();
        $this->requested_user_login = $this->admin_gui_request->getUserLogin();
        $this->requested_users = $this->admin_gui_request->getUsers();
        $this->requested_user_ids = $this->admin_gui_request->getUserIds();
        $this->requested_table_profile_action = $this->admin_gui_request->getTableProfileAction();
        $this->requested_table_profile_ids = $this->admin_gui_request->getTableProfileIds();
        $this->requested_table_profile_level_assignment_action = $this->admin_gui_request->getTableProfileLevelAssignmentAction();
        $this->requested_table_profile_level_assignment_ids = $this->admin_gui_request->getTableProfileLevelAssignmentIds();

        if ($this->requested_sprof_id > 0) {
            $this->id = $this->requested_sprof_id;
        }

        if ($this->id > 0) {
            $this->profile = $this->profile_manager->getProfile($this->id);
            if ($this->skill_tree_id == 0) {
                $this->skill_tree_id = $this->profile->getSkillTreeId();
            }
            if ($this->profile->getRefId() > 0 && $this->requested_local_context) {
                $this->local_context = true;
            }
        }
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $cmd = $ilCtrl->getCmd("listProfiles");
        $next_class = $ilCtrl->getNextClass();
        switch ($next_class) {
            case 'ilrepositorysearchgui':
                $user_search = new ilRepositorySearchGUI();
                $user_search->setTitle($lng->txt('skmg_add_user_to_profile'));
                $user_search->setCallback($this, 'assignUser');
                $user_search->setRoleCallback($this, 'assignRole');

                // Set tabs
                //$this->tabs_gui->setTabActive('user_assignment');
                $ilCtrl->setReturn($this, 'showUsers');
                $ret = $ilCtrl->forwardCommand($user_search);
                break;

            default:
                if (in_array($cmd, array("listProfiles", "create", "edit", "save", "update",
                    "deleteProfiles", "showLevels", "assignLevel",
                    "assignLevelSelectSkill", "updateLevelOfSelectedSkill",
                    "assignLevelToProfile", "updateLevelOfProfile",
                    "confirmLevelAssignmentRemoval", "removeLevelAssignments",
                    "showUsers", "assignUser", "assignRole",
                    "confirmUserRemoval", "removeUsers", "exportProfiles", "showImportForm",
                    "importProfiles", "saveLevelOrder", "createLocal", "saveLocal",
                    "listLocalProfiles", "showLevelsWithLocalContext", "showObjects",
                    "showLevelsWithTableContext"))) {
                    $this->$cmd();
                }
                break;
        }
    }

    public function setTabs(string $a_active): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilHelp = $this->help;

        $tpl->setTitle($lng->txt("skmg_profile") . ": " .
            $this->profile->getTitle());
        $tpl->setDescription($this->profile->getDescription());

        $ilTabs->clearTargets();
        $ilHelp->setScreenIdComponent("skmg_prof");

        $ilTabs->setBackTarget(
            $lng->txt("skmg_skill_profiles"),
            $ilCtrl->getLinkTarget($this, "")
        );

        // levels
        $ilTabs->addTab(
            "levels",
            $lng->txt("skmg_assigned_skill_levels"),
            $ilCtrl->getLinkTarget($this, "showLevels")
        );

        // users
        $ilTabs->addTab(
            "users",
            $lng->txt("skmg_assigned_users"),
            $ilCtrl->getLinkTarget($this, "showUsers")
        );

        // objects
        $ilTabs->addTab(
            "objects",
            $lng->txt("skmg_assigned_objects"),
            $ilCtrl->getLinkTarget($this, "showObjects")
        );

        // settings
        if ($this->skill_tree_access_manager->hasManageProfilesPermission()) {
            $ilTabs->addTab(
                "settings",
                $lng->txt("settings"),
                $ilCtrl->getLinkTarget($this, "edit")
            );
        }

        $ilTabs->activateTab($a_active);
    }

    public function listProfiles(): void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if ($this->skill_tree_access_manager->hasManageProfilesPermission()) {
            $ilToolbar->addButton(
                $lng->txt("skmg_add_profile"),
                $ilCtrl->getLinkTarget($this, "create")
            );

            $ilToolbar->addButton(
                $lng->txt("import"),
                $ilCtrl->getLinkTarget($this, "showImportForm")
            );
        }

        $table = $this->table_manager->getProfileTable($this->requested_ref_id, $this->skill_tree_id)->getComponent();

        $tpl->setContent($this->ui_ren->render($table));
    }

    public function listLocalProfiles(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirectByClass("ilcontskilladmingui", "listProfiles");
    }

    public function create(): void
    {
        $tpl = $this->tpl;

        $form = $this->initProfileForm("create");
        $tpl->setContent($this->ui_ren->render($form));
    }

    public function createLocal(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;

        $tabs->clearTargets();
        $tabs->setBackTarget(
            $lng->txt("back_to_course"),
            $ctrl->getLinkTargetByClass("ilcontskilladmingui", "listProfiles")
        );

        $form = $this->initProfileForm("createLocal");
        $tpl->setContent($this->ui_ren->render($form));
    }

    public function edit(): void
    {
        $tpl = $this->tpl;

        $this->setTabs("settings");
        $form = $this->initProfileForm("edit");
        $tpl->setContent($this->ui_ren->render($form));
    }

    public function initProfileForm(string $a_mode = "edit"): \ILIAS\UI\Component\Input\Container\Form\Form
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter(
            $this,
            "profile",
            "profile_settings"
        );

        // title
        $ti = $this->ui_fac->input()->field()->text($lng->txt("title"))
                           ->withRequired(true);

        // description
        $desc = $this->ui_fac->input()->field()->textarea($lng->txt("description"));

        // skill trees (if local profile)
        $se = null;
        if ($a_mode == "createLocal") {
            $options = [];
            $trees = $this->tree_service->getObjSkillTrees();
            foreach ($trees as $tree) {
                $options[$tree->getId()] = $tree->getTitle();
            }
            $se = $this->ui_fac->input()->field()->select($lng->txt("skmg_skill_tree"), $options)->withRequired(true);
        }

        // image
        $img = $this->ui_fac->input()->field()->file(new ilSkillProfileUploadHandlerGUI(), $lng->txt("image"))
                            ->withAcceptedMimeTypes([MimeType::IMAGE__PNG, MimeType::IMAGE__JPEG]);

        // save commands
        $sec_des = "";
        $form_action = "";
        if ($this->skill_tree_access_manager->hasManageProfilesPermission()) {
            if ($a_mode == "create") {
                $sec_des = $lng->txt("skmg_add_profile");
                $form_action = $ilCtrl->getFormAction($this, "save");
            } elseif ($a_mode == "createLocal") {
                $sec_des = $lng->txt("skmg_add_local_profile");
                $form_action = $ilCtrl->getFormAction($this, "saveLocal");
            } else {
                // set values
                $ti = $ti->withValue($this->profile->getTitle());
                $desc = $desc->withValue($this->profile->getDescription());
                $img = $this->profile->getImageId() ? $img->withValue([$this->profile->getImageId()]) : $img;

                $sec_des = $lng->txt("skmg_edit_profile");
                $form_action = $ilCtrl->getFormAction($this, "update");
            }
        }

        if (is_null($se)) {
            $section_basic = $this->ui_fac->input()->field()->section(
                ["title" => $ti, "description" => $desc],
                $sec_des
            );
        } else {
            $section_basic = $this->ui_fac->input()->field()->section(
                ["title" => $ti, "description" => $desc, "skill_tree" => $se],
                $sec_des
            );
        }
        $section_advanced = $this->ui_fac->input()->field()->section(["image" => $img], $lng->txt("skmg_form_presentation"));

        $form = $this->ui_fac->input()->container()->form()->standard(
            $form_action,
            ["section_basic" => $section_basic, "section_advanced" => $section_advanced]
        );

        return $form;
    }

    public function save(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        $form = $this->initProfileForm("create");
        if ($this->request->getMethod() == "POST"
            && $this->request->getQueryParams()["profile"] == "profile_settings") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
            if (is_null($result)) {
                $tpl->setContent($this->ui_ren->render($form));
                return;
            }
            $profile = $this->skill_factory->profile()->profile(
                0,
                $result["section_basic"]["title"],
                $result["section_basic"]["description"],
                $this->skill_tree_id,
                $result["section_advanced"]["image"][0] ?? ""
            );
            $this->profile_manager->createProfile($profile);

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listProfiles");
        }
        $ilCtrl->redirect($this, "listProfiles");
    }

    public function saveLocal(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        $form = $this->initProfileForm("createLocal");
        if ($this->request->getMethod() == "POST"
            && $this->request->getQueryParams()["profile"] == "profile_settings") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
            if (is_null($result)) {
                $tpl->setContent($this->ui_ren->render($form));
                return;
            }
            $profile = $this->skill_factory->profile()->profile(
                0,
                $result["section_basic"]["title"],
                $result["section_basic"]["description"],
                $result["section_basic"]["skill_tree"],
                $result["section_advanced"]["image"][0] ?? "",
                $this->requested_ref_id
            );
            $new_profile = $this->profile_manager->createProfile($profile);
            $this->profile_manager->addRoleToProfile(
                $new_profile->getId(),
                ilParticipants::getDefaultMemberRole($this->requested_ref_id)
            );
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirectByClass("ilcontskilladmingui", "listProfiles");
        }
        $ilCtrl->redirectByClass("ilcontskilladmingui", "listProfiles");
    }

    public function update(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        $form = $this->initProfileForm("edit");
        if ($this->request->getMethod() == "POST"
            && $this->request->getQueryParams()["profile"] == "profile_settings") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
            if (is_null($result)) {
                $tpl->setContent($this->ui_ren->render($form));
                return;
            }
            $profile = $this->skill_factory->profile()->profile(
                $this->profile->getId(),
                $result["section_basic"]["title"],
                $result["section_basic"]["description"],
                $this->profile->getSkillTreeId(),
                $result["section_advanced"]["image"][0] ?? "",
                $this->profile->getRefId()
            );
            $this->profile_manager->updateProfile($profile);

            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "edit");
        }
        $ilCtrl->redirect($this, "listProfiles");
    }

    public function deleteProfiles(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        if (!empty($this->requested_profile_ids)) {
            foreach ($this->requested_profile_ids as $i) {
                $this->profile_manager->delete($i);
                $this->profile_completion_manager->deleteEntriesForProfile($i);
            }
            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirect($this, "listProfiles");
    }

    ////
    //// skill profile levels
    ////

    public function showLevelsWithTableContext(): void
    {
        $ilCtrl = $this->ctrl;

        if ($this->requested_table_profile_action === "editProfile" && !empty($this->requested_table_profile_ids)) {
            $ilCtrl->setParameter($this, "sprof_id", $this->requested_table_profile_ids[0]);
            $ilCtrl->redirect($this, "showLevels");
        }
    }

    public function showLevels(): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;

        $this->setTabs("levels");

        if ($this->skill_tree_access_manager->hasManageProfilesPermission()) {
            $ilToolbar->addButton(
                $lng->txt("skmg_assign_level"),
                $ilCtrl->getLinkTarget($this, "assignLevel")
            );
        }

        $tab = new ilSkillProfileLevelsTableGUI(
            $this,
            "showLevels",
            $this->profile
        );
        $tpl->setContent($tab->getHTML());
    }

    public function showLevelsWithLocalContext(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;
        $toolbar = $this->toolbar;

        $tabs->clearTargets();
        $tabs->setBackTarget(
            $lng->txt("back_to_course"),
            $ctrl->getLinkTargetByClass("ilcontskilladmingui", "listProfiles")
        );

        if ($this->skill_tree_access_manager->hasManageProfilesPermission()) {
            $toolbar->addButton(
                $lng->txt("skmg_assign_level"),
                $ctrl->getLinkTarget($this, "assignLevel")
            );
        }

        $tab = new ilSkillProfileLevelsTableGUI(
            $this,
            "showLevelsWithLocalContext",
            $this->profile
        );
        $tpl->setContent($tab->getHTML());
    }

    public function assignLevel(): void
    {
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $local = $this->local_context;

        $tpl->setTitle($lng->txt("skmg_profile") . ": " .
            $this->profile->getTitle());
        $tpl->setDescription("");

        //$this->setTabs("levels");

        $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_select_skill_level_assign"));

        $ilTabs->clearTargets();
        if ($local) {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "showLevelsWithLocalContext")
            );
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "showLevels")
            );
        }


        $exp = new ilSkillSelectorGUI(
            $this,
            "assignLevel",
            $this,
            "assignLevelSelectSkill",
            "cskill_id",
            $this->skill_tree_id
        );
        if (!$exp->handleCommand()) {
            $tpl->setContent($exp->getHTML());
        }
    }

    /**
     * Output level table for profile assignment
     */
    public function assignLevelSelectSkill(bool $update = false): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $local = $this->local_context;

        $ilCtrl->saveParameter($this, "cskill_id");

        $tpl->setTitle($lng->txt("skmg_profile") . ": " .
            $this->profile->getTitle());
        $tpl->setDescription("");

        $ilTabs->clearTargets();
        if ($local) {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "showLevelsWithLocalContext")
            );
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "showLevels")
            );
        }

        $table = $this->table_manager->getProfileLevelAssignmentTable($this->requested_cskill_id, $update)
                                     ->getComponent();
        $tpl->setContent($this->ui_ren->render($table));
    }

    public function updateLevelOfSelectedSkill(): void
    {
        $this->assignLevelSelectSkill(true);
    }

    public function assignLevelToProfile(Profile\SkillProfileLevel $level = null): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $local = $this->local_context;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        if ($level) {
            $this->profile_manager->updateSkillLevel($level);
        } elseif ($this->requested_table_profile_level_assignment_action === "assignLevel"
            && !empty($this->requested_table_profile_level_assignment_ids)
        ) {
            $parts = explode(":", $this->requested_cskill_id);
            $level = $this->skill_factory->profile()->profileLevel(
                $this->profile->getId(),
                (int) $parts[0],
                (int) $parts[1],
                (int) $this->requested_table_profile_level_assignment_ids[0],
                $this->profile_manager->getMaxLevelOrderNr($this->profile->getId()) + 10
            );
            $this->profile_manager->addSkillLevel($level);
        }

        // profile completion check because of profile editing
        $this->profile_completion_manager->writeCompletionEntryForAllAssignedUsersOfProfile($this->profile->getId());

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        if ($local) {
            $ilCtrl->redirect($this, "showLevelsWithLocalContext");
        }
        $ilCtrl->redirect($this, "showLevels");
    }

    public function updateLevelOfProfile(): void
    {
        if ($this->requested_table_profile_level_assignment_action === "assignLevel"
            && !empty($this->requested_table_profile_level_assignment_ids)
        ) {
            $parts = explode(":", $this->requested_cskill_id);
            $level = $this->profile_manager->getSkillLevel($this->profile->getId(), (int) $parts[0], (int) $parts[1]);
            $level_updated = $this->skill_factory->profile()->profileLevel(
                $level->getProfileId(),
                $level->getBaseSkillId(),
                $level->getTrefId(),
                (int) $this->requested_table_profile_level_assignment_ids[0],
                $level->getOrderNr()
            );
            $this->assignLevelToProfile($level_updated);
        }
    }

    public function confirmLevelAssignmentRemoval(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $tabs = $this->tabs;
        $local = $this->local_context;

        if ($local) {
            $tabs->clearTargets();
        } else {
            $this->setTabs("levels");
        }

        if (empty($this->requested_level_ass_ids)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            if ($local) {
                $ilCtrl->redirect($this, "showLevelsWithLocalContext");
            }
            $ilCtrl->redirect($this, "showLevels");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_confirm_remove_level_ass"));
            if ($local) {
                $cgui->setCancel($lng->txt("cancel"), "showLevelsWithLocalContext");
            } else {
                $cgui->setCancel($lng->txt("cancel"), "showLevels");
            }
            $cgui->setConfirm($lng->txt("remove"), "removeLevelAssignments");

            foreach ($this->requested_level_ass_ids as $i) {
                $id_arr = explode(":", $i);
                $cgui->addItem(
                    "ass_id[]",
                    $i,
                    ilBasicSkill::_lookupTitle((int) $id_arr[0]) . ": " .
                    ilBasicSkill::lookupLevelTitle((int) $id_arr[2])
                );
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function removeLevelAssignments(): void
    {
        $ilCtrl = $this->ctrl;
        $local = $this->local_context;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        if (!empty($this->requested_level_ass_ids)) {
            foreach ($this->requested_level_ass_ids as $i) {
                $id_arr = explode(":", $i);
                $level = $this->skill_factory->profile()->profileLevel(
                    $this->profile->getId(),
                    (int) $id_arr[0],
                    (int) $id_arr[1],
                    (int) $id_arr[2],
                    (int) $id_arr[3]
                );
                $this->profile_manager->removeSkillLevel($level);
            }
            $this->profile_manager->fixSkillOrderNumbering($this->profile->getId());
        }

        // profile completion check because of profile editing
        $this->profile_completion_manager->writeCompletionEntryForAllAssignedUsersOfProfile($this->profile->getId());

        if ($local) {
            $ilCtrl->redirect($this, "showLevelsWithLocalContext");
        }
        $ilCtrl->redirect($this, "showLevels");
    }

    public function saveLevelOrder(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $local = $this->local_context;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        $order = ilArrayUtil::stripSlashesArray($this->requested_level_order);
        $this->profile_manager->updateSkillOrder($this->profile->getId(), $order);

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        if ($local) {
            $ilCtrl->redirect($this, "showLevelsWithLocalContext");
        }
        $ilCtrl->redirect($this, "showLevels");
    }

    public function showUsers(): void
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;

        // add member
        if ($this->skill_tree_access_manager->hasManageProfilesPermission() && !$this->profile->getRefId() > 0) {
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $ilToolbar,
                array(
                    'auto_complete_name' => $lng->txt('user'),
                    'submit_name' => $lng->txt('skmg_assign_user')
                )
            );

            $ilToolbar->addSeparator();

            $button = $this->ui_fac->button()->standard(
                $this->lng->txt("skmg_add_assignment"),
                $this->ctrl->getLinkTargetByClass("ilRepositorySearchGUI", "start")
            );
            $ilToolbar->addComponent($button);
        }

        $this->setTabs("users");

        $table = $this->table_manager->getProfileUserAssignmentTable(
            $this->profile,
            $this->skill_tree_access_manager
        )->getComponent();
        $tpl->setContent($this->ui_ren->render($table));
    }

    public function assignUser(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        // user assignment with toolbar
        $user_id = ilObjUser::_lookupId($this->requested_user_login);
        if ($user_id > 0) {
            $this->profile_manager->addUserToProfile($this->profile->getId(), $user_id);
            // profile completion check for added user
            $this->profile_completion_manager->writeCompletionEntryForSingleProfileOfUser($user_id, $this->profile->getId());
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }

        // user assignment with ilRepositorySearchGUI
        $users = $this->requested_users;
        if (!empty($users)) {
            foreach ($users as $id) {
                if ($id > 0) {
                    $this->profile_manager->addUserToProfile($this->profile->getId(), $id);
                    // profile completion check for added user
                    $this->profile_completion_manager->writeCompletionEntryForSingleProfileOfUser($id, $this->profile->getId());
                }
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirect($this, "showUsers");
    }

    public function assignRole(array $role_ids): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        $success = false;
        foreach ($role_ids as $id) {
            if ($id > 0) {
                $this->profile_manager->addRoleToProfile($this->profile->getId(), $id);
                $this->profile_completion_manager->writeCompletionEntryForRole($id, $this->profile->getId());
                $success = true;
            }
        }
        if ($success) {
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirect($this, "showUsers");
    }

    public function removeUsers(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        if (!empty($this->requested_user_ids)) {
            foreach ($this->requested_user_ids as $i) {
                $type = ilObject::_lookupType($i);
                switch ($type) {
                    case "usr":
                        $this->profile_manager->removeUserFromProfile($this->profile->getId(), $i);
                        break;

                    case "role":
                        $this->profile_manager->removeRoleFromProfile($this->profile->getId(), $i);
                        break;

                    default:
                        echo "not deleted";
                }
            }
            $this->tpl->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "showUsers");
    }

    public function showObjects(): void
    {
        $tpl = $this->tpl;

        $this->setTabs("objects");

        $usage_info = new ilSkillUsage();
        $objects = $usage_info->getAssignedObjectsForSkillProfile($this->profile->getId());

        $table = $this->table_manager->getAssignedObjectsTable($objects)
                                     ->getComponent();
        $tpl->setContent($this->ui_ren->render($table));
    }

    public function exportProfiles(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->skill_tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        $profiles_to_export = [];
        if ($this->requested_table_profile_action === "exportProfiles"
            && !empty($this->requested_table_profile_ids)
            && $this->requested_table_profile_ids[0] === "ALL_OBJECTS"
        ) {
            $profiles = $this->skill_tree_id
                ? $this->profile_manager->getProfilesForSkillTree($this->skill_tree_id)
                : $this->profile_manager->getProfilesForAllSkillTrees();
            foreach ($profiles as $profile) {
                $profiles_to_export[] = $profile->getId();
            }
        } elseif ($this->requested_table_profile_action === "exportProfiles") {
            $profiles_to_export = array_map("intval", $this->requested_table_profile_ids);
        }

        if (empty($profiles_to_export)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "");
        }

        $exp = new ilExport();
        $conf = $exp->getConfig("Services/Skill");
        $conf->setMode(ilSkillExportConfig::MODE_PROFILES);
        $conf->setSelectedProfiles($profiles_to_export);
        $conf->setSkillTreeId($this->skill_tree_id);
        $exp->exportObject("skmg", ilObject::_lookupObjId($this->requested_ref_id));

        //ilExport::_createExportDirectory(0, "xml", "");
        //$export_dir = ilExport::_getExportDirectory($a_id, "xml", $a_type);
        //$exp->exportEntity("skprof", $_POST["id"], "", "Services/Skill", $a_title, $a_export_dir, "skprof");

        $ilCtrl->redirectByClass(array("ilobjskilltreegui", "ilexportgui"), "");
    }

    public function showImportForm(): void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $tpl->setContent($this->initInputForm()->getHTML());
    }

    public function initInputForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        $fi = new ilFileInputGUI($lng->txt("skmg_input_file"), "import_file");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $form->addItem($fi);

        // save and cancel commands
        $form->addCommandButton("importProfiles", $lng->txt("import"));
        $form->addCommandButton("", $lng->txt("cancel"));

        $form->setTitle($lng->txt("import"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    public function importProfiles(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initInputForm();
        if ($form->checkInput()) {
            $imp = new ilImport();
            $conf = $imp->getConfig("Services/Skill");
            $conf->setSkillTreeId($this->skill_tree_id);
            $imp->importEntity($_FILES["import_file"]["tmp_name"], $_FILES["import_file"]["name"], "skmg", "Services/Skill");

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }
}
