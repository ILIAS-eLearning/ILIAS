<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilPersonalSkill.php");
include_once("./Services/Skill/classes/class.ilSkillProfile.php");

/**
 * Personal skills GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPersonalSkillsGUI:
 *
 * @ingroup ServicesSkill
 */
class ilPersonalSkillsGUI
{
    const LIST_SELECTED = "";
    const LIST_PROFILES = "profiles";

    protected $offline_mode;
    protected $skill_tree;
    public static $skill_tt_cnt = 1;
    protected $actual_levels = array();
    protected $gap_self_eval_levels = array();
    protected $mode = "";
    protected $history_view = false;
    protected $trigger_objects_filter = array();
    protected $intro_text = "";
    protected $hidden_skills = array();

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var mixed
     */
    protected $help;

    /**
     * @var mixed
     */
    protected $setting;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var mixed
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_fac;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_ren;
    protected $obj_id = 0;
    protected $obj_skills = array();

    /**
     * @var ilPersonalSkillsFilterGUI
     */
    protected $filter;

    /**
     * @var string
     */
    protected $list_mode = self::LIST_SELECTED;

    /**
     * Contructor
     *
     * @access public
     */
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

        $this->list_mode = $_GET["list_mode"];

        $this->user_profiles = ilSkillProfile::getProfilesOfUser($this->user->getId());

        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $this->skill_tree = new ilSkillTree();
        
        $this->use_materials = !$ilSetting->get("disable_personal_workspace");

        include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
        $this->skmg_settings = new ilSkillManagementSettings();

