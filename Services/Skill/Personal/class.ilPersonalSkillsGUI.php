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

use ILIAS\DI\UIServices;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Skill\Service;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Chart\Bar\BarConfig;
use ILIAS\UI\Component\Chart\Bar\XAxis;
use ILIAS\Skill\Profile;
use ILIAS\Skill\Personal;
use ILIAS\Skill\Resource;
use ILIAS\Container\Skills as ContainerSkills;

/**
 * Personal skills GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilPersonalSkillsGUI:
 */
class ilPersonalSkillsGUI
{
    public const LIST_SELECTED = "selected";
    public const LIST_PROFILES = "";

    protected string $offline_mode = "";

    /**
     * @var array<int, array<int, int>>
     */
    protected array $actual_levels = [];

    /**
     * @var array<int, array<int, float>>
     */
    protected array $next_level_fuls = [];

    /**
     * @var array<int, array<int, int>>
     */
    protected array $gap_self_eval_levels = [];
    protected bool $history_view = false;

    /**
     * @var int[]
     */
    protected array $trigger_objects_filter = [];
    protected string $intro_text = "";

    /**
     * @var string[]
     */
    protected array $hidden_skills = [];
    protected string $mode = "";
    protected string $gap_mode = "";
    protected int $gap_mode_obj_id = 0;
    protected string $gap_mode_type = "";
    protected string $gap_cat_title = "";

    protected UIServices $ui;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilHelpGUI $help;
    protected ilSetting $setting;
    protected ilObjUser $user;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected ilAccessHandler $access;
    protected Factory $ui_fac;
    protected Renderer $ui_ren;
    protected ResourceStorage $storage;
    protected DataFactory $data_fac;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_definition;

    protected int $obj_id = 0;

    /**
     * @var array<string, array{base_skill_id: int, tref_id: int, title: int}>
     */
    protected array $obj_skills = [];
    protected int $profile_id = 0;

    /**
     * @var Profile\SkillProfileLevel[]
     */
    protected array $profile_levels = [];

    /**
     * @var Profile\SkillProfile[]
     */
    protected array $user_profiles = [];

    /**
     * @var Profile\SkillRoleProfile[]
     */
    protected array $cont_profiles = [];
    protected bool $use_materials = false;
    protected ilSkillManagementSettings $skmg_settings;
    protected ilPersonalSkillsFilterGUI $filter;
    protected Service\SkillPersonalGUIRequest $personal_gui_request;
    protected ilSkillTreeRepository $tree_repo;
    protected ilSkillLevelRepository $level_repo;
    protected Service\SkillTreeService $tree_service;
    protected Profile\SkillProfileManager $profile_manager;
    protected Profile\SkillProfileCompletionManager $profile_completion_manager;
    protected Personal\PersonalSkillManager $personal_manager;
    protected Personal\AssignedMaterialManager $assigned_material_manager;
    protected Personal\SelfEvaluationManager $self_evaluation_manager;
    protected Resource\SkillResourcesManager $resource_manager;
    protected ContainerSkills\ContainerSkillInternalFactoryService $cont_factory_service;
    protected string $requested_list_mode = self::LIST_PROFILES;
    protected int $requested_node_id = 0;
    protected int $requested_profile_id = 0;
    protected int $requested_skill_id = 0;

    /**
     * @var int[]
     */
    protected array $requested_skill_ids = [];
    protected int $requested_basic_skill_id = 0;
    protected int $requested_tref_id = 0;
    protected int $requested_level_id = 0;
    protected int $requested_self_eval_level_id = 0;
    protected int $requested_wsp_id = 0;

    /**
     * @var int[]
     */
    protected array $requested_wsp_ids = [];

    /**
     * @var string[]
     */
    protected array $trigger_user_filter = [];

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $this->setting = $DIC["ilSetting"];
        $this->user = $DIC->user();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->access = $DIC->access();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->ui = $DIC->ui();
        $this->storage = $DIC->resourceStorage();
        $this->data_fac = new \ILIAS\Data\Factory();
        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
        $this->personal_gui_request = $DIC->skills()->internal()->gui()->personal_request();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->level_repo = $DIC->skills()->internal()->repo()->getLevelRepo();
        $this->tree_service = $DIC->skills()->tree();
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();
        $this->profile_completion_manager = $DIC->skills()->internal()->manager()->getProfileCompletionManager();
        $this->personal_manager = $DIC->skills()->internal()->manager()->getPersonalSkillManager();
        $this->assigned_material_manager = $DIC->skills()->internal()->manager()->getAssignedMaterialManager();
        $this->self_evaluation_manager = $DIC->skills()->internal()->manager()->getSelfEvaluationManager();
        $this->resource_manager = $DIC->skills()->internal()->manager()->getResourceManager();
        $this->cont_factory_service = $DIC->skills()->internalContainer()->factory();

        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;
        $lng = $this->lng;
        $ilSetting = $this->setting;


        $lng->loadLanguageModule('skmg');
        $ilHelp->setScreenIdComponent("skill");

        $ilCtrl->saveParameter($this, "skill_id");
        $ilCtrl->saveParameter($this, "tref_id");
        $ilCtrl->saveParameter($this, "profile_id");
        $ilCtrl->saveParameter($this, "list_mode");

        $this->requested_list_mode = $this->personal_gui_request->getListMode();
        $this->requested_node_id = $this->personal_gui_request->getNodeId();
        $this->requested_profile_id = $this->personal_gui_request->getProfileId();
        $this->requested_skill_id = $this->personal_gui_request->getSkillId();
        $this->requested_skill_ids = $this->personal_gui_request->getSkillIds();
        $this->requested_basic_skill_id = $this->personal_gui_request->getBasicSkillId();
        $this->requested_tref_id = $this->personal_gui_request->getTrefId();
        $this->requested_level_id = $this->personal_gui_request->getLevelId();
        $this->requested_self_eval_level_id = $this->personal_gui_request->getSelfEvaluationLevelId();
        $this->requested_wsp_id = $this->personal_gui_request->getWorkspaceId();
        $this->requested_wsp_ids = $this->personal_gui_request->getWorkspaceIds();

        $this->user_profiles = $this->profile_manager->getProfilesOfUser($this->user->getId());
        $this->cont_profiles = [];

        $this->use_materials = !$ilSetting->get("disable_personal_workspace");

        $this->skmg_settings = new ilSkillManagementSettings();