        $this->filter = new ilPersonalSkillsFilterGUI();
    }

    /**
     * Get filter
     *
     * @return ilPersonalSkillsFilterGUI
     */
    protected function getFilter()
    {
        return $this->filter;
    }


    /**
     * Set profile id
     *
     * @param  $a_val
     */
    public function setProfileId($a_val)
    {
        $this->profile_id = $a_val;
    }
    
    /**
     * Get profile id
     *
     * @return
     */
    public function getProfileId()
    {
        return $this->profile_id;
    }
    
    /**
     * Set self evaluation levels for gap analysis
     *
     * @param array $a_val self evaluation values key1: base_skill_id, key2: tref_id: value: level id
     */
    public function setGapAnalysisSelfEvalLevels(array $a_val)
    {
        $this->gap_self_eval_levels = $a_val;
    }
    
    /**
     * Get self evaluation levels for gap analysis
     *
     * @return array self evaluation values key1: base_skill_id, key2: tref_id: value: level id
     */
    public function getGapAnalysisSelfEvalLevels()
    {
        return $this->gap_self_eval_levels;
    }
    
    /**
     * Set history view
     *
     * @param bool $a_val history view
     */
    public function setHistoryView($a_val)
    {
        $this->history_view = $a_val;
    }
    
    /**
     * Get history view
     *
     * @return bool history view
     */
    public function getHistoryView()
    {
        return $this->history_view;
    }
    
    /**
     * @return array
     */
    public function getTriggerObjectsFilter()
    {
        return $this->trigger_objects_filter;
    }
    
    /**
     * @param array $trigger_objects_filter
     */
    public function setTriggerObjectsFilter($trigger_objects_filter)
    {
        $this->trigger_objects_filter = $trigger_objects_filter;
    }
    
    /**
     * Set intro text
     *
     * @param string $a_val intro text html
     */
    public function setIntroText($a_val)
    {
        $this->intro_text = $a_val;
    }
    
    /**
     * Get intro text
     *
     * @return string intro text html
     */
    public function getIntroText()
    {
        return $this->intro_text;
    }

    /**
     * Hide skill
     *
     * @param
     * @return
     */
    public function hideSkill($a_skill_id, $a_tref_id = 0)
    {
        $this->hidden_skills[] = $a_skill_id . ":" . $a_tref_id;
    }

    /**
     * Determine current profile id
     *
     * @param
     * @return
     */
    public function determineCurrentProfile()
    {
        $ilCtrl = $this->ctrl;

        if (count($this->user_profiles) == 0) {
            return;
        }
        $current_prof_id = 0;
        if ((int) $_GET["profile_id"] > 0) {
            foreach ($this->user_profiles as $p) {
                if ($p["id"] == (int) $_GET["profile_id"]) {
                    $current_prof_id = (int) $_GET["profile_id"];
                }
            }
        }

        if ($current_prof_id == 0 && !(is_array($this->obj_skills) && $this->obj_id > 0)) {
            $current_prof_id = $this->user_profiles[0]["id"];
        }
        $ilCtrl->setParameter($this, "profile_id", $current_prof_id);
        $this->setProfileId($current_prof_id);
    }

    /**
     * Set object skills
     *
     * @param int $a_obj_id object id
     * @param array $a_skills skills array
     */
    public function setObjectSkills($a_obj_id, $a_skills = null)
    {
        $this->obj_id = $a_obj_id;
        $this->obj_skills = $a_skills;
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $next_class = $ilCtrl->getNextClass($this);
        

        // determin standard command
        $std_cmd = "listSkills";

        $cmd = $ilCtrl->getCmd($std_cmd);
        
        //$tpl->setTitle($lng->txt("skills"));
        //$tpl->setTitleIcon(ilUtil::getImagePath("icon_skmg.svg"));

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Set tabs
     */
    public function setTabs($a_activate)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        // list skills
        $ilCtrl->setParameter($this, "list_mode", self::LIST_SELECTED);
        $ilTabs->addSubTab(
            "list_skills",
            $lng->txt("skmg_selected_skills"),
            $ilCtrl->getLinkTarget($this, "render")
        );

        if (count($this->user_profiles) > 0) {
            $ilCtrl->setParameter($this, "list_mode", self::LIST_PROFILES);
            $ilTabs->addSubTab(
                "profile",
                $lng->txt("skmg_assigned_profiles"),
                $ilCtrl->getLinkTarget($this, "render")
            );
        }

        $ilCtrl->clearParameterByClass(get_class($this), "list_mode");

        // assign materials

        $ilTabs->activateSubTab($a_activate);
    }
    
    public function setOfflineMode($a_file_path)
    {
        $this->offline_mode = $a_file_path;
    }

    /**
     * Render
     */
    protected function render()
    {
        switch ($this->list_mode) {
            case self::LIST_PROFILES:
                $this->listAssignedProfile();
                break;

            default:
                $this->listSkills();
                break;
        }
    }


    /**
     * List skills
     */
    public function listSkills()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;
        $main_tpl = $this->tpl;
        $ilToolbar = $this->toolbar;

        $tpl = new ilTemplate("tpl.skill_filter.html", true, true, "Services/Skill");

        $this->setTabs("list_skills");
        
        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $stree = new ilSkillTree();
        
        // skill selection / add new personal skill
        $ilToolbar->addFormButton(
            $lng->txt("skmg_add_skill"),
            "listSkillsForAdd"
        );
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));

        $filter_toolbar = new ilToolbarGUI();
        $filter_toolbar->setFormAction($ilCtrl->getFormAction($this));
        $this->getFilter()->addToToolbar($filter_toolbar, false);
            
        $skills = ilPersonalSkill::getSelectedUserSkills($ilUser->getId());
        $html = "";
        foreach ($skills as $s) {
            $path = $stree->getSkillTreePath($s["skill_node_id"]);

            // check draft
            foreach ($path as $p) {
                if ($p["status"] == ilSkillTreeNode::STATUS_DRAFT) {
                    continue(2);
                }
            }
            $html.= $this->getSkillHTML($s["skill_node_id"], 0, true);
        }
        
        // list skills
        //		include_once("./Services/Skill/classes/class.ilPersonalSkillTableGUI.php");
        //		$sktab = new ilPersonalSkillTableGUI($this, "listSkills");

        if ($html != "") {
            $filter_toolbar->addFormButton($this->lng->txt("skmg_refresh_view"), "applyFilter");
            $tpl->setVariable("FILTER", $filter_toolbar->getHTML());
            $html = $tpl->get() . $html;
        }

        $main_tpl->setContent($html);
    }

    /**
     * Apply filter
     */
    protected function applyFilter()
    {
        $this->getFilter()->save();
        $this->ctrl->redirect($this, "listSkills");
    }

    /**
     * Apply filter for profiles view
     */
    protected function applyFilterAssignedProfiles()
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
    public function getSkillHTML($a_top_skill_id, $a_user_id = 0, $a_edit = false, $a_tref_id = 0)
    {
        // user interface plugin slot + default rendering
        include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
        $uip = new ilUIHookProcessor(
            "Services/Skill",
            "personal_skill_html",
            array("personal_skills_gui" => $this, "top_skill_id" => $a_top_skill_id, "user_id" => $a_user_id,
                "edit" => $a_edit, "tref_id" => $a_tref_id)
        );
        if (!$uip->replaced()) {
            $skill_html = $this->renderSkillHTML($a_top_skill_id, $a_user_id, $a_edit, $a_tref_id);
        }
        $skill_html = $uip->getHTML($skill_html);

        return $skill_html;
    }

    /**
     * Render skill html
     *
     * @param
     * @return
     */
    public function renderSkillHTML($a_top_skill_id, $a_user_id = 0, $a_edit = false, $a_tref_id = 0)
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        $sub_panels = array();

        if ($a_user_id == 0) {
            $user = $ilUser;
        } else {
            $user = new ilObjUser($a_user_id);
        }

        $tpl = new ilTemplate("tpl.skill_pres.html", true, true, "Services/Skill");
        
        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $stree = new ilSkillTree();

        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        $tref_id = $a_tref_id;
        $skill_id = $a_top_skill_id;
        if (ilSkillTreeNode::_lookupType($a_top_skill_id) == "sktr") {
            include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
            $tref_id = $a_top_skill_id;
            $skill_id = ilSkillTemplateReference::_lookupTemplateId($a_top_skill_id);
        }
        $b_skills = $vtree->getSubTreeForCSkillId($skill_id . ":" . $tref_id, true);

        foreach ($b_skills as $bs) {
            $bs["id"] = $bs["skill_id"];
            $bs["tref"] = $bs["tref_id"];

            $path = $stree->getSkillTreePath($bs["id"], $bs["tref"]);

            $panel_comps = array();


            // check draft
            foreach ($path as $p) {
                if ($p["status"] == ilSkillTreeNode::STATUS_DRAFT) {
                    continue(2);
                }
            }
            reset($path);
        
            $skill = ilSkillTreeNodeFactory::getInstance($bs["id"]);
            $level_data = $skill->getLevelData();


            // skill description
            $panel_comps[] = $this->ui_fac->legacy($this->getSkillDescription($skill));


            if ($this->getProfileId() > 0) {
                if (!$this->skmg_settings->getHideProfileBeforeSelfEval() ||
                    ilBasicSkill::hasSelfEvaluated($user->getId(), $bs["id"], $bs["tref"])) {
                    if ($this->getFilter()->showTargetLevel()) {
                        $panel_comps[] = $this->ui_fac->legacy($this->getProfileTargetItem($this->getProfileId(), $level_data, $bs["tref"]));
                    }
                }
            }

            if ($this->mode == "gap" && !$this->history_view) {
                $panel_comps[] = $this->ui_fac->legacy($this->getActualGapItem($level_data, $bs["tref"]) . "");
                $panel_comps[] = $this->ui_fac->legacy($this->getSelfEvalGapItem($level_data, $bs["tref"]) . "");
            } else {
                // get date of self evaluation
                $se_date = ilPersonalSkill::getSelfEvaluationDate($user->getId(), $a_top_skill_id, $bs["tref"], $bs["id"]);
                $se_rendered = ($se_date == "")
                    ? true
                    : false;
                    
                // get all object triggered entries and render them
                foreach ($skill->getAllHistoricLevelEntriesOfUser($bs["tref"], $user->getId(), ilBasicSkill::EVAL_BY_ALL) as $level_entry) {
                    if (count($this->getTriggerObjectsFilter()) && !in_array($level_entry['trigger_obj_id'], $this->getTriggerObjectsFilter())) {
                        continue;
                    }
                    
                    // render the self evaluation at the correct position within the list of object triggered entries
                    if ($se_date > $level_entry["status_date"] && !$se_rendered) {
                        $se_rendered = true;
                    }
                    if ($this->getFilter()->isInRange($level_data, $level_entry)) {
                        $panel_comps[] = $this->ui_fac->legacy($this->getEvalItem($level_data, $level_entry));
                    }
                }
            }

            // materials (new)
            if ($this->mode != "gap") {
                if ($this->getFilter()->showMaterialsRessources() && $this->use_materials) {
                    $mat = $this->getMaterials($level_data, $bs["tref"], $user->getId());
                }
                if ($mat != "") {
                    $panel_comps[] = $this->ui_fac->legacy($mat);
                }
            }

            // suggested resources
            if ($this->getFilter()->showMaterialsRessources()) {
                $sugg = $this->getSuggestedResources($this->getProfileId(), $level_data, $bs["id"], $bs["tref"]);
            }
            if ($sugg != "") {
                $panel_comps[] = $this->ui_fac->legacy($sugg);
            }

            $title = $sep = "";
            $found = false;
            foreach ($path as $p) {
                if ($found) {
                    $title.= $sep . $p["title"];
                    $sep = " > ";
                }
                if ($a_top_skill_id == $p["child"]) {
                    $found = true;
                }
            }

            $sub = $this->ui_fac->panel()->sub((string) $title, $panel_comps);
            if ($a_edit) {
                $actions = array();
                $ilCtrl->setParameterByClass("ilpersonalskillsgui", "skill_id", $a_top_skill_id);
                $ilCtrl->setParameterByClass("ilpersonalskillsgui", "tref_id", $bs["tref"]);
                $ilCtrl->setParameterByClass("ilpersonalskillsgui", "basic_skill_id", $bs["id"]);
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
            
            $tpl->parseCurrentBlock();
        }
        
        $panel = $this->ui_fac->panel()->standard(
            (string) ilSkillTreeNode::_lookupTitle($skill_id, $tref_id),
            $sub_panels
        );

        if ($a_edit && $this->getProfileId() == 0) {
            $actions = array();

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
     *
     * @param int $a_wsp_id
     * @return array caption, url
     */
    public function getMaterialInfo($a_wsp_id, $a_user_id)
    {
        if (!$this->ws_tree) {
            include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
            include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
            $this->ws_tree = new ilWorkspaceTree($a_user_id);
            $this->ws_access = new ilWorkspaceAccessHandler($caption);
        }
        
        $obj_id = $this->ws_tree->lookupObjectId($a_wsp_id);
        $caption = ilObject::_lookupTitle($obj_id);
        
        if (!$this->offline_mode) {
            $url = $this->ws_access->getGotoLink($a_wsp_id, $obj_id);
        } else {
            $url = $this->offline_mode . "file_" . $obj_id . "/";
                        
            // all possible material types for now
            switch (ilObject::_lookupType($obj_id)) {
                case "tstv":
                    include_once "Modules/Test/classes/class.ilObjTestVerification.php";
                    $obj = new ilObjTestVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;
                    
                case "excv":
                    include_once "Modules/Exercise/classes/class.ilObjExerciseVerification.php";
                    $obj = new ilObjExerciseVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;
                
                case "crsv":
                    include_once "Modules/Course/classes/Verification/class.ilObjCourseVerification.php";
                    $obj = new ilObjCourseVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;
                
                case "scov":
                    include_once "Modules/ScormAicc/classes/Verification/class.ilObjSCORMVerification.php";
                    $obj = new ilObjSCORMVerification($obj_id, false);
                    $url .= $obj->getOfflineFilename();
                    break;
                
                case "file":
                    $file = new ilObjFile($obj_id, false);
                    $url .= $file->getFilename();
                    break;
            }
        }
        
        return array($caption, $url);
    }
    
    /**
     * Add personal skill
     */
    public function addSkill()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        ilPersonalSkill::addPersonalSkill($ilUser->getId(), (int) $_GET["obj_id"]);
        
        ilUtil::sendSuccess($lng->txt("msg_object_modified"));
        $ilCtrl->redirect($this, "listSkills");
    }
    
    
    
    /**
     * Confirm skill remove
     */
    public function confirmSkillRemove()
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
            
        include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
        if ($_GET["skill_id"] > 0) {
            $_POST["id"][] = $_GET["skill_id"];
        }
        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listSkills");
        } else {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_really_remove_skills"));
            $cgui->setCancel($lng->txt("cancel"), "listSkills");
            $cgui->setConfirm($lng->txt("remove"), "removeSkills");
            
            foreach ($_POST["id"] as $i) {
                $cgui->addItem("id[]", $i, ilSkillTreeNode::_lookupTitle($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Remove skills
     */
    public function removeSkills()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;
        
        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $n_id) {
                ilPersonalSkill::removeSkill($ilUser->getId(), $n_id);
            }
        }
        
        ilUtil::sendSuccess($lng->txt("msg_object_modified"));
        $ilCtrl->redirect($this, "listSkills");
    }
    
    
    //
    // Materials assignments
    //
    
    /**
     * Assign materials to skill levels
     *
     * @param
     * @return
     */
    public function assignMaterials()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilTabs = $this->tabs;


        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "render")
        );
        
        $ilCtrl->saveParameter($this, "skill_id");
        $ilCtrl->saveParameter($this, "basic_skill_id");
        $ilCtrl->saveParameter($this, "tref_id");
        
        include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
        $tpl->setTitle(ilSkillTreeNode::_lookupTitle((int) $_GET["skill_id"]));
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_" .
            ilSkillTreeNode::_lookupType((int) $_GET["skill_id"]) .
            ".svg"));
         
        // basic skill selection
        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        $tref_id = 0;
        $skill_id = (int) $_GET["skill_id"];
        if (ilSkillTreeNode::_lookupType((int) $_GET["skill_id"]) == "sktr") {
            include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
            $tref_id = $_GET["skill_id"];
            $skill_id = ilSkillTemplateReference::_lookupTemplateId($_GET["skill_id"]);
        }
        $bs = $vtree->getSubTreeForCSkillId($skill_id . ":" . $tref_id, true);
        
        $options = array();
        foreach ($bs as $b) {
            //$options[$b["id"]] = ilSkillTreeNode::_lookupTitle($b["id"]);
            $options[$b["skill_id"]] = ilSkillTreeNode::_lookupTitle($b["skill_id"]);
        }
        
        $cur_basic_skill_id = ((int) $_POST["basic_skill_id"] > 0)
            ? (int) $_POST["basic_skill_id"]
            : (((int) $_GET["basic_skill_id"] > 0)
                ? (int) $_GET["basic_skill_id"]
                : key($options));

        $ilCtrl->setParameter($this, "basic_skill_id", $cur_basic_skill_id);
            
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($lng->txt("skmg_skill"), "basic_skill_id");
        $si->setOptions($options);
        $si->setValue($cur_basic_skill_id);
        $ilToolbar->addInputItem($si, true);
        $ilToolbar->addFormButton(
            $lng->txt("select"),
            "assignMaterials"
        );
        
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
        
        // table
        include_once("./Services/Skill/classes/class.ilSkillAssignMaterialsTableGUI.php");
        $tab = new ilSkillAssignMaterialsTableGUI(
            $this,
            "assignMaterials",
            (int) $_GET["skill_id"],
            (int) $_GET["tref_id"],
            $cur_basic_skill_id
        );
        
        $tpl->setContent($tab->getHTML());
    }
    
    
    /**
     * Assign materials to skill level
     *
     * @param
     * @return
     */
    public function assignMaterial()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilSetting = $this->setting;
        $ui = $this->ui;

        if (!$ilSetting->get("disable_personal_workspace")) {
            $url = 'ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToWorkspace';
            $mbox = $ui->factory()->messageBox()->info($lng->txt("skmg_ass_materials_from_workspace"))
                ->withLinks([$ui->factory()->link()->standard(
                    $lng->txt("personal_workspace"),
                    $url
                )]);
            $message =  $ui->renderer()->render($mbox);
        }
        
        $ilCtrl->saveParameter($this, "skill_id");
        $ilCtrl->saveParameter($this, "level_id");
        $ilCtrl->saveParameter($this, "tref_id");
        $ilCtrl->saveParameter($this, "basic_skill_id");
        
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "assignMaterials")
        );


        include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceExplorerGUI.php");
        $exp = new ilWorkspaceExplorerGUI($ilUser->getId(), $this, "assignMaterial", $this, "");
        $exp->setTypeWhiteList(array("blog", "wsrt", "wfld", "file", "tstv", "excv"));
        $exp->setSelectableTypes(array("file", "tstv", "excv"));
        $exp->setSelectMode("wsp_id", true);
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
    
    /**
     * Select material
     */
    public function selectMaterial()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;


        include_once("./Services/Skill/classes/class.ilPersonalSkill.php");
        if (is_array($_POST["wsp_id"])) {
            foreach ($_POST["wsp_id"] as $w) {
                ilPersonalSkill::assignMaterial(
                    $ilUser->getId(),
                    (int) $_GET["skill_id"],
                    (int) $_GET["tref_id"],
                    (int) $_GET["basic_skill_id"],
                    (int) $_GET["level_id"],
                    (int) $w
                );
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        
        $ilCtrl->saveParameter($this, "skill_id");
        $ilCtrl->saveParameter($this, "level_id");
        $ilCtrl->saveParameter($this, "tref_id");
        $ilCtrl->saveParameter($this, "basic_skill_id");
        
        $ilCtrl->redirect($this, "assignMaterials");
    }
    
    
    /**
     * Remove material
     */
    public function removeMaterial()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;


        ilPersonalSkill::removeMaterial(
            $ilUser->getId(),
            (int) $_GET["tref_id"],
            (int) $_GET["level_id"],
            (int) $_GET["wsp_id"]
        );
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "assignMaterials");
    }
    
    
    //
    // Self evaluation
    //
    
    /**
     * Assign materials to skill levels
     *
     * @param
     * @return
     */
    public function selfEvaluation()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilTabs = $this->tabs;


        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "render")
        );
        
        $ilCtrl->saveParameter($this, "skill_id");
        $ilCtrl->saveParameter($this, "basic_skill_id");
        $ilCtrl->saveParameter($this, "tref_id");
        
        include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
        $tpl->setTitle(ilSkillTreeNode::_lookupTitle((int) $_GET["skill_id"]));
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_" .
            ilSkillTreeNode::_lookupType((int) $_GET["skill_id"]) .
            ".svg"));
         
        // basic skill selection
        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        $tref_id = 0;
        $skill_id = (int) $_GET["skill_id"];
        if (ilSkillTreeNode::_lookupType((int) $_GET["skill_id"]) == "sktr") {
            include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
            $tref_id = $_GET["skill_id"];
            $skill_id = ilSkillTemplateReference::_lookupTemplateId($_GET["skill_id"]);
        }
        $bs = $vtree->getSubTreeForCSkillId($skill_id . ":" . $tref_id, true);
        

        $options = array();
        foreach ($bs as $b) {
            $options[$b["skill_id"]] = ilSkillTreeNode::_lookupTitle($b["skill_id"]);
        }

        $cur_basic_skill_id = ((int) $_POST["basic_skill_id"] > 0)
            ? (int) $_POST["basic_skill_id"]
            : (((int) $_GET["basic_skill_id"] > 0)
                ? (int) $_GET["basic_skill_id"]
                : key($options));

        $ilCtrl->setParameter($this, "basic_skill_id", $cur_basic_skill_id);
            
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($lng->txt("skmg_skill"), "basic_skill_id");
        $si->setOptions($options);
        $si->setValue($cur_basic_skill_id);
        $ilToolbar->addInputItem($si, true);
        $ilToolbar->addFormButton(
            $lng->txt("select"),
            "selfEvaluation"
        );
        
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
        
        // table
        include_once("./Services/Skill/classes/class.ilSelfEvaluationSimpleTableGUI.php");
        $tab = new ilSelfEvaluationSimpleTableGUI(
            $this,
            "selfEvaluation",
            (int) $_GET["skill_id"],
            (int) $_GET["tref_id"],
            $cur_basic_skill_id
        );
        
        $tpl->setContent($tab->getHTML());
    }

    /**
     * Save self evaluation
     */
    public function saveSelfEvaluation()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        ilPersonalSkill::saveSelfEvaluation(
            $ilUser->getId(),
            (int) $_GET["skill_id"],
            (int) $_GET["tref_id"],
            (int) $_GET["basic_skill_id"],
            (int) $_POST["se"]
        );
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        
        /*		$ilCtrl->saveParameter($this, "skill_id");
                $ilCtrl->saveParameter($this, "level_id");
                $ilCtrl->saveParameter($this, "tref_id");
                $ilCtrl->saveParameter($this, "basic_skill_id");*/
        
        $ilCtrl->redirect($this, "render");
    }
    
    /**
     * LIst skills for adding
     *
     * @param
     * @return
     */
    public function listSkillsForAdd()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;


        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "")
        );

        include_once("./Services/Skill/classes/class.ilPersonalSkillExplorerGUI.php");
        $exp = new ilPersonalSkillExplorerGUI($this, "listSkillsForAdd", $this, "addSkill");
        if ($exp->getHasSelectableNodes()) {
            if (!$exp->handleCommand()) {
                $tpl->setContent($exp->getHTML());
            }
            ilUtil::sendInfo($lng->txt("skmg_select_skill"));
        } else {
            ilUtil::sendInfo($lng->txt("skmg_no_nodes_selectable"));
        }
    }
    
    /**
     * List profiles
     *
     * @param
     */
    public function listProfilesForGap()
    {
        $tpl = $this->tpl;

        //$a_user_id = $ilUser->getId();

        //$profiles = ilSkillProfile::getProfilesOfUser($a_user_id);

        if (count($this->user_profiles) == 0 && $this->obj_skills == null) {
            return;
        }

        $this->determineCurrentProfile();
        $this->showProfileSelectorToolbar();
        
        $tpl->setContent($this->getGapAnalysisHTML());
    }



    /**
     * Show profile selector toolbar
     */
    public function showProfileSelectorToolbar()
    {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $options = array();
        if (is_array($this->obj_skills) && $this->obj_id > 0) {
            $options[0] = $lng->txt("obj_" . ilObject::_lookupType($this->obj_id)) . ": " . ilObject::_lookupTitle($this->obj_id);
        }

        foreach ($this->user_profiles as $p) {
            $options[$p["id"]] = $lng->txt("skmg_profile") . ": " . $p["title"];
        }

        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($lng->txt("skmg_profile"), "profile_id");
        $si->setOptions($options);
        $si->setValue($this->getProfileId());
        $ilToolbar->addInputItem($si, true);
        $ilToolbar->addFormButton(
            $lng->txt("select"),
            "selectProfile"
        );
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
    }


    /**
     * Set gap analysis actual status mode "per type"
     *
     * @param string $a_type type
     */
    public function setGapAnalysisActualStatusModePerType($a_type, $a_cat_title = "")
    {
        $this->gap_mode = "max_per_type";
        $this->gap_mode_type = $a_type;
        $this->gap_cat_title = $a_cat_title;
        $this->mode = "gap";
    }

    /**
     * Set gap analysis actual status mode "per object"
     *
     * @param integer $a_obj_id object id
     */
    public function setGapAnalysisActualStatusModePerObject($a_obj_id, $a_cat_title = "")
    {
        $this->gap_mode = "max_per_object";
        $this->gap_mode_obj_id = $a_obj_id;
        $this->gap_cat_title = $a_cat_title;
        $this->mode = "gap";
    }

    /**
     * Get actual levels
     *
     * @param array $skills
     * @param int $user_id
     */
    protected function getActualLevels($skills, $user_id)
    {
        // get actual levels for gap analysis
        $this->actual_levels = array();
        include_once("./Services/Skill/classes/class.ilBasicSkill.php");
        foreach ($skills as $sk) {
            $bs = new ilBasicSkill($sk["base_skill_id"]);
            if ($this->gap_mode == "max_per_type") {
                $max = $bs->getMaxLevelPerType($sk["tref_id"], $this->gap_mode_type, $user_id);
                $this->actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
            } elseif ($this->gap_mode == "max_per_object") {
                $max = $bs->getMaxLevelPerObject($sk["tref_id"], $this->gap_mode_obj_id, $user_id);
                $this->actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
            } else {
                $max = $bs->getMaxLevel($sk["tref_id"], $user_id);
                $this->actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
            }
        }
    }


    /**
     * Get gap analysis html
     *
     * @param
     * @param array $a_skills deprecated, use setObjectSkills and listProfiles instead
     * @return
     */
    public function getGapAnalysisHTML($a_user_id = 0, $a_skills = null)
    {
        $ilUser = $this->user;
        $lng = $this->lng;


        if ($a_skills == null) {
            $a_skills = $this->obj_skills;
        }

        include_once("./Services/UIComponent/Panel/classes/class.ilPanelGUI.php");

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

        $skills = array();
        if ($this->getProfileId() > 0) {
            $profile = new ilSkillProfile($this->getProfileId());
            $this->profile_levels = $profile->getSkillLevels();

            foreach ($this->profile_levels as $l) {
                $skills[] = array(
                    "base_skill_id" => $l["base_skill_id"],
                    "tref_id" => $l["tref_id"],
                    "level_id" => $l["level_id"]
                    );
            }
        } elseif (is_array($a_skills)) {
            $skills = $a_skills;
        }

        // get actual levels for gap analysis
        $this->getActualLevels($skills, $user_id);

        $incl_self_eval = false;
        if (count($this->getGapAnalysisSelfEvalLevels()) > 0) {
            $incl_self_eval = true;
            $self_vals = $this->getGapAnalysisSelfEvalLevels();
        }

        // output spider stuff
        $all_chart_html = "";

        // determine skills that should be shown in the spider web
        $sw_skills = array();
        foreach ($skills as $sk) {
            if (!in_array($sk["base_skill_id"] . ":" . $sk["tref_id"], $this->hidden_skills)) {
                $sw_skills[] = $sk;
            }
        }

        if (count($sw_skills) >= 3) {
            $skill_packages = array();

            if (count($sw_skills) < 8) {
                $skill_packages[1] = $sw_skills;
            } else {
                $mod = count($sw_skills) % 7;
                $pkg_num = floor((count($sw_skills) - 1) / 7) + 1;
                $cpkg = 1;
                foreach ($sw_skills as $k => $s) {
                    $skill_packages[$cpkg][$k] = $s;
                    if ($mod < 3 && count($skill_packages) == ($pkg_num - 1) && count($skill_packages[$cpkg]) == 3+$mod) {
                        $cpkg+=1;
                    } elseif (count($skill_packages[$cpkg]) == 7) {
                        $cpkg+=1;
                    }
                }
            }

            $pkg_cnt = 0;
            foreach ($skill_packages as $pskills) {
                $pkg_cnt++;
                $max_cnt = 0;
                $leg_labels = array();
                //var_dump($this->profile_levels);
                //foreach ($this->profile_levels as $k => $l)

                // write target, actual and self counter to skill array
                foreach ($pskills as $k => $l) {
                    //$bs = new ilBasicSkill($l["base_skill_id"]);
                    $bs = new ilBasicSkill($l["base_skill_id"]);
                    $leg_labels[] = ilBasicSkill::_lookupTitle($l["base_skill_id"], $l["tref_id"]);
                    $levels = $bs->getLevelData();
                    $cnt = 0;
                    foreach ($levels as $lv) {
                        $cnt++;
                        if ($l["level_id"] == $lv["id"]) {
                            $pskills[$k]["target_cnt"] = $cnt;
                        }
                        if ($this->actual_levels[$l["base_skill_id"]][$l["tref_id"]] == $lv["id"]) {
                            $pskills[$k]["actual_cnt"] = $cnt;
                        }
                        if ($incl_self_eval) {
                            if ($self_vals[$l["base_skill_id"]][$l["tref_id"]] == $lv["id"]) {
                                $pskills[$k]["self_cnt"] = $cnt;
                            }
                        }
                        $max_cnt = max($max_cnt, $cnt);
                    }
                }

                include_once("./Services/Chart/classes/class.ilChart.php");
                $chart = ilChart::getInstanceByType(ilChart::TYPE_SPIDER, "gap_chart" . $pkg_cnt);
                $chart->setsize(800, 300);
                $chart->setYAxisMax($max_cnt);
                $chart->setLegLabels($leg_labels);

                // target level
                $cd = $chart->getDataInstance();
                $cd->setLabel($lng->txt("skmg_target_level"));
                $cd->setFill(true, "#A0A0A0");

                // other users
                $cd2 = $chart->getDataInstance();
                if ($this->gap_cat_title != "") {
                    $cd2->setLabel($this->gap_cat_title);
                } elseif ($this->gap_mode == "max_per_type") {
                    $cd2->setLabel($lng->txt("objs_" . $this->gap_mode_type));
                } elseif ($this->gap_mode == "max_per_object") {
                    $cd2->setLabel(ilObject::_lookupTitle($this->gap_mode_obj_id));
                }
                //$cd2->setFill(true, "#dcb496");
                $cd2->setFill(true, "#FF8080");
                $cd2->setFill(true, "#cc8466");

                // self evaluation
                if ($incl_self_eval) {
                    $cd3 = $chart->getDataInstance();
                    $cd3->setLabel($lng->txt("skmg_self_evaluation"));
                    $cd3->setFill(true, "#6ea03c");
                }

                // fill in data
                $cnt = 0;
                foreach ($pskills as $pl) {
                    $cd->addPoint($cnt, (int) $pl["target_cnt"]);
                    $cd2->addPoint($cnt, (int) $pl["actual_cnt"]);
                    if ($incl_self_eval) {
                        $cd3->addPoint($cnt, (int) $pl["self_cnt"]);
                    }
                    $cnt++;
                }

                // add data to chart
                if ($this->getProfileId() > 0) {
                    $chart->addData($cd);
                }
                $chart->addData($cd2);
                if ($incl_self_eval && count($this->getGapAnalysisSelfEvalLevels()) > 0) {
                    $chart->addData($cd3);
                }

                if ($pkg_cnt == 1) {
                    $lg = new ilChartLegend();
                    $chart->setLegend($lg);
                }

                $chart_html = $chart->getHTML();
                $all_chart_html.= $chart_html;
            }

            $pan = ilPanelGUI::getInstance();
            $pan->setPanelStyle(ilPanelGUI::PANEL_STYLE_PRIMARY);
            $pan->setBody($all_chart_html);
            $all_chart_html = $pan->getHTML();
        }

        $stree = new ilSkillTree();
        $html = "";

        // order skills per virtual skill tree
        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        $skills = $vtree->getOrderedNodeset($skills, "base_skill_id", "tref_id");
        foreach ($skills as $s) {
            $path = $stree->getSkillTreePath($s["base_skill_id"]);

            // check draft
            foreach ($path as $p) {
                if ($p["status"] == ilSkillTreeNode::STATUS_DRAFT) {
                    continue(2);
                }
            }
            $html.= $this->getSkillHTML($s["base_skill_id"], $user_id, false, $s["tref_id"]);
        }

        // list skills
        //		include_once("./Services/Skill/classes/class.ilPersonalSkillTableGUI.php");
        //		$sktab = new ilPersonalSkillTableGUI($this, "listSkills");
        
        return $intro_html . $all_chart_html . $html;
    }
    
    /**
     * Select profile
     *
     * @param
     */
    public function selectProfile()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "profile_id", $_POST["profile_id"]);
        if ($this->mode == "gap") {
            $ilCtrl->redirect($this, "listProfilesForGap");
        } else {
            $ilCtrl->redirect($this, "listAssignedProfile");
        }
    }

    /**
     * Get materials
     *
     * @param
     * @return string
     */
    public function getMaterials($a_levels, $a_tref_id = 0, $a_user_id = 0)
    {
        $ilUser = $this->user;
        $lng = $this->lng;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        // only render, if materials given
        $got_mat = false;
        foreach ($a_levels as $v) {
            $mat_cnt = ilPersonalSkill::countAssignedMaterial(
                $a_user_id,
                $a_tref_id,
                $v["id"]
            );
            if ($mat_cnt > 0) {
                $got_mat = true;
            }
        }
        if (!$got_mat) {
            return "";
        }

        $tpl = new ilTemplate("tpl.skill_materials.html", true, true, "Services/Skill");
        foreach ($a_levels as $k => $v) {
            $got_mat = false;
            foreach (ilPersonalSkill::getAssignedMaterial(
                $a_user_id,
                $a_tref_id,
                $v["id"]
            ) as $item) {
                $tpl->setCurrentBlock("material");
                $mat_data = $this->getMaterialInfo($item["wsp_id"], $a_user_id);
                $tpl->setVariable("HREF_LINK", $mat_data[1]);
                $tpl->setVariable("TXT_LINK", $mat_data[0]);
                $tpl->parseCurrentBlock();
                $got_mat = true;
            }
            if ($got_mat) {
                $tpl->setCurrentBlock("level");
                $tpl->setVariable("LEVEL_VAL", $v["title"]);
                $tpl->parseCurrentBlock();
            }
        }
        $tpl->setVariable("TXT_MATERIAL", $lng->txt("skmg_materials"));

        return $tpl->get();
    }

    /**
     * Get profile target item
     *
     * @param
     * @return
     */
    public function getProfileTargetItem($a_profile_id, $a_levels, $a_tref_id = 0)
    {
        $lng = $this->lng;

        $profile = new ilSkillProfile($a_profile_id);
        $profile_levels = $profile->getSkillLevels();

        $a_activated_levels = array();

        foreach ($a_levels as $k => $v) {
            foreach ($profile_levels as $pl) {
                if ($pl["level_id"] == $v["id"] &&
                    $pl["base_skill_id"] == $v["skill_id"] &&
                    $a_tref_id == $pl["tref_id"]) {
                    $a_activated_levels[] = $pl["level_id"];
                }
            }
        }

        $tpl = new ilTemplate("tpl.skill_eval_item.html", true, true, "Services/Skill");
        $tpl->setVariable("SCALE_BAR", $this->getScaleBar($a_levels, $a_activated_levels));

        $tpl->setVariable("TYPE", $lng->txt("skmg_target_level"));
        $tpl->setVariable("TITLE", "");

        return $tpl->get();
    }

    /**
     *
     *
     * @param
     * @return
     */
    public function getActualGapItem($a_levels, $a_tref_id = 0)
    {
        $lng = $this->lng;

        $a_activated_levels = array();
        foreach ($a_levels as $k => $v) {
            if ($this->actual_levels[$v["skill_id"]][$a_tref_id] == $v["id"]) {
                $a_activated_levels[] = $v["id"];
            }
        }

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
        $tpl->setVariable("TYPE", $lng->txt("skmg_eval_type_" . $type));
        if ($type > 0) {
            $tpl->touchBlock("st" . $type);
            $tpl->touchBlock("stb" . $type);
        }

        if ($title != $lng->txt("skmg_eval_type_" . $type)) {
            $tpl->setVariable("TITLE", $title);
        }

        return $tpl->get();
    }

    /**
     *
     *
     * @param
     * @return
     */
    public function getSelfEvalGapItem($a_levels, $a_tref_id = 0)
    {
        $lng = $this->lng;

        $self_vals = $this->getGapAnalysisSelfEvalLevels();
        if (count($self_vals) == 0) {
            return;
        }

        $a_activated_levels = array();
        foreach ($a_levels as $k => $v) {
            if ($self_vals[$v["skill_id"]][$a_tref_id] == $v["id"]) {
                $a_activated_levels[] = $v["id"];
            }
        }

        $tpl = new ilTemplate("tpl.skill_eval_item.html", true, true, "Services/Skill");
        $tpl->setVariable("SCALE_BAR", $this->getScaleBar($a_levels, $a_activated_levels));

        $type = 3;
        $tpl->setVariable("TYPE", $lng->txt("skmg_eval_type_" . $type));
        if ($type > 0) {
            $tpl->touchBlock("st" . $type);
            $tpl->touchBlock("stb" . $type);
        }

        return $tpl->get();
    }


    /**
     * Get scale bar
     *
     * @param
     * @return
     */
    public function getScaleBar($a_levels, $a_activated_levels)
    {
        $vals = array();

        if (!is_array($a_activated_levels)) {
            $a_activated_levels = array($a_activated_levels);
        }

        foreach ($a_levels as $level) {
            $vals[$level["title"]] = (in_array($level["id"], $a_activated_levels));
        }
        $scale_bar = $this->ui_fac->chart()->scaleBar($vals);

        return $this->ui_ren->render($scale_bar);
    }

    /**
     * Get eval item
     *
     * @param
     * @return
     */
    public function getEvalItem($a_levels, $a_level_entry)
    {
        $lng = $this->lng;
        $ilAccess = $this->access;

        $tpl = new ilTemplate("tpl.skill_eval_item.html", true, true, "Services/Skill");
        $tpl->setVariable("SCALE_BAR", $this->getScaleBar($a_levels, $a_level_entry["level_id"]));

        include_once("./Services/Skill/classes/class.ilSkillEval.php");
        $type = ilSkillEval::TYPE_APPRAISAL;

        if ($a_level_entry["self_eval"] == 1) {
            $type = ilSkillEval::TYPE_SELF_EVAL;
        }

        if ($a_level_entry["trigger_obj_type"] == "tst") {
            $type = ilSkillEval::TYPE_MEASUREMENT;
        }

        ilDatePresentation::setUseRelativeDates(false);
        $title = ($a_level_entry["trigger_obj_id"] > 0)
                ? $a_level_entry["trigger_title"]
                : "";

        if ($a_level_entry["trigger_ref_id"] > 0
            && $ilAccess->checkAccess("read", "", $a_level_entry["trigger_ref_id"])) {
            include_once("./Services/Link/classes/class.ilLink.php");
            $title = "<a href='" . ilLink::_getLink($a_level_entry["trigger_ref_id"]) . "'>" . $title . "</a>";
        }

        $tpl->setVariable("TYPE", $lng->txt("skmg_eval_type_" . $type));
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

    /**
     * Get level description
     *
     * @param
     * @return
     */
    public function getSkillDescription($skill)
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

    /**
     * Render suggested resources
     *
     * @param
     * @return
     */
    public function getSuggestedResources($a_profile_id, $a_levels, $a_base_skill, $a_tref_id)
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.suggested_resources.html", true, true, "Services/Skill");

        // use a profile
        if ($a_profile_id > 0) {
            $too_low = true;
            $current_target_level = 0;

            foreach ($a_levels as $k => $v) {
                foreach ($this->profile_levels as $pl) {
                    if ($pl["level_id"] == $v["id"] &&
                        $pl["base_skill_id"] == $v["skill_id"]) {
                        $too_low = true;
                        $current_target_level = $v["id"];
                    }
                }

                if ($this->actual_levels[$v["skill_id"]][$a_tref_id] == $v["id"]) {
                    $too_low = false;
                }
            }

            // suggested resources
            if ($too_low) {
                include_once("./Services/Skill/classes/class.ilSkillResources.php");
                $skill_res = new ilSkillResources($a_base_skill, $a_tref_id);
                $res = $skill_res->getResources();
                $imp_resources = array();
                foreach ($res as $level) {
                    foreach ($level as $r) {
                        if ($r["imparting"] == true &&
                            $current_target_level == $r["level_id"]) {
                            $imp_resources[] = $r;
                        }
                    }
                }
                foreach ($imp_resources as $r) {
                    $ref_id = $r["rep_ref_id"];
                    $obj_id = ilObject::_lookupObjId($ref_id);
                    $title = ilObject::_lookupTitle($obj_id);
                    include_once("./Services/Link/classes/class.ilLink.php");
                    $tpl->setCurrentBlock("resource_item");
                    $tpl->setVariable("TXT_RES", $title);
                    $tpl->setVariable("HREF_RES", ilLink::_getLink($ref_id));
                    $tpl->parseCurrentBlock();
                }
                if (count($imp_resources) > 0) {
                    $tpl->touchBlock("resources_list");
                    $tpl->setVariable("SUGGESTED_MAT_MESS", $lng->txt("skmg_skill_needs_impr_res"));
                    return $tpl->get();
                } else {
                    $tpl->setVariable("SUGGESTED_MAT_MESS", $lng->txt("skmg_skill_needs_impr_no_res"));
                    return $tpl->get();
                }
            } else {
                $tpl->setVariable("SUGGESTED_MAT_MESS", $lng->txt("skmg_skill_no_needs_impr"));
                return $tpl->get();
            }
        } else {
            // no profile, just list all resources
            include_once("./Services/Skill/classes/class.ilSkillResources.php");
            $skill_res = new ilSkillResources($a_base_skill, $a_tref_id);
            $res = $skill_res->getResources();
            // add $r["level_id"] info
            $any = false;
            foreach ($res as $level) {
                $available = false;
                $cl = 0;
                foreach ($level as $r) {
                    if ($r["imparting"]) {
                        $ref_id = $r["rep_ref_id"];
                        $obj_id = ilObject::_lookupObjId($ref_id);
                        $title = ilObject::_lookupTitle($obj_id);
                        include_once("./Services/Link/classes/class.ilLink.php");
                        $tpl->setCurrentBlock("resource_item");
                        $tpl->setVariable("TXT_RES", $title);
                        $tpl->setVariable("HREF_RES", ilLink::_getLink($ref_id));
                        $tpl->parseCurrentBlock();
                        $available = true;
                        $any = true;
                        $cl = $r["level_id"];
                    }
                }
                if ($available) {
                    $tpl->setCurrentBlock("resources_list_level");
                    $tpl->setVariable("TXT_LEVEL", $lng->txt("skmg_level"));
                    $tpl->setVariable("LEVEL_NAME", ilBasicSkill::lookupLevelTitle($cl));
                    $tpl->parseCurrentBlock();
                    $tpl->touchBlock("resources_list");
                }
            }
            if ($any) {
                $tpl->setVariable("SUGGESTED_MAT_MESS", $lng->txt("skmg_suggested_resources"));
                return $tpl->get();
            }
        }
        return "";
    }

    /**
     * List profile
     *
     * @param
     * @return
     */
    public function listAssignedProfile()
    {
        $ilCtrl = $this->ctrl;

        $main_tpl = $this->tpl;

        $tpl = new ilTemplate("tpl.skill_filter.html", true, true, "Services/Skill");

        $this->setTabs("profile");

        $this->determineCurrentProfile();
        $this->showProfileSelectorToolbar();

        $filter_toolbar = new ilToolbarGUI();
        $filter_toolbar->setFormAction($ilCtrl->getFormAction($this));
        $this->getFilter()->addToToolbar($filter_toolbar, true);

        $skills = array();
        if ($this->getProfileId() > 0) {
            $profile = new ilSkillProfile($this->getProfileId());
            $this->profile_levels = $profile->getSkillLevels();

            foreach ($this->profile_levels as $l) {
                $skills[] = array(
                    "base_skill_id" => $l["base_skill_id"],
                    "tref_id" => $l["tref_id"],
                    "level_id" => $l["level_id"]
                );
            }
        }

        $this->getActualLevels($skills, $this->user->getId());

        // render
        $html = "";
        foreach ($skills as $s) {
            // todo draft check
            $html.= $this->getSkillHTML($s["base_skill_id"], 0, true, $s["tref_id"]);
        }

        if ($html != "") {
            $filter_toolbar->addFormButton($this->lng->txt("skmg_refresh_view"), "applyFilterAssignedProfiles");

            $tpl->setVariable("FILTER", $filter_toolbar->getHTML());

            $html = $tpl->get() . $html;
        }

        $main_tpl->setContent($html);
    }
}