        $this->filter = new ilPersonalSkillsFilterGUI();
    }

    public function getFilter(): ilPersonalSkillsFilterGUI
    {
        return $this->filter;
    }

    public function setProfileId(int $a_val): void
    {
        $this->profile_id = $a_val;
    }

    public function getProfileId(): int
    {
        return $this->profile_id;
    }

    /**
     * @param array<int, array<int, int>> $a_val self evaluation values key1: base_skill_id, key2: tref_id: value: level id
     */
    public function setGapAnalysisSelfEvalLevels(array $a_val): void
    {
        $this->gap_self_eval_levels = $a_val;
    }

    /**
     * @return array self evaluation values key1: base_skill_id, key2: tref_id: value: level id
     */
    public function getGapAnalysisSelfEvalLevels(): array
    {
        return $this->gap_self_eval_levels;
    }

    public function setHistoryView(bool $a_val): void
    {
        $this->history_view = $a_val;
    }

    public function getHistoryView(): bool
    {
        return $this->history_view;
    }

    public function getNonHistoricGapModeView(): bool
    {
        return ($this->mode == "gap" && !$this->history_view);
    }

    public function getTriggerObjectsFilter(): array
    {
        return $this->trigger_objects_filter;
    }

    public function setTriggerObjectsFilter(array $trigger_objects_filter): void
    {
        $this->trigger_objects_filter = $trigger_objects_filter;
    }

    public function setIntroText(string $a_val): void
    {
        $this->intro_text = $a_val;
    }

    public function getIntroText(): string
    {
        return $this->intro_text;
    }

    /**
     * @return string[]
     */
    public function getTriggerUserFilter(): array
    {
        return $this->trigger_user_filter;
    }

    /**
     * @param string[] $trigger_user_filter
     */
    public function setTriggerUserFilter(array $trigger_user_filter): void
    {
        $this->trigger_user_filter = $trigger_user_filter;
    }

    public function hideSkill(int $a_skill_id, int $a_tref_id = 0): void
    {
        $this->hidden_skills[] = $a_skill_id . ":" . $a_tref_id;
    }

    public function getObjectId(): int
    {
        return $this->obj_id;
    }

    /**
     * @return \ILIAS\Container\Skills\ContainerSkill[]
     */
    public function getObjectSkills(): array
    {
        return $this->obj_skills;
    }

    /**
     * @param \ILIAS\Container\Skills\ContainerSkill[] $a_skills
     */
    public function setObjectSkills(int $a_obj_id, array $a_skills): void
    {
        $this->obj_id = $a_obj_id;
        $this->obj_skills = $a_skills;
    }

    public function setObjectSkillProfiles(
        int $cont_member_role_id
    ): void {
        $this->cont_profiles = $this->profile_manager->getAllProfilesOfRole($cont_member_role_id);
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $next_class = $ilCtrl->getNextClass($this);


        $cmd = $ilCtrl->getCmd("render");

        //$tpl->setTitle($lng->txt("skills"));
        //$tpl->setTitleIcon(ilUtil::getImagePath("icon_skmg.svg"));

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function setTabs(string $a_activate): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        if (!empty($this->user_profiles)) {
            $ilCtrl->setParameter($this, "list_mode", self::LIST_PROFILES);
            $ilTabs->addTab(
                "profile",
                $lng->txt("skmg_assigned_profiles"),
                $ilCtrl->getLinkTarget($this, "render")
            );
        }

        // list skills
        $ilCtrl->setParameter($this, "list_mode", self::LIST_SELECTED);
        $ilTabs->addTab(
            "list_skills",
            $lng->txt("skmg_selected_skills"),
            $ilCtrl->getLinkTarget($this, "render")
        );

        $ilCtrl->clearParameterByClass(get_class($this), "list_mode");

        // assign materials

        $ilTabs->activateTab($a_activate);
    }

    public function setOfflineMode(string $a_file_path): void
    {
        $this->offline_mode = $a_file_path;
    }

    public function getOfflineMode(): string
    {
        return $this->offline_mode;
    }

    protected function render(): void
    {
        if ($this->requested_list_mode == self::LIST_SELECTED || empty($this->user_profiles)) {
            $this->listSkills();
        } else {
            $this->listAllAssignedProfiles();
        }
    }

    public function listSkills(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;
        $main_tpl = $this->tpl;
        $ilToolbar = $this->toolbar;

        $tpl = new ilTemplate("tpl.skill_filter.html", true, true, "Services/Skill");

        $this->setTabs("list_skills");

        // skill selection / add new personal skill
        $ilToolbar->addFormButton(
            $lng->txt("skmg_add_skill"),
            "listSkillsForAdd"
        );
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));

        $filter_toolbar = new ilToolbarGUI();
        $filter_toolbar->setFormAction($ilCtrl->getFormAction($this));
        $this->getFilter()->addToToolbar($filter_toolbar, false);

        $skills = $this->personal_manager->getSelectedUserSkills($ilUser->getId());
        $html = "";
        foreach ($skills as $s) {
            $path = $this->tree_service->getSkillTreePath($s->getSkillNodeId());

            // check draft
            foreach ($path as $p) {
                if ($p["status"] == ilSkillTreeNode::STATUS_DRAFT) {
                    continue(2);
                }
            }
            $html .= $this->getSkillHTML($s->getSkillNodeId(), 0, true);
        }

        // list skills

        if ($html != "") {
            $filter_toolbar->addFormButton($this->lng->txt("skmg_refresh_view"), "applyFilter");
            $tpl->setVariable("FILTER", $filter_toolbar->getHTML());
            $html = $tpl->get() . $html;
        } else {
            $box = $this->ui_fac->messageBox()->info($lng->txt("skmg_no_skills_selected_info"));
            $html = $this->ui_ren->render($box);
        }

        $main_tpl->setContent($html);
    }

    protected function applyFilter(): void
    {
        $this->getFilter()->save();
        $this->ctrl->setParameter($this, "list_mode", self::LIST_SELECTED);
        $this->ctrl->redirect($this, "listSkills");
    }

    /**
     * Apply filter for profiles view
     */
    protected function applyFilterAssignedProfiles(): void
    {
        $this->getFilter()->save();
        $this->ctrl->redirect($this, "listAssignedProfile");
    }


    /**
     * Get skill presentation HTML
     *
     * $a_top_skill_id is a node of the skill "main tree", it can be a tref id!
     * - called in listSkills (this class) -> $a_top_skill is the selected user skill (main tree node id), tref_id not set
     * - called in ilPortfolioPage -> $a_top_skill is the selected user skill (main tree node id), tref_id not set
     * - called in getGapAnalysis (this class) -> $a_top_skill id is the (basic) skill_id, tref_id may be set
     */
    public function getSkillHTML(
        int $a_top_skill_id,
        int $a_user_id = 0,
        bool $a_edit = false,
        int $a_tref_id = 0
    ): string {
        $main_tpl = $this->tpl;

        // user interface plugin slot + default rendering
        $uip = new ilUIHookProcessor(
            "Services/Skill",
            "personal_skill_html",
            array("personal_skills_gui" => $this, "top_skill_id" => $a_top_skill_id, "user_id" => $a_user_id,
                  "edit" => $a_edit, "tref_id" => $a_tref_id)
        );
        $skill_html = "";
        if (!$uip->replaced()) {
            $skill_html = $this->renderSkillHTML($a_top_skill_id, $a_user_id, $a_edit, $a_tref_id);
        }
        $skill_html = $uip->getHTML($skill_html);
        $main_tpl->addJavaScript("./Services/Skill/js/SkillEntries.js");

        return $skill_html;
    }

    public function renderSkillHTML(
        int $a_top_skill_id,
        int $a_user_id = 0,
        bool $a_edit = false,
        int $a_tref_id = 0
    ): string {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        $sub_panels = [];

        if ($a_user_id == 0) {
            $user = $ilUser;
        } else {
            $user = new ilObjUser($a_user_id);
        }

        $tpl = new ilTemplate("tpl.skill_pres.html", true, true, "Services/Skill");

        $vtree = $this->tree_repo->getVirtualTreeForNodeId($a_top_skill_id);
        $tref_id = $a_tref_id;
        $skill_id = $a_top_skill_id;
        if (ilSkillTreeNode::_lookupType($a_top_skill_id) == "sktr") {
            $tref_id = $a_top_skill_id;
            $skill_id = ilSkillTemplateReference::_lookupTemplateId($a_top_skill_id);
        }
        $b_skills = $vtree->getSubTreeForCSkillId($skill_id . ":" . $tref_id, true);

        foreach ($b_skills as $bs) {
            $bs["id"] = (int) $bs["skill_id"];
            $bs["tref"] = (int) $bs["tref_id"];

            $path = $this->tree_service->getSkillTreePath($bs["id"], $bs["tref"]);

            $panel_comps = [];


            // check draft
            foreach ($path as $p) {
                if ($p["status"] == ilSkillTreeNode::STATUS_DRAFT) {
                    continue(2);
                }
            }
            reset($path);

            $skill = ilSkillTreeNodeFactory::getInstance($bs["id"]);
            $level_data = $skill->getLevelData();


            $title = $sep = "";
            $description = "";
            $found = false;
            foreach ($path as $p) {
                if ($found) {
                    $title .= $sep . $p["title"];
                    $sep = " > ";
                    $description = $p["description"];
                }
                if ($a_top_skill_id == $p["child"]) {
                    $found = true;
                }
            }
            if (empty($title)) {
                $title = $lng->txt("skmg_skill_overview");
            }

            //  skill description
            $panel_comps[] = $this->ui_fac->legacy($this->getBasicSkillDescription((string) $description));


            // skill level description
            $skl_lvl_desc = $this->getSkillLevelDescription($skill);
            if (!empty($skl_lvl_desc)) {
                $acc = new ilAccordionGUI();
                $acc->setBehaviour(ilAccordionGUI::ALL_CLOSED);
                $acc->addItem($lng->txt('skmg_skill_levels'), $skl_lvl_desc);
                $panel_comps[] = $this->ui_fac->legacy($acc->getHTML());
            }

            $prof_comp_head_rendered = false;
            $has_at_least_one_entry = false;
            if ($this->getProfileId() > 0) {
                if ($this->getNonHistoricGapModeView()) {
                    if (!empty($self_eval_gap_item_prof = $this->getSelfEvalGapItem($level_data, $bs["tref"]))) {
                        $panel_comps[] = $this->ui_fac->legacy($this->getSkillEntriesHeader(ilBasicSkill::EVAL_BY_SELF));
                        $has_at_least_one_entry = true;
                    }
                    $panel_comps[] = $this->ui_fac->legacy($self_eval_gap_item_prof);
                } else {
                    // get all self eval entries and render them
                    $self_eval_entries_latest = $this->getLatestEntriesForSkillHTML(
                        $a_top_skill_id,
                        $bs,
                        $skill,
                        $user,
                        ilBasicSkill::EVAL_BY_SELF,
                        $level_data
                    );
                    $self_eval_entries_non_latest = $this->getNonLatestEntriesForSkillHTML(
                        $a_top_skill_id,
                        $bs,
                        $skill,
                        $user,
                        ilBasicSkill::EVAL_BY_SELF,
                        $level_data
                    );

                    if (!empty($self_eval_entries_latest)) {
                        $panel_comps[] = $this->ui_fac->legacy($this->getSkillEntriesHeader(ilBasicSkill::EVAL_BY_SELF));
                        $has_at_least_one_entry = true;
                    }
                    $panel_comps[] = $this->ui_fac->legacy($self_eval_entries_latest);
                    $panel_comps[] = $this->ui_fac->legacy($self_eval_entries_non_latest);
                }

                if (!$this->skmg_settings->getHideProfileBeforeSelfEval() ||
                    ilBasicSkill::hasSelfEvaluated($user->getId(), $bs["id"], $bs["tref"])) {
                    if ($this->getFilter()->showTargetLevel()) {
                        $panel_comps[] = $this->ui_fac->legacy($this->getSkillEntriesHeader(ilBasicSkill::EVAL_BY_OTHERS));
                        $prof_comp_head_rendered = true;
                        $panel_comps[] = $this->ui_fac->legacy($this->getProfileTargetItem($this->getProfileId(), $level_data, $bs["tref"]));
                    }
                }
            }

            if ($this->getNonHistoricGapModeView()) {
                if (!empty($actual_gap_item = $this->getActualGapItem($level_data, $bs["tref"]))) {
                    $panel_comps[] = $this->ui_fac->legacy($actual_gap_item);
                    $has_at_least_one_entry = true;
                }
                if ($this->getProfileId() == 0) {
                    if (!empty($self_eval_gap_item_non_prof = $this->getSelfEvalGapItem($level_data, $bs["tref"]))) {
                        $panel_comps[] = $this->ui_fac->legacy($self_eval_gap_item_non_prof);
                        $has_at_least_one_entry = true;
                    }
                }
            } else {
                if ($this->getProfileId() > 0) {
                    // get all non-self eval entries and render them
                    $object_entries_latest = $this->getLatestEntriesForSkillHTML(
                        $a_top_skill_id,
                        $bs,
                        $skill,
                        $user,
                        ilBasicSkill::EVAL_BY_OTHERS,
                        $level_data
                    );
                    $object_entries_non_latest = $this->getNonLatestEntriesForSkillHTML(
                        $a_top_skill_id,
                        $bs,
                        $skill,
                        $user,
                        ilBasicSkill::EVAL_BY_OTHERS,
                        $level_data
                    );

                    if (!empty($object_entries_latest) && !$prof_comp_head_rendered) {
                        $panel_comps[] = $this->ui_fac->legacy($this->getSkillEntriesHeader(ilBasicSkill::EVAL_BY_OTHERS));
                    }
                    if (!empty($object_entries_latest)) {
                        $has_at_least_one_entry = true;
                    }
                    $panel_comps[] = $this->ui_fac->legacy($object_entries_latest);
                    $panel_comps[] = $this->ui_fac->legacy($object_entries_non_latest);
                } else {
                    // get all skill entries and render them
                    $all_entries_latest = $this->getLatestEntriesForSkillHTML(
                        $a_top_skill_id,
                        $bs,
                        $skill,
                        $user,
                        ilBasicSkill::EVAL_BY_ALL,
                        $level_data
                    );
                    $all_entries_non_latest = $this->getNonLatestEntriesForSkillHTML(
                        $a_top_skill_id,
                        $bs,
                        $skill,
                        $user,
                        ilBasicSkill::EVAL_BY_ALL,
                        $level_data
                    );

                    if (!empty($all_entries_latest) && !$prof_comp_head_rendered) {
                        $panel_comps[] = $this->ui_fac->legacy($this->getSkillEntriesHeader(ilBasicSkill::EVAL_BY_OTHERS));
                    }
                    if (!empty($all_entries_latest)) {
                        $has_at_least_one_entry = true;
                    }
                    $panel_comps[] = $this->ui_fac->legacy($all_entries_latest);
                    $panel_comps[] = $this->ui_fac->legacy($all_entries_non_latest);
                }
            }

            if (!$has_at_least_one_entry) {
                $panel_comps[] = $this->ui_fac->legacy("<br/>" . $lng->txt("skmg_no_skill_entries"));
            }

            // suggested resources

            $sub = $this->ui_fac->panel()->sub($title, $panel_comps);
            if ($this->getFilter()->showMaterialsRessources() && $this->getProfileId() > 0
                && $res = $this->getSuggestedResourcesForProfile($level_data, $bs["id"], $bs["tref"], $this->gap_mode_obj_id)) {
                $sub = $sub->withFurtherInformation($res);
            } elseif ($this->getFilter()->showMaterialsRessources() && $this->getProfileId() == 0 && !$this->gap_mode_obj_id) {
                // no profile, just list all resources and only in global view
                $sugg = $this->getAllSuggestedResources($bs["id"], $bs["tref"]);
                if ($sugg) {
                    $sub = $sub->withFurtherInformation($sugg);
                }
            }
            if ($a_edit) {
                $actions = [];
                $ilCtrl->setParameterByClass("ilpersonalskillsgui", "skill_id", $a_top_skill_id);
                $ilCtrl->setParameterByClass("ilpersonalskillsgui", "tref_id", $bs["tref"]);
                $ilCtrl->setParameterByClass("ilpersonalskillsgui", "basic_skill_id", $bs["id"]);
                $ilCtrl->setParameterByClass("ilpersonalskillsgui", "list_mode", $this->requested_list_mode);
                if ($this->use_materials) {
                    $actions[] = $this->ui_fac->button()->shy(
                        $lng->txt('skmg_assign_materials'),
                        $ilCtrl->getLinkTargetByClass("ilpersonalskillsgui", "assignMaterials")
                    );
                }
                $actions[] = $this->ui_fac->button()->shy(
                    $lng->txt('skmg_self_evaluation'),
                    $ilCtrl->getLinkTargetByClass("ilpersonalskillsgui", "selfEvaluation")
                );
                $sub = $sub->withActions($this->ui_fac->dropdown()->standard($actions)->withLabel($lng->txt("actions")));
            }

            $sub_panels[] = $sub;

            // materials
            if ($this->mode != "gap" && $this->getFilter()->showMaterialsRessources() && $this->use_materials) {
                $mat = $this->getMaterials($level_data, $bs["tref"], $user->getId());
                if ($mat) {
                    $sub_panels[] = $mat;
                }
            }

            $tpl->parseCurrentBlock();
        }

        $des = $this->getSkillCategoryDescription($skill_id, $tref_id);

        //put the description of the skill category to the very top of the sub panels
        $sub_panels = $this->ui_fac->legacy($des . $this->ui_ren->render($sub_panels));

        $panel = $this->ui_fac->panel()->standard(
            ilSkillTreeNode::_lookupTitle($skill_id, $tref_id),
            $sub_panels
        );

        if ($a_edit && $this->getProfileId() == 0) {
            $actions = [];

            $ilCtrl->setParameterByClass("ilpersonalskillsgui", "skill_id", $a_top_skill_id);
            $actions[] = $this->ui_fac->button()->shy(
                $lng->txt('skmg_remove_skill'),
                $ilCtrl->getLinkTargetByClass("ilpersonalskillsgui", "confirmSkillRemove")
            );

            $panel = $panel->withActions($this->ui_fac->dropdown()->standard($actions)->withLabel($lng->txt("actions")));
        }

        return $this->ui_ren->render($panel);
    }


    /**
     * Get material file name and goto url
     */
    public function getMaterialInfo(int $a_wsp_id, int $a_user_id): array
    {
        $ws_tree = new ilWorkspaceTree($a_user_id);
        $ws_access = new ilWorkspaceAccessHandler();

        $obj_id = $ws_tree->lookupObjectId($a_wsp_id);
        $caption = ilObject::_lookupTitle($obj_id);

        if (!$this->getOfflineMode()) {
            $url = $ws_access->getGotoLink($a_wsp_id, $obj_id);
        } else {
            $url = $this->getOfflineMode() . "file_" . $obj_id . "/";

            // all possible material types for now
            switch (ilObject::_lookupType($obj_id)) {
                case "tstv":
                    $obj = new ilObjTestVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;

                case "excv":
                    $obj = new ilObjExerciseVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;

                case "crsv":
                    $obj = new ilObjCourseVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;

                case "cmxv":
                    $obj = new ilObjCmiXapiVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;

                case "ltiv":
                    $obj = new ilObjLTIConsumerVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;

                case "scov":
                    $obj = new ilObjSCORMVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;

                case "file":
                    $file = new ilObjFile($obj_id, false);
                    $url .= $file->getFileName();
                    break;
            }
        }

        return array($caption, $url, $obj_id);
    }

    public function addSkill(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        $this->personal_manager->addPersonalSkill($ilUser->getId(), $this->requested_node_id);

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_object_modified"));
        $ilCtrl->setParameter($this, "list_mode", self::LIST_SELECTED);
        $ilCtrl->redirect($this, "listSkills");
    }

    public function confirmSkillRemove(): void
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        if ($this->requested_skill_id > 0) {
            $this->requested_skill_ids[] = $this->requested_skill_id;
        }
        if (empty($this->requested_skill_ids)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->setParameter($this, "list_mode", self::LIST_SELECTED);
            $ilCtrl->redirect($this, "listSkills");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_really_remove_skills"));
            $cgui->setCancel($lng->txt("cancel"), "listSkills");
            $cgui->setConfirm($lng->txt("remove"), "removeSkills");

            foreach ($this->requested_skill_ids as $i) {
                $cgui->addItem("id[]", (string) $i, ilSkillTreeNode::_lookupTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function removeSkills(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        if (!empty($this->requested_skill_ids)) {
            foreach ($this->requested_skill_ids as $n_id) {
                $this->personal_manager->removePersonalSkill($ilUser->getId(), $n_id);
            }
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_object_modified"));
        $ilCtrl->setParameter($this, "list_mode", self::LIST_SELECTED);
        $ilCtrl->redirect($this, "listSkills");
    }


    //
    // Materials assignments
    //

    /**
     * Assign materials to skill levels
     */
    public function assignMaterials(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilTabs = $this->tabs;

        $cmd = ($this->requested_list_mode == self::LIST_SELECTED || empty($this->user_profiles))
            ? "render"
            : "listAssignedProfile";
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, $cmd)
        );

        $ilCtrl->saveParameter($this, "skill_id");
        $ilCtrl->saveParameter($this, "basic_skill_id");
        $ilCtrl->saveParameter($this, "tref_id");

        $tpl->setTitle(ilSkillTreeNode::_lookupTitle($this->requested_skill_id));
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_" .
            ilSkillTreeNode::_lookupType($this->requested_skill_id) .
            ".svg"));

        // basic skill selection
        $vtree = $this->tree_repo->getVirtualTreeForNodeId($this->requested_skill_id);
        $tref_id = 0;
        $skill_id = $this->requested_skill_id;
        if (ilSkillTreeNode::_lookupType($this->requested_skill_id) == "sktr") {
            $tref_id = $this->requested_skill_id;
            $skill_id = ilSkillTemplateReference::_lookupTemplateId($this->requested_skill_id);
        }
        $bs = $vtree->getSubTreeForCSkillId($skill_id . ":" . $tref_id, true);

        $options = [];
        foreach ($bs as $b) {
            $options[$b["skill_id"]] = ilSkillTreeNode::_lookupTitle((int) $b["skill_id"]);
        }

        $cur_basic_skill_id = ($this->requested_basic_skill_id > 0)
            ? $this->requested_basic_skill_id
            : key($options);

        $ilCtrl->setParameter($this, "basic_skill_id", $cur_basic_skill_id);

        if (count($options) > 1) {
            $si = new ilSelectInputGUI($lng->txt("skmg_skill"), "basic_skill_id");
            $si->setOptions($options);
            $si->setValue($cur_basic_skill_id);
            $ilToolbar->addInputItem($si, true);
            $ilToolbar->addFormButton(
                $lng->txt("select"),
                "assignMaterials"
            );

            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
        }

        // table
        $tab = new ilSkillAssignMaterialsTableGUI(
            $this,
            "assignMaterials",
            $this->requested_skill_id,
            $this->requested_tref_id,
            $cur_basic_skill_id
        );

        $tpl->setContent($tab->getHTML());
    }


    /**
     * Assign materials to skill level
     */
    public function assignMaterial(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilSetting = $this->setting;
        $ui = $this->ui;

        $message = "";
        if (!$ilSetting->get("disable_personal_workspace")) {
            $url = 'ilias.php?baseClass=ilDashboardGUI&amp;cmd=jumpToWorkspace';
            $mbox = $ui->factory()->messageBox()->info($lng->txt("skmg_ass_materials_from_workspace"))
                       ->withLinks([$ui->factory()->link()->standard(
                           $lng->txt("personal_resources"),
                           $url
                       )]);
            $message = $ui->renderer()->render($mbox);
        }

        $ilCtrl->saveParameter($this, "skill_id");
        $ilCtrl->saveParameter($this, "level_id");
        $ilCtrl->saveParameter($this, "tref_id");
        $ilCtrl->saveParameter($this, "basic_skill_id");

        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "assignMaterials")
        );


        $exp = new ilWorkspaceExplorerGUI($ilUser->getId(), $this, "assignMaterial", $this, "");
        $exp->setTypeWhiteList(array("blog", "wsrt", "wfld", "file", "tstv", "excv"));
        $exp->setSelectableTypes(array("file", "tstv", "excv"));
        $exp->setSelectMode("wsp_ids", true);
        if ($exp->handleCommand()) {
            return;
        }

        // fill template
        $mtpl = new ilTemplate("tpl.materials_selection.html", true, true, "Services/Skill");
        $mtpl->setVariable("EXP", $exp->getHTML());

        // toolbars
        $tb = new ilToolbarGUI();
        $tb->addFormButton(
            $lng->txt("select"),
            "selectMaterial"
        );
        $tb->setFormAction($ilCtrl->getFormAction($this));
        $tb->setOpenFormTag(true);
        $tb->setCloseFormTag(false);
        $mtpl->setVariable("TOOLBAR1", $tb->getHTML());
        $tb->setOpenFormTag(false);
        $tb->setCloseFormTag(true);
        $mtpl->setVariable("TOOLBAR2", $tb->getHTML());

        $tpl->setContent($message . $mtpl->get());
    }

    public function selectMaterial(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;


        if (!empty($this->requested_wsp_ids)) {
            foreach ($this->requested_wsp_ids as $w) {
                $this->assigned_material_manager->assignMaterial(
                    $ilUser->getId(),
                    $this->requested_skill_id,
                    $this->requested_tref_id,
                    $this->requested_basic_skill_id,
                    $this->requested_level_id,
                    $w
                );
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->saveParameter($this, "skill_id");
        $ilCtrl->saveParameter($this, "level_id");
        $ilCtrl->saveParameter($this, "tref_id");
        $ilCtrl->saveParameter($this, "basic_skill_id");

        $ilCtrl->redirect($this, "assignMaterials");
    }

    public function removeMaterial(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;


        $this->assigned_material_manager->removeAssignedMaterial(
            $ilUser->getId(),
            $this->requested_tref_id,
            $this->requested_level_id,
            $this->requested_wsp_id
        );
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "assignMaterials");
    }


    //
    // Self evaluation
    //

    public function selfEvaluation(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilTabs = $this->tabs;

        $cmd = ($this->requested_list_mode == self::LIST_SELECTED || empty($this->user_profiles))
            ? "render"
            : "listAssignedProfile";
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, $cmd)
        );

        $ilCtrl->saveParameter($this, "skill_id");
        $ilCtrl->saveParameter($this, "basic_skill_id");
        $ilCtrl->saveParameter($this, "tref_id");

        $tpl->setTitle(ilSkillTreeNode::_lookupTitle($this->requested_skill_id));
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_" .
            ilSkillTreeNode::_lookupType($this->requested_skill_id) .
            ".svg"));

        // basic skill selection
        $vtree = $this->tree_repo->getVirtualTreeForNodeId($this->requested_skill_id);
        $tref_id = 0;
        $skill_id = $this->requested_skill_id;
        if (ilSkillTreeNode::_lookupType($this->requested_skill_id) == "sktr") {
            $tref_id = $this->requested_skill_id;
            $skill_id = ilSkillTemplateReference::_lookupTemplateId($this->requested_skill_id);
        }
        $bs = $vtree->getSubTreeForCSkillId($skill_id . ":" . $tref_id, true);


        $options = [];
        foreach ($bs as $b) {
            $options[$b["skill_id"]] = ilSkillTreeNode::_lookupTitle((int) $b["skill_id"]);
        }

        $cur_basic_skill_id = ($this->requested_basic_skill_id > 0)
            ? $this->requested_basic_skill_id
            : key($options);

        $ilCtrl->setParameter($this, "basic_skill_id", $cur_basic_skill_id);

        if (count($options) > 1) {
            $si = new ilSelectInputGUI($lng->txt("skmg_skill"), "basic_skill_id");
            $si->setOptions($options);
            $si->setValue($cur_basic_skill_id);
            $ilToolbar->addInputItem($si, true);
            $ilToolbar->addFormButton(
                $lng->txt("select"),
                "selfEvaluation"
            );

            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
        }

        // table
        $tab = new ilSelfEvaluationSimpleTableGUI(
            $this,
            "selfEvaluation",
            $this->requested_skill_id,
            $this->requested_tref_id,
            $cur_basic_skill_id
        );

        $tpl->setContent($tab->getHTML());
    }

    public function saveSelfEvaluation(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        $this->self_evaluation_manager->saveSelfEvaluation(
            $ilUser->getId(),
            $this->requested_skill_id,
            $this->requested_tref_id,
            $this->requested_basic_skill_id,
            $this->requested_self_eval_level_id
        );
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        /*		$ilCtrl->saveParameter($this, "skill_id");
                $ilCtrl->saveParameter($this, "level_id");
                $ilCtrl->saveParameter($this, "tref_id");
                $ilCtrl->saveParameter($this, "basic_skill_id");*/

        $cmd = ($this->requested_list_mode == self::LIST_SELECTED || empty($this->user_profiles))
            ? "render" : "listAssignedProfile";
        $ilCtrl->redirect($this, $cmd);
    }

    public function listSkillsForAdd(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;


        $ilCtrl->setParameter($this, "list_mode", self::LIST_SELECTED);
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "render")
        );
        $ilCtrl->clearParameterByClass(get_class($this), "list_mode");

        $exp = new ilPersonalSkillExplorerGUI($this, "listSkillsForAdd", $this, "addSkill");
        if ($exp->getHasSelectableNodes()) {
            if (!$exp->handleCommand()) {
                $tpl->setContent($exp->getHTML());
            }
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_select_skill"));
        } else {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_no_nodes_selectable"));
        }
    }

    public function showProfiles(): void
    {
        $this->ctrl->redirectByClass("ilContSkillPresentationGUI", "showProfiles");
    }

    public function listAllProfilesForGap(): void
    {
        if (empty($this->cont_profiles)) {
            $this->tpl->setContent($this->showInfoBoxForProfiles());
            return;
        }

        $prof_list = $this->getProfilesListed($this->cont_profiles, true);

        $html = $this->showInfoBoxForProfiles() . $this->ui_ren->render($prof_list);
        $this->tpl->setContent($html);
    }

    public function listProfileForGap(): void
    {
        // needed fix for profiles in gap view, because there is no filter shown (yet)
        $this->getFilter()->clear();
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "showProfiles")
        );
        $this->setProfileId($this->requested_profile_id);
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_skmg.svg"));
        $this->tpl->setTitle($this->profile_manager->lookupTitle($this->getProfileId()));

        $this->tpl->setContent($this->getGapAnalysisHTML());
    }

    public function listRecordsForGap(): void
    {
        if (empty($this->getObjectSkills())) {
            $this->tpl->setContent($this->showInfoBoxForRecords());
            return;
        }

        $this->tpl->setContent($this->getGapAnalysisHTML());
    }

    /**
     * @param Profile\SkillProfile[] $profiles
     */
    protected function getProfilesListed(array $profiles, bool $gap_mode = false): ILIAS\UI\Component\Panel\Listing\Listing
    {
        $prof_items = [];

        foreach ($profiles as $p) {
            $image_id = $p->getImageId();
            if ($image_id) {
                $identification = $this->storage->manage()->find($image_id);
                $src = $this->storage->consume()->src($identification);
                $image = $this->ui_fac->image()->responsive($src->getSrc(), $this->lng->txt("skmg_custom_image_alt"));
            } else {
                $image = $this->ui_fac->image()->responsive(
                    "./templates/default/images/logo/ilias_logo_72x72.png",
                    "ILIAS"
                );
            }

            $this->ctrl->setParameter($this, "profile_id", $p->getId());
            $link = $this->ui_fac->link()->standard(
                $p->getTitle(),
                $this->ctrl->getLinkTarget($this, $gap_mode ? "listProfileForGap" : "listAssignedProfile")
            );
            $this->ctrl->setParameter($this, "profile_id", "");

            $chart_value = $this->profile_completion_manager->getProfileProgress($this->user->getId(), $p->getId());
            $prof_item = $this->ui_fac->item()->standard($link)
                                      ->withDescription($p->getDescription())
                                      ->withLeadImage($image)
                                      ->withProgress($this->ui_fac->chart()->progressMeter()->standard(100, $chart_value));

            $prof_items[] = $prof_item;
        }

        $prof_list = $this->ui_fac->panel()->listing()->standard("", array(
            $this->ui_fac->item()->group("", $prof_items)
        ));

        return $prof_list;
    }

    protected function showInfoBoxForProfiles(): string
    {
        if (!empty($this->cont_profiles)) {
            $link = $this->ui_fac->link()->standard(
                $this->lng->txt("skmg_open_all_assigned_profiles"),
                $this->ctrl->getLinkTargetByClass(["ilDashboardGUI", "ilAchievementsGUI", "ilPersonalSkillsGUI"])
            );
            $box = $this->ui_fac->messageBox()->info($this->lng->txt("skmg_cont_profiles_info"))->withLinks([$link]);
        } else {
            $box = $this->ui_fac->messageBox()->info($this->lng->txt("skmg_cont_profiles_info_empty"));
        }

        return $this->ui_ren->render($box);
    }

    protected function showInfoBoxForRecords(): string
    {
        $box = $this->ui_fac->messageBox()->info($this->lng->txt("skmg_cont_records_info_empty"));

        return $this->ui_ren->render($box);
    }

    public function setGapAnalysisActualStatusModePerType(string $a_type, string $a_cat_title = ""): void
    {
        $this->gap_mode = "max_per_type";
        $this->gap_mode_type = $a_type;
        $this->gap_cat_title = $a_cat_title;
        $this->mode = "gap";
    }

    public function setGapAnalysisActualStatusModePerObject(int $a_obj_id, string $a_cat_title = ""): void
    {
        $this->gap_mode = "max_per_object";
        $this->gap_mode_obj_id = $a_obj_id;
        $this->gap_cat_title = $a_cat_title;
        $this->mode = "gap";
    }

    public function getGapAnalysisHTML(int $a_user_id = 0, ?array $a_skills = null): string
    {
        $ilUser = $this->user;
        $lng = $this->lng;

        // needed fix for profiles in gap view, because there is no filter shown (yet)
        $this->getFilter()->clear();

        if ($a_skills == null) {
            foreach ($this->getObjectSkills() as $s) {
                $a_skills[] = array(
                    "cont_obj_id" => $s->getContainerObjectId(),
                    "base_skill_id" => $s->getBaseSkillId(),
                    "tref_id" => $s->getTrefId(),
                    "title" => $s->getTitle()
                );
            }
        }

        $intro_html = "";
        if ($this->getIntroText() != "") {
            $pan = ilPanelGUI::getInstance();
            $pan->setPanelStyle(ilPanelGUI::PANEL_STYLE_PRIMARY);
            $pan->setBody($this->getIntroText());
            $intro_html = $pan->getHTML();
        }

        //		$this->setTabs("list_skills");

        if ($a_user_id == 0) {
            $user_id = $ilUser->getId();
        } else {
            $user_id = $a_user_id;
        }

        $skills = [];
        if ($this->getProfileId() > 0) {
            $this->profile_levels = $this->profile_manager->getSkillLevels($this->getProfileId());
            $skills = $this->profile_levels;
        } else {
            // order skills per virtual skill tree
            $vtree = $this->tree_service->getGlobalVirtualSkillTree();
            $a_skills = $vtree->getOrderedNodeset($a_skills, "base_skill_id", "tref_id");

            foreach ($a_skills as $s) {
                /** @var XAxis $x_axis */
                $skills[] = $this->cont_factory_service->containerSkill()->skill(
                    (int) $s["cont_obj_id"],
                    (int) $s["base_skill_id"],
                    (int) $s["tref_id"],
                    $s["title"]
                );
            }
        }

        // get actual levels for gap analysis
        $this->actual_levels = $this->profile_completion_manager->getActualMaxLevels(
            $user_id,
            $skills,
            $this->gap_mode,
            $this->gap_mode_type,
            $this->gap_mode_obj_id
        );
        $this->next_level_fuls = $this->profile_completion_manager->getActualNextLevelFulfilments(
            $user_id,
            $skills,
            $this->gap_mode,
            $this->gap_mode_type,
            $this->gap_mode_obj_id
        );

        $bc_skills = [];
        $html = "";
        $not_all_self_evaluated = false;

        foreach ($skills as $s) {
            if ($this->skmg_settings->getHideProfileBeforeSelfEval() &&
                !ilBasicSkill::hasSelfEvaluated($this->user->getId(), $s->getBaseSkillId(), $s->getTrefId())) {
                $not_all_self_evaluated = true;
            }

            $path = $this->tree_service->getSkillTreePath($s->getBaseSkillId());

            // check draft
            foreach ($path as $p) {
                if ($p["status"] == ilSkillTreeNode::STATUS_DRAFT) {
                    continue(2);
                }
            }
            $bc_skills[] = $s;
            $html .= $this->getSkillHTML($s->getBaseSkillId(), $user_id, false, $s->getTrefId());
        }

        if ($not_all_self_evaluated) {
            $box = $this->ui_fac->messageBox()->info($lng->txt("skmg_skill_needs_self_eval_box"));
            $html = $this->ui_ren->render($box) . $html;
        }

        // output bar charts
        $all_chart_html = $this->getBarChartHTML($bc_skills);

        if (!empty($all_chart_html)) {
            $pan = ilPanelGUI::getInstance();
            $pan->setPanelStyle(ilPanelGUI::PANEL_STYLE_PRIMARY);
            $pan->setBody($all_chart_html);
            $all_chart_html = $pan->getHTML();
        }

        // list skills

        return $intro_html . $all_chart_html . $html;
    }

    /**
     * @param \ILIAS\Skill\GapAnalysisSkill[] $skills
     */
    protected function getBarChartHTML(array $skills): string
    {
        $lng = $this->lng;

        // dimension names
        $target_dim = $lng->txt("skmg_target_level");
        $eval_dim = $lng->txt("skmg_eval_type_1");
        if ($this->gap_cat_title != "") {
            $eval_dim = $this->gap_cat_title;
        } elseif ($this->gap_mode == "max_per_type") {
            $eval_dim = $lng->txt("objs_" . $this->gap_mode_type);
        } elseif ($this->gap_mode == "max_per_object") {
            $eval_dim = ilObject::_lookupTitle($this->gap_mode_obj_id);
        }
        $self_eval_dim = $lng->txt("skmg_self_evaluation");

        $incl_self_eval = false;
        $self_vals = [];
        if (!empty($this->getGapAnalysisSelfEvalLevels())) {
            $incl_self_eval = true;
            $self_vals = $this->getGapAnalysisSelfEvalLevels();
        }

        $chart_counter = 0;
        $bar_counter = 0;
        $tmp_labels = [];
        $all_chart_data = [];
        $render_eval_dim = false;
        foreach ($skills as $l) {
            $bs = new ilBasicSkill($l->getBaseSkillId());
            $levels = $bs->getLevelData();
            // filter out skills with no levels from chart
            if (empty($levels)) {
                continue;
            }

            $cnt = 0;
            $points = [];
            $tooltips = [];
            $labels = [0 => ""];
            foreach ($levels as $lv) {
                // points and tooltips
                $cnt++;
                $labels[] = $lv["title"];
                if ($this->getProfileId() > 0) {
                    if ($l->getLevelId() == $lv["id"]) {
                        $points[$target_dim] = [$cnt - 0.01, $cnt];
                        $tooltips[$target_dim] = $lv["title"];
                    } else {
                        $points[$target_dim] = $points[$target_dim] ?? null;
                        $tooltips[$target_dim] = $tooltips[$target_dim] ?? null;
                    }
                }
                if ($this->actual_levels[$l->getBaseSkillId()][$l->getTrefId()] == $lv["id"]) {
                    $perc = $this->next_level_fuls[$l->getBaseSkillId()][$l->getTrefId()];
                    $points[$eval_dim] = $cnt + $perc;
                    $tooltips[$eval_dim] = null;
                    if ($perc > 0) {
                        $tooltips[$eval_dim] = $lv["title"] . " + " . $perc * 100 . "%";
                    }
                    $render_eval_dim = true;
                } else {
                    $points[$eval_dim] = $points[$eval_dim] ?? null;
                    $tooltips[$eval_dim] = $tooltips[$eval_dim] ?? null;
                    if (!is_null($points[$eval_dim])) {
                        $render_eval_dim = true;
                    }
                }
                if ($incl_self_eval) {
                    if (($self_vals[$l->getBaseSkillId()][$l->getTrefId()] ?? 0) == $lv["id"]) {
                        $points[$self_eval_dim] = $cnt;
                        $tooltips[$self_eval_dim] = null;
                    } else {
                        $points[$self_eval_dim] = $points[$self_eval_dim] ?? null;
                        $tooltips[$self_eval_dim] = $tooltips[$self_eval_dim] ?? null;
                    }
                }
            }

            // do not show eval dimension if there is no data for it
            if (!$render_eval_dim) {
                unset($points[$eval_dim]);
                unset($tooltips[$eval_dim]);
            }

            /*
             * create new chart when number and title of the levels of the current skill are not identical with
             * the previous skill
            */
            if (!empty($tmp_labels) && $tmp_labels !== $labels) {
                $chart_counter++;
                $bar_counter = 0;
            }
            $tmp_labels = $labels;

            $all_chart_data[$chart_counter][$bar_counter]["item_title"] = ilBasicSkill::_lookupTitle(
                $l->getBaseSkillId(),
                $l->getTrefId()
            );
            $all_chart_data[$chart_counter][$bar_counter]["levels"] = $labels;
            $all_chart_data[$chart_counter][$bar_counter]["points"] = $points;
            $all_chart_data[$chart_counter][$bar_counter]["tooltips"] = $tooltips;

            $bar_counter++;
        }

        $all_chart_html = "";
        foreach ($all_chart_data as $chart_data) {
            $c_dimension = $this->data_fac->dimension()->cardinal($chart_data[0]["levels"]);
            $r_dimension = $this->data_fac->dimension()->range($c_dimension);

            // dimensions and bar configs
            $ds = [];
            $bars = [];

            if ($this->getProfileId() > 0) {
                $target_bar = new BarConfig();
                $target_bar = $target_bar->withRelativeWidth(1.1);
                $target_bar = $target_bar->withColor($this->data_fac->color("#333333"));
                $ds[$target_dim] = $r_dimension;
                $bars[$target_dim] = $target_bar;
            }

            if ($render_eval_dim) {
                $eval_bar = new BarConfig();
                $eval_bar = $eval_bar->withRelativeWidth(0.5);
                $eval_bar = $eval_bar->withColor($this->data_fac->color("#307C88"));
                if (ilObject::_lookupType($this->gap_mode_obj_id) == "tst") {
                    $eval_bar = $eval_bar->withColor($this->data_fac->color("#d38000"));
                }
                $ds[$eval_dim] = $c_dimension;
                $bars[$eval_dim] = $eval_bar;
            }

            if ($incl_self_eval) {
                $self_eval_bar = new BarConfig();
                $self_eval_bar = $self_eval_bar->withRelativeWidth(0.5);
                $self_eval_bar = $self_eval_bar->withColor($this->data_fac->color("#557b2e"));
                $ds[$self_eval_dim] = $c_dimension;
                $bars[$self_eval_dim] = $self_eval_bar;
            }

            $dataset = $this->data_fac->dataset($ds);

            $render_chart = false;
            foreach ($chart_data as $a) {
                if ($render_eval_dim && !isset($a["points"][$eval_dim])) {
                    $a["points"][$eval_dim] = null;
                    $a["tooltips"][$eval_dim] = null;
                }
                $dataset = $dataset->withPoint($a["item_title"], $a["points"]);
                $dataset = $dataset->withAlternativeInformation($a["item_title"], $a["tooltips"]);
                foreach ($a["points"] as $dim => $p) {
                    // render chart only if there are bars
                    if (!is_null($p) && $dim != $target_dim) {
                        $render_chart = true;
                    }
                }
            }

            if ($render_chart) {
                $bar_chart = $this->ui_fac->chart()->bar()->horizontal(
                    "",
                    $dataset,
                    $bars
                );

                $x_axis = new XAxis();
                $x_axis = $x_axis->withMaxValue(count($chart_data[0]["levels"]) - 1);
                /** @var XAxis $x_axis */
                $bar_chart = $bar_chart->withCustomXAxis($x_axis);
                $bar_chart = $bar_chart->withTitleVisible(false);

                $all_chart_html .= $this->ui_ren->render($bar_chart);
            }
        }

        return $all_chart_html;
    }

    public function getMaterials(array $a_levels, int $a_tref_id = 0, int $a_user_id = 0): ?\ILIAS\UI\Component\Panel\Sub
    {
        $ilUser = $this->user;
        $lng = $this->lng;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        // only render, if materials given
        $got_mat = false;
        foreach ($a_levels as $v) {
            $mat_cnt = $this->assigned_material_manager->countAssignedMaterials(
                $a_user_id,
                $a_tref_id,
                (int) $v["id"]
            );
            if ($mat_cnt > 0) {
                $got_mat = true;
            }
        }
        if (!$got_mat) {
            return null;
        }

        $item_groups = [];
        foreach ($a_levels as $k => $v) {
            $got_mat = false;
            $items = [];
            foreach ($this->assigned_material_manager->getAssignedMaterials(
                $a_user_id,
                $a_tref_id,
                (int) $v["id"]
            ) as $item) {
                $mat_data = $this->getMaterialInfo($item->getWorkspaceId(), $a_user_id);
                $title = $mat_data[0];
                $icon = $this->ui_fac->symbol()->icon()->standard(
                    ilObject::_lookupType($mat_data[2]),
                    $lng->txt("icon") . " " . $lng->txt(ilObject::_lookupType($mat_data[2]))
                );
                $link = $this->ui_fac->link()->standard($title, $mat_data[1]);
                $items[] = $this->ui_fac->item()->standard($link)->withLeadIcon($icon);
                $got_mat = true;
            }
            if ($got_mat) {
                $item_groups[] = $this->ui_fac->item()->group($v["title"], $items);
            }
        }
        $mat_panel = $this->ui_fac->panel()->sub(
            $lng->txt("skmg_materials"),
            $item_groups
        );

        return $mat_panel;
    }

    public function getProfileTargetItem(int $a_profile_id, array $a_levels, int $a_tref_id = 0): string
    {
        $lng = $this->lng;

        $profile_levels = $this->profile_manager->getSkillLevels($a_profile_id);

        $a_activated_levels = [];

        foreach ($a_levels as $k => $v) {
            foreach ($profile_levels as $pl) {
                if ($pl->getLevelId() == $v["id"] &&
                    $pl->getBaseSkillId() == $v["skill_id"] &&
                    $a_tref_id == $pl->getTrefId()) {
                    $a_activated_levels[] = $pl->getLevelId();
                }
            }
        }

        $tpl = new ilTemplate("tpl.skill_eval_item.html", true, true, "Services/Skill");
        $tpl->setVariable("SCALE_BAR", $this->getScaleBar($a_levels, $a_activated_levels));

        $tpl->setVariable("TYPE", $lng->txt("skmg_target_level"));
        $tpl->setVariable("TITLE", "");

        return $tpl->get();
    }

    public function getActualGapItem(array $a_levels, int $a_tref_id = 0): string
    {
        $lng = $this->lng;

        $a_activated_levels = [];
        foreach ($a_levels as $k => $v) {
            if ($this->actual_levels[$v["skill_id"]][$a_tref_id] == $v["id"]) {
                $a_activated_levels[] = $v["id"];
            }
        }

        if (empty($a_activated_levels)) {
            return "";
        }

        $title = "";
        if ($this->gap_cat_title != "") {
            $title = $this->gap_cat_title;
        } elseif ($this->gap_mode == "max_per_type") {
            $title = $lng->txt("objs_" . $this->gap_mode_type);
        } elseif ($this->gap_mode == "max_per_object") {
            $title = ilObject::_lookupTitle($this->gap_mode_obj_id);
        }

        $tpl = new ilTemplate("tpl.skill_eval_item.html", true, true, "Services/Skill");
        $tpl->setVariable("SCALE_BAR", $this->getScaleBar($a_levels, $a_activated_levels));

        $type = 1;
        $tpl->setVariable("TYPE", $lng->txt("skmg_eval_type_latest_" . $type));
        if ($type > 0) {
            $tpl->touchBlock("st" . $type);
            $tpl->touchBlock("stb" . $type);
        }

        if ($title != $lng->txt("skmg_eval_type_" . $type)) {
            $tpl->setVariable("TITLE", $title);
        }

        return $tpl->get();
    }

    public function getSelfEvalGapItem(array $a_levels, int $a_tref_id = 0): string
    {
        $lng = $this->lng;

        $self_vals = $this->getGapAnalysisSelfEvalLevels();
        if (empty($self_vals)) {
            return "";
        }

        $a_activated_levels = [];
        foreach ($a_levels as $k => $v) {
            if (isset($self_vals[$v["skill_id"]][$a_tref_id]) &&
                $self_vals[$v["skill_id"]][$a_tref_id] == $v["id"]) {
                $a_activated_levels[] = $v["id"];
            }
        }

        if (empty($a_activated_levels)) {
            return "";
        }

        $tpl = new ilTemplate("tpl.skill_eval_item.html", true, true, "Services/Skill");
        $tpl->setVariable("SCALE_BAR", $this->getScaleBar($a_levels, $a_activated_levels));

        $type = 3;
        $tpl->setVariable("TYPE", $lng->txt("skmg_eval_type_latest_" . $type));
        if ($type > 0) {
            $tpl->touchBlock("st" . $type);
            $tpl->touchBlock("stb" . $type);
        }

        return $tpl->get();
    }

    /**
     * @param array $a_levels
     * @param array|string $a_activated_levels
     * @return string
     */
    public function getScaleBar(array $a_levels, $a_activated_levels): string
    {
        $vals = [];

        if (!is_array($a_activated_levels)) {
            $a_activated_levels = array($a_activated_levels);
        }

        foreach ($a_levels as $level) {
            $vals[$level["title"]] = (in_array($level["id"], $a_activated_levels));
        }
        $scale_bar = $this->ui_fac->chart()->scaleBar($vals);

        return $this->ui_ren->render($scale_bar);
    }

    public function getEvalItem(array $a_levels, array $a_level_entry, bool $is_latest = false): string
    {
        $lng = $this->lng;
        $ilAccess = $this->access;

        $tpl = new ilTemplate("tpl.skill_eval_item.html", true, true, "Services/Skill");
        $tpl->setVariable("SCALE_BAR", $this->getScaleBar($a_levels, $a_level_entry["level_id"]));

        $type = Personal\SkillEval::TYPE_APPRAISAL;

        if ($a_level_entry["self_eval"] == 1) {
            $type = Personal\SkillEval::TYPE_SELF_EVAL;
        }

        if ($a_level_entry["trigger_obj_type"] == "tst") {
            $type = Personal\SkillEval::TYPE_MEASUREMENT;
        }

        ilDatePresentation::setUseRelativeDates(false);
        $title = "";
        if ($a_level_entry["trigger_obj_id"] > 0) {
            if (ilObject::_exists($a_level_entry["trigger_ref_id"], true)) {
                $title = ilObject::_lookupTitle($a_level_entry["trigger_obj_id"]);
            } elseif (!empty($del_data = ilObjectDataDeletionLog::get($a_level_entry["trigger_obj_id"]))) {
                $title = $del_data["title"];
            } else {
                $title = ($a_level_entry["trigger_title"]) ?? "";
            }
        }

        if ($a_level_entry["trigger_ref_id"] > 0
            && $ilAccess->checkAccess("read", "", $a_level_entry["trigger_ref_id"])) {
            $title = "<a href='" . ilLink::_getLink($a_level_entry["trigger_ref_id"]) . "'>" . $title . "</a>";
        }

        if ($is_latest) {
            $tpl->setVariable("TYPE", $lng->txt("skmg_eval_type_latest_" . $type));
        } else {
            $tpl->setVariable("TYPE", $lng->txt("skmg_eval_type_" . $type));
        }
        if ($type > 0) {
            $tpl->touchBlock("st" . $type);
            $tpl->touchBlock("stb" . $type);
        }
        $tpl->setVariable("TITLE", $title);
        $tpl->setVariable(
            "DATE",
            ilDatePresentation::formatDate(new ilDate($a_level_entry["status_date"], IL_CAL_DATETIME))
        );

        ilDatePresentation::setUseRelativeDates(true);

        return $tpl->get();
    }

    protected function getLatestEntriesForSkillHTML(
        int $top_skill_id,
        array $bs,
        ilSkillTreeNode $skill,
        ilObjUser $user,
        int $eval_type,
        array $level_data
    ): string {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.skill_entries_latest.html", true, true, "Services/Skill");

        $user_entries = $skill->getAllHistoricLevelEntriesOfUser($bs["tref"], $user->getId(), $eval_type);
        $user_entries_filtered = $this->getFilteredEntriesForSkill(
            $user_entries,
            $top_skill_id,
            $bs,
            $user
        );
        if ($eval_type == ilBasicSkill::EVAL_BY_SELF) {
            $latest_entries = $this->getSelfEvalEntriesLatestOnly($user_entries_filtered);
        } else {
            $latest_entries = $this->getAllEntriesLatestOnly($user_entries_filtered);
        }

        $latest_entries_html = "";
        foreach ($latest_entries as $entry) {
            $latest_entries_html .= $this->ui_ren->render(
                $this->ui_fac->legacy($this->getEvalItem($level_data, $entry, true))
            );
        }

        if (!empty($latest_entries_html)) {
            $tpl->setVariable("SKILL_ENTRIES", $latest_entries_html);

            if (count($user_entries_filtered) != count($latest_entries)) {
                $tpl->setCurrentBlock("all_entries_button");
                $show_all_button = $this->ui_fac->button()->standard($lng->txt("skmg_show_all"), "#")
                                                ->withOnLoadCode(function ($id) {
                                                    return "$('#$id').on('click', function() {SkillEntries.showNonLatest($id); return false;})";
                                                });
                $tpl->setVariable("BUTTON", $this->ui_ren->render($show_all_button));
                $tpl->parseCurrentBlock();
            }

            return $tpl->get();
        }

        return "";
    }

    protected function getNonLatestEntriesForSkillHTML(
        int $top_skill_id,
        array $bs,
        ilSkillTreeNode $skill,
        ilObjUser $user,
        int $eval_type,
        array $level_data
    ): string {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.skill_entries_non_latest.html", true, true, "Services/Skill");

        $user_entries = $skill->getAllHistoricLevelEntriesOfUser($bs["tref"], $user->getId(), $eval_type);
        $user_entries_filtered = $this->getFilteredEntriesForSkill(
            $user_entries,
            $top_skill_id,
            $bs,
            $user
        );
        if ($eval_type == ilBasicSkill::EVAL_BY_SELF) {
            $non_latest_entries = $this->getSelfEvalEntriesWithoutLatest($user_entries_filtered);
        } else {
            $non_latest_entries = $this->getAllEntriesWithoutLatest($user_entries_filtered);
        }

        $non_latest_entries_filtered_html = "";
        foreach ($non_latest_entries as $entry) {
            $non_latest_entries_filtered_html .= $this->ui_ren->render(
                $this->ui_fac->legacy($this->getEvalItem($level_data, $entry, false))
            );
        }

        if (!empty($non_latest_entries_filtered_html)) {
            $tpl->setVariable("SKILL_ENTRIES", $non_latest_entries_filtered_html);

            $show_latest_button = $this->ui_fac->button()->standard($lng->txt("skmg_show_latest_entries"), "#")
                                               ->withOnLoadCode(function ($id) {
                                                   return "$('#$id').on('click', function() {SkillEntries.hideNonLatest($id); return false;})";
                                               });
            $tpl->setVariable("BUTTON", $this->ui_ren->render($show_latest_button));

            return $tpl->get();
        }

        return "";
    }

    protected function getFilteredEntriesForSkill(
        array $entries,
        int $top_skill_id,
        array $bs,
        ilObjUser $user
    ): array {
        // get date of self evaluation
        $se_date = $this->self_evaluation_manager->getSelfEvaluationDate($user->getId(), $top_skill_id, $bs["tref"], $bs["id"]);
        $se_rendered = $se_date == "";

        $filtered_entries = [];
        foreach ($entries as $level_entry) {
            if (count($this->getTriggerObjectsFilter()) && !in_array($level_entry['trigger_obj_id'], $this->getTriggerObjectsFilter())) {
                continue;
            }
            if (count($this->getTriggerUserFilter()) && !in_array($level_entry['trigger_user_id'], $this->getTriggerUserFilter())) {
                continue;
            }

            // render the self evaluation at the correct position within the list of object triggered entries
            if ($se_date > $level_entry["status_date"] && !$se_rendered) {
                $se_rendered = true;
            }
            if ($this->getFilter()->isInRange($level_entry)) {
                $filtered_entries[] = $level_entry;
            }
        }

        return $filtered_entries;
    }

    protected function getSelfEvalEntriesLatestOnly(array $entries): array
    {
        if (!empty($entries)) {
            $last_entry[] = $entries[0];
            return $last_entry;
        }

        return [];
    }

    protected function getSelfEvalEntriesWithoutLatest(array $entries): array
    {
        if (count($entries) > 1) {
            array_shift($entries);
            return $entries;
        }

        return [];
    }

    protected function getAllEntriesLatestOnly(array $entries): array
    {
        $first_self_added = false;
        $first_measurement_added = false;
        $first_appraisal_added = false;
        $latest_entries = [];
        foreach ($entries as $entry) {
            if (!$first_self_added && $entry["self_eval"] == 1) {
                $latest_entries[] = $entry;
                $first_self_added = true;
                continue;
            }
            if (!$first_measurement_added && $entry["trigger_obj_type"] == "tst") {
                $latest_entries[] = $entry;
                $first_measurement_added = true;
                continue;
            }
            if (!$first_appraisal_added && $entry["self_eval"] != 1 && $entry["trigger_obj_type"] != "tst") {
                $latest_entries[] = $entry;
                $first_appraisal_added = true;
            }
        }

        return $latest_entries;
    }

    protected function getAllEntriesWithoutLatest(array $entries): array
    {
        $first_self_filtered = false;
        $first_measurement_filtered = false;
        $first_appraisal_filtered = false;
        $non_latest_entries = [];
        foreach ($entries as $entry) {
            if (!$first_self_filtered && $entry["self_eval"] == 1) {
                $first_self_filtered = true;
                continue;
            }
            if (!$first_measurement_filtered && $entry["trigger_obj_type"] == "tst") {
                $first_measurement_filtered = true;
                continue;
            }
            if (!$first_appraisal_filtered && $entry["self_eval"] != 1 && $entry["trigger_obj_type"] != "tst") {
                $first_appraisal_filtered = true;
                continue;
            }
            $non_latest_entries[] = $entry;
        }

        return $non_latest_entries;
    }

    protected function getSkillEntriesHeader(int $eval_type): string
    {
        $tpl = new ilTemplate("tpl.skill_entries_header.html", true, true, "Services/Skill");

        if ($eval_type == ilBasicSkill::EVAL_BY_SELF) {
            $tpl->setVariable("HEADING", $this->lng->txt("skmg_self_evaluation"));
            $tpl->setCurrentBlock("header_byline");
            $tpl->setVariable("BYLINE", $this->lng->txt("skmg_self_evaluation_byline"));
            $tpl->parseCurrentBlock();
        } else {
            if ($this->getProfileId() > 0) {
                $tpl->setVariable("HEADING", $this->lng->txt("skmg_skill_profile_records"));
            }
        }

        return $tpl->get();
    }

    protected function getSkillCategoryDescription(int $skill_id, int $tref_id): string
    {
        $tpl = new ilTemplate("tpl.skill_description_category.html", true, true, "Services/Skill");

        //if (ilSkillTreeNode::_lookupType($skill_id) == "scat") {
        $des = ilSkillTreeNode::_lookupDescription($skill_id);
        if (!empty($des)) {
            $tpl->setCurrentBlock("description_category");
            $tpl->setVariable("DESCRIPTION_CATEGORY", $des);
            $tpl->parseCurrentBlock();
        }
        //}

        return $tpl->get();
    }

    protected function getBasicSkillDescription(string $description): string
    {
        $tpl = new ilTemplate("tpl.skill_description_basic.html", true, true, "Services/Skill");

        if (!empty($description)) {
            $tpl->setCurrentBlock("description_basic");
            $tpl->setVariable("DESCRIPTION_BASIC", $description);
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    public function getSkillLevelDescription(ilSkillTreeNode $skill): string
    {
        $level_data = $skill->getLevelData();
        $tpl = new ilTemplate("tpl.skill_desc.html", true, true, "Services/Skill");

        $desc_exists = false;
        foreach ($level_data as $l) {
            if ($l["description"] != "") {
                $desc_exists = true;
            }
        }
        reset($level_data);
        if ($desc_exists) {
            foreach ($level_data as $l) {
                $tpl->setCurrentBlock("level");
                $tpl->setVariable("LEVEL_VAL", $l["title"]);
                $tpl->setVariable("LEVEL_DESC", nl2br($l["description"]));
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }

    public function getSuggestedResourcesForProfile(
        array $a_levels,
        int $a_base_skill,
        int $a_tref_id,
        int $gap_mode_obj_id = 0
    ): ?\ILIAS\UI\Component\Panel\Secondary\Secondary {
        $lng = $this->lng;

        $gap_mode_obj_type = ilObject::_lookupType($gap_mode_obj_id);
        if ($gap_mode_obj_id > 0 && !$this->obj_definition->isContainer($gap_mode_obj_type)) {
            return null;
        }

        // note for self-evaluation
        if ($this->skmg_settings->getHideProfileBeforeSelfEval() &&
            !ilBasicSkill::hasSelfEvaluated($this->user->getId(), $a_base_skill, $a_tref_id)) {
            $sec_panel_content = $this->ui_fac->legacy($lng->txt("skmg_skill_needs_self_eval"));
            $sec_panel = $this->ui_fac->panel()->secondary()->legacy("", $sec_panel_content);
            return $sec_panel;
        }

        // suggested resources
        if ($this->resource_manager->isLevelTooLow($a_tref_id, $a_levels, $this->profile_levels, $this->actual_levels)) {
            $imp_resources = $this->resource_manager->getSuggestedResources(
                $a_base_skill,
                $a_tref_id,
                $a_levels,
                $this->profile_levels
            );
            $info = $this->ui_fac->item()->standard($lng->txt("skmg_recommended_learning_material_info"));
            $item_groups[] = $this->ui_fac->item()->group("", [$info]);

            $at_least_one_item = false;
            $highlighted_level = false;

            $sub_objects = [];
            $is_container = false;
            if ($gap_mode_obj_id > 0 && $this->obj_definition->isContainer($gap_mode_obj_type)) {
                $is_container = true;
                $sub_objects = $this->tree->getSubTree(
                    $this->tree->getNodeData((int) current(\ilObject::_getAllReferences($gap_mode_obj_id))),
                    false
                );
            }

            foreach ($imp_resources as $order_level_id => $resources) {
                $level_id = (int) substr(strrchr($order_level_id, '_'), 1);
                // do not show level if already reached
                if ($level_id <= $this->actual_levels[$a_base_skill][$a_tref_id]) {
                    continue;
                }
                if ($level_id === $this->resource_manager->determineCurrentTargetLevel($a_levels, $this->profile_levels)) {
                    $highlighted_level = true;
                }
                $level_title = $this->level_repo->lookupLevelTitle($level_id);
                $items = [];
                foreach ($resources as $r) {
                    $ref_id = $r->getRepoRefId();
                    // in containers: filter resources only by objects in sub tree
                    if ($is_container && !in_array($ref_id, $sub_objects)) {
                        continue;
                    }
                    $obj_id = ilObject::_lookupObjId($ref_id);
                    $title = ilObject::_lookupTitle($obj_id);
                    $icon = $this->ui_fac->symbol()->icon()->standard(
                        ilObject::_lookupType($obj_id),
                        $lng->txt("icon") . " " . $lng->txt(ilObject::_lookupType($obj_id))
                    );
                    $link = $this->ui_fac->link()->standard($title, ilLink::_getLink($ref_id));
                    $items[] = $this->ui_fac->item()->standard($link)->withLeadIcon($icon);
                    $at_least_one_item = true;
                }
                $item_groups[] = $this->ui_fac->item()->group(
                    $highlighted_level
                        ? "<strong>" . $level_title . " (" . $lng->txt("skmg_target_level") . ")</strong>"
                        : $level_title,
                    $items
                );
            }
            if ($at_least_one_item) {
                switch ($gap_mode_obj_type) {
                    case "crs":
                        $sec_panel_title = $lng->txt("skmg_recommended_learning_material_crs");
                        break;
                    case "grp":
                        $sec_panel_title = $lng->txt("skmg_recommended_learning_material_grp");
                        break;
                    default:
                        $sec_panel_title = $lng->txt("skmg_recommended_learning_material_global");
                }

                $sec_panel = $this->ui_fac->panel()->secondary()->listing(
                    $sec_panel_title,
                    $item_groups
                );
            } else {
                $sec_panel_content = $this->ui_fac->legacy($lng->txt("skmg_skill_needs_impr_no_res"));
                $sec_panel = $this->ui_fac->panel()->secondary()->legacy("", $sec_panel_content);
            }
        } else {
            $sec_panel_content = $this->ui_fac->legacy($lng->txt("skmg_skill_no_needs_impr_info"));
            $sec_panel = $this->ui_fac->panel()->secondary()->legacy($lng->txt("skmg_skill_no_needs_impr"), $sec_panel_content);
        }

        return $sec_panel;
    }

    public function getAllSuggestedResources(
        int $a_base_skill,
        int $a_tref_id
    ): ?\ILIAS\UI\Component\Panel\Secondary\Secondary {
        $lng = $this->lng;

        $res = $this->resource_manager->getResources($a_base_skill, $a_tref_id);
        $any = false;
        $item_groups = [];
        foreach ($res as $level) {
            $available = false;
            $cl = 0;
            $items = [];
            foreach ($level as $r) {
                if ($r->getImparting()) {
                    $ref_id = $r->getRepoRefId();
                    $obj_id = ilObject::_lookupObjId($ref_id);
                    $title = ilObject::_lookupTitle($obj_id);
                    $icon = $this->ui_fac->symbol()->icon()->standard(
                        ilObject::_lookupType($obj_id),
                        $lng->txt("icon") . " " . $lng->txt(ilObject::_lookupType($obj_id))
                    );
                    $link = $this->ui_fac->link()->standard($title, ilLink::_getLink($ref_id));
                    $items[] = $this->ui_fac->item()->standard($link)->withLeadIcon($icon);
                    $available = true;
                    $any = true;
                    $cl = $r->getLevelId();
                }
            }
            if ($available) {
                $item_groups[] = $this->ui_fac->item()->group(ilBasicSkill::lookupLevelTitle($cl), $items);
            }
        }
        if ($any) {
            $sec_panel = $this->ui_fac->panel()->secondary()->listing(
                $lng->txt("skmg_suggested_resources"),
                $item_groups
            );
            return $sec_panel;
        }

        return null;
    }

    public function listAllAssignedProfiles(): void
    {
        if (empty($this->user_profiles)) {
            $this->ctrl->redirect($this, "listSkills");
        }

        $this->setTabs("profile");

        $prof_list = $this->getProfilesListed($this->user_profiles);

        $this->tpl->setContent($this->ui_ren->render($prof_list));
    }

    public function listAssignedProfile(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $main_tpl = $this->tpl;

        $tpl = new ilTemplate("tpl.skill_filter.html", true, true, "Services/Skill");

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listallassignedprofiles")
        );
        $this->setProfileId($this->requested_profile_id);

        $main_tpl->setTitle($this->profile_manager->lookupTitle($this->getProfileId()));

        $filter_toolbar = new ilToolbarGUI();
        $filter_toolbar->setFormAction($ilCtrl->getFormAction($this));
        $this->getFilter()->addToToolbar($filter_toolbar, true);

        $skills = [];
        if ($this->getProfileId() > 0) {
            $this->profile_levels = $this->profile_manager->getSkillLevels($this->getProfileId());
            $skills = $this->profile_levels;
        }

        $this->actual_levels = $this->profile_completion_manager->getActualMaxLevels(
            $this->user->getId(),
            $skills,
            $this->gap_mode,
            $this->gap_mode_type,
            $this->gap_mode_obj_id
        );

        // render
        $html = "";
        $not_all_self_evaluated = false;
        foreach ($skills as $s) {
            if ($this->skmg_settings->getHideProfileBeforeSelfEval() &&
                !ilBasicSkill::hasSelfEvaluated($this->user->getId(), $s->getBaseSkillId(), $s->getTrefId())) {
                $not_all_self_evaluated = true;
            }

            // todo draft check
            $html .= $this->getSkillHTML($s->getBaseSkillId(), 0, true, $s->getTrefId());
        }

        if ($html != "") {
            $filter_toolbar->addFormButton($this->lng->txt("skmg_refresh_view"), "applyFilterAssignedProfiles");

            $tpl->setVariable("FILTER", $filter_toolbar->getHTML());

            $html = $tpl->get() . $html;
        }

        if ($not_all_self_evaluated) {
            $box = $this->ui_fac->messageBox()->info($lng->txt("skmg_skill_needs_self_eval_box"));
            $html = $this->ui_ren->render($box) . $html;
        }

        $main_tpl->setContent($html);
    }
}
