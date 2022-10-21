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
use ILIAS\Skill\Service\SkillAdminGUIRequest;
use ILIAS\Skill\Service\SkillInternalManagerService;
use ILIAS\GlobalScreen\ScreenContext\ContextServices;
use ILIAS\Skill\Tree;

/**
 * Skill management main GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilObjSkillManagementGUI: ilPermissionGUI, ilSkillProfileGUI, ilExportGUI, SkillTreeAdminGUI
 * @ilCtrl_isCalledBy ilObjSkillManagementGUI: ilAdministrationGUI
 */
class ilObjSkillManagementGUI extends ilObjectGUI
{
    protected ilRbacSystem $rbacsystem;
    protected ilErrorHandling $error;
    protected ilTabsGUI $tabs;
    protected Factory $ui_fac;
    protected Renderer $ui_ren;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected ContextServices $tool_context;
    protected ilPropertyFormGUI $form;
    protected ilSkillTree $skill_tree;
    protected Tree\SkillTreeNodeManager $skill_tree_node_manager;
    protected SkillInternalManagerService $skill_manager;
    protected SkillManagementAccess $management_access_manager;
    protected int $requested_node_id = 0;
    protected int $requested_tref_id = 0;
    protected int $requested_templates_tree = 0;
    protected string $requested_skexpand = "";
    protected bool $requested_tmpmode = false;

    /**
     * @var string[]
     */
    protected array $requested_titles = [];

    /**
     * @var int[]
     */
    protected array $requested_node_ids = [];

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
        $this->rbacsystem = $DIC->rbac()->system();
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
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $ilCtrl = $DIC->ctrl();

        $this->tool_context = $DIC->globalScreen()->tool()->context();

        $this->type = 'skmg';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('skmg');

        $ilCtrl->saveParameter($this, "node_id");
        $this->skill_manager = $DIC->skills()->internal()->manager();
        $this->skill_tree_node_manager = $this->skill_manager->getTreeNodeManager($this->object->getId());
        $this->management_access_manager = $this->skill_manager->getManagementAccessManager($this->object->getRefId());

        $this->requested_node_id = $this->admin_gui_request->getNodeId();
        $this->requested_tref_id = $this->admin_gui_request->getTrefId();
        $this->requested_templates_tree = $this->admin_gui_request->getTemplatesTree();
        $this->requested_skexpand = $this->admin_gui_request->getSkillExpand();
        $this->requested_tmpmode = $this->admin_gui_request->getTemplateMode();
        $this->requested_titles = $this->admin_gui_request->getTitles();
        $this->requested_node_ids = $this->admin_gui_request->getNodeIds();
    }

    public function executeCommand(): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$this->management_access_manager->hasReadManagementPermission()) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilskillrootgui':
                $skrt_gui = new ilSkillRootGUI(
                    $this->skill_tree_node_manager,
                    $this->requested_node_id
                );
                $skrt_gui->setParentGUI($this);
                $ret = $this->ctrl->forwardCommand($skrt_gui);
                break;
                /*
                case 'ilskillcategorygui':
                    $this->tabs_gui->activateTab("skills");
                    $scat_gui = new ilSkillCategoryGUI($this->requested_node_id);
                    $scat_gui->setParentGUI($this);
                    $this->showTree(false, $scat_gui, "listItems");
                    $ret = $this->ctrl->forwardCommand($scat_gui);
                    break;

                case 'ilbasicskillgui':
                    $this->tabs_gui->activateTab("skills");
                    $skill_gui = new ilBasicSkillGUI($this->requested_node_id);
                    $skill_gui->setParentGUI($this);
                    $this->showTree(false, $skill_gui, "edit");
                    $ret = $this->ctrl->forwardCommand($skill_gui);
                    break;

                case 'ilskilltemplatecategorygui':
                    $this->tabs_gui->activateTab("skill_templates");
                    $sctp_gui = new ilSkillTemplateCategoryGUI($this->requested_node_id, $this->requested_tref_id);
                    $sctp_gui->setParentGUI($this);
                    $this->showTree(($this->requested_tref_id == 0), $sctp_gui, "listItems");
                    $ret = $this->ctrl->forwardCommand($sctp_gui);
                    break;

                case 'ilbasicskilltemplategui':
                    $this->tabs_gui->activateTab("skill_templates");
                    $sktp_gui = new ilBasicSkillTemplateGUI($this->requested_node_id, $this->requested_tref_id);
                    $sktp_gui->setParentGUI($this);
                    $this->showTree(($this->requested_tref_id == 0), $sktp_gui, "edit");
                    $ret = $this->ctrl->forwardCommand($sktp_gui);
                    break;

                case 'ilskilltemplatereferencegui':
                    $this->tabs_gui->activateTab("skills");
                    $sktr_gui = new ilSkillTemplateReferenceGUI($this->requested_tref_id);
                    $sktr_gui->setParentGUI($this);
                    $this->showTree(false, $sktr_gui, "listItems");
                    $ret = $this->ctrl->forwardCommand($sktr_gui);
                    break;

                case "ilskillprofilegui":
                    $ilTabs->activateTab("profiles");
                    $skprof_gui = new ilSkillProfileGUI();
                    $ret = $this->ctrl->forwardCommand($skprof_gui);
                    break;
                    */

            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab('permissions');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilexportgui":
                $this->prepareOutput();
                $this->tabs_gui->activateTab('export');
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                //$exp_gui->addFormat("html", "", $this, "exportHTML");
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;

            case "skilltreeadmingui":
                $this->prepareOutput();
                $ilTabs->activateTab("skill_trees");
                $gui = new SkillTreeAdminGUI($this->skill_manager);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $this->prepareOutput();
                if (!$cmd || $cmd == 'view') {
                    $cmd = "listTrees";
                }

                if ($cmd == "showTree") {
                    $this->showTree((bool) $this->requested_templates_tree);
                } else {
                    $this->$cmd();
                }
                break;
        }
    }

    public function getAdminTabs(): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;
        $lng = $this->lng;

        if ($this->management_access_manager->hasReadManagementPermission()) {
            $this->tabs_gui->addTab(
                "skill_trees",
                $lng->txt("skmg_skill_trees"),
                $this->ctrl->getLinkTargetByClass("skilltreeadmingui", "")
            );

            /*
            $this->tabs_gui->addTab(
                "skills",
                $lng->txt("skmg_skills"),
                $this->ctrl->getLinkTarget($this, "editSkills")
            );

            $this->tabs_gui->addTab(
                "skill_templates",
                $lng->txt("skmg_skill_templates"),
                $this->ctrl->getLinkTarget($this, "editSkillTemplates")
            );

            $this->tabs_gui->addTab(
                "profiles",
                $lng->txt("skmg_skill_profiles"),
                $this->ctrl->getLinkTargetByClass("ilskillprofilegui")
            );
            */

            $this->tabs_gui->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );

            /*if ($this->management_access_manager->hasEditManagementSettingsPermission()) {
                $this->tabs_gui->addTab(
                    "export",
                    $lng->txt("export"),
                    $this->ctrl->getLinkTargetByClass("ilexportgui", "")
                );
            }*/
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

    public function saveAllTitles(bool $a_succ_mess = true): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!count($this->requested_titles) == 0) {
            foreach ($this->requested_titles as $id => $title) {
                $node_obj = ilSkillTreeNodeFactory::getInstance($id);
                if (is_object($node_obj)) {
                    // update title
                    ilSkillTreeNode::_writeTitle($id, ilUtil::stripSlashes($title));
                }
            }
            if ($a_succ_mess) {
                $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            }
        }
        $ilCtrl->redirect($this, "editSkills");
    }

    public function saveAllTemplateTitles(bool $a_succ_mess = true): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!count($this->requested_titles) == 0) {
            foreach ($this->requested_titles as $id => $title) {
                $node_obj = ilSkillTreeNodeFactory::getInstance($id);
                if (is_object($node_obj)) {
                    // update title
                    ilSkillTreeNode::_writeTitle($id, ilUtil::stripSlashes($title));
                }
            }
            if ($a_succ_mess) {
                $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            }
        }
        $ilCtrl->redirect($this, "editSkillTemplates");
    }

    public function expandAll(bool $a_redirect = true): void
    {
        $this->requested_skexpand = "";
        $n_id = ($this->requested_node_id > 0)
            ? $this->requested_node_id
            : $this->skill_tree->readRootId();
        $stree = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($n_id));
        $n_arr = [];
        foreach ($stree as $n) {
            $n_arr[] = $n["child"];
            ilSession::set("skexpand", $n_arr);
        }
        $this->saveAllTitles(false);
    }

    public function collapseAll(bool $a_redirect = true): void
    {
        $this->requested_skexpand = "";
        $n_id = ($this->requested_node_id > 0)
            ? $this->requested_node_id
            : $this->skill_tree->readRootId();
        $stree = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($n_id));
        $old = ilSession::get("skexpand");
        foreach ($stree as $n) {
            if (in_array($n["child"], $old) && $n["child"] != $n_id) {
                $k = array_search($n["child"], $old);
                unset($old[$k]);
            }
        }
        ilSession::set("skexpand", $old);
        $this->saveAllTitles(false);
    }

    public function deleteNodes(object $a_gui): void
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;

        if (empty($this->requested_node_ids)) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        $ilTabs->clearTargets();

        // check usages
        $mode = "";
        $cskill_ids = [];
        foreach ($this->requested_node_ids as $id) {
            if (in_array(ilSkillTreeNode::_lookupType($id), array("skll", "scat", "sktr"))) {
                if ($mode == "templates") {
                    $this->ilias->raiseError("Skill Deletion - type mismatch.", $this->ilias->error_obj->MESSAGE);
                }
                $mode = "basic";
                $skill_id = $id;
                $tref_id = 0;
                if (ilSkillTreeNode::_lookupType($id) == "sktr") {
                    $skill_id = ilSkillTemplateReference::_lookupTemplateId($id);
                    $tref_id = $id;
                }
                $cskill_ids[] = array("skill_id" => $skill_id, "tref_id" => $tref_id);
            }
            if (in_array(ilSkillTreeNode::_lookupType($id), array("sktp", "sctp"))) {
                if ($mode == "basic") {
                    $this->ilias->raiseError("Skill Deletion - type mismatch.", $this->ilias->error_obj->MESSAGE);
                }
                $mode = "templates";

                foreach (ilSkillTemplateReference::_lookupTrefIdsForTemplateId($id) as $tref_id) {
                    $cskill_ids[] = array("skill_id" => $id, "tref_id" => $tref_id);
                }
            }
            // for cats, skills and template references, get "real" usages
            // for skill and category templates check usage in references
        }

        if ($mode == "basic" || $mode == "templates") {
            $u = new ilSkillUsage();
            $usages = $u->getAllUsagesInfoOfSubtrees($cskill_ids);
            if (count($usages) > 0) {
                $html = "";
                foreach ($usages as $k => $usage) {
                    $tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage);
                    $html .= $tab->getHTML() . "<br/><br/>";
                }
                $tpl->setContent($html);
                $ilCtrl->saveParameter($a_gui, "tmpmode");
                $ilToolbar->addButton(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($a_gui, "cancelDelete")
                );
                $this->tpl->setOnScreenMessage('failure', $lng->txt("skmg_cannot_delete_nodes_in_use"));
                return;
            }
        } else {
            $this->ilias->raiseError("Skill Deletion - type mismatch.", $this->ilias->error_obj->MESSAGE);
        }

        // SAVE POST VALUES
        ilSession::set("saved_post", $this->requested_node_ids);

        $confirmation_gui = new ilConfirmationGUI();

        $ilCtrl->setParameter($a_gui, "tmpmode", (int) $this->requested_tmpmode);
        $a_form_action = $this->ctrl->getFormAction($a_gui);
        $confirmation_gui->setFormAction($a_form_action);
        $confirmation_gui->setHeaderText($this->lng->txt("info_delete_sure"));

        // Add items to delete
        foreach ($this->requested_node_ids as $id) {
            if ($id != ilTree::POS_FIRST_NODE) {
                $node_obj = ilSkillTreeNodeFactory::getInstance($id);
                $confirmation_gui->addItem(
                    "id[]",
                    (string) $node_obj->getId(),
                    $node_obj->getTitle(),
                    ilUtil::getImagePath("icon_" . $node_obj->getType() . ".svg")
                );
            }
        }

        $confirmation_gui->setCancel($lng->txt("cancel"), "cancelDelete");
        $confirmation_gui->setConfirm($lng->txt("confirm"), "confirmedDelete");

        $tpl->setContent($confirmation_gui->getHTML());
    }

    public function cancelDelete(): void
    {
        $this->ctrl->redirect($this, "editSkills");
    }

    public function confirmedDelete(): void
    {
        $ilCtrl = $this->ctrl;

        // delete all selected objects
        foreach ($this->requested_node_ids as $id) {
            if ($id != ilTree::POS_FIRST_NODE) {
                $obj = ilSkillTreeNodeFactory::getInstance($id);
                $node_data = $this->skill_tree->getNodeData($id);
                if (is_object($obj)) {
                    $obj->delete();
                }
                if ($this->skill_tree->isInTree($id)) {
                    $this->skill_tree->deleteTree($node_data);
                }
            }
        }

        // feedback
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("info_deleted"), true);
    }

    //
    // Skill Templates
    //

    public function editSkillTemplates(): void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->activateTab("skill_templates");
        $ilCtrl->setParameterByClass("ilobjskillmanagementgui", "node_id", $this->skill_tree->readRootId());
        $ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
    }

    //
    // Tree
    //

    public function showTree(bool $a_templates, $a_gui = null, string $a_gui_cmd = ""): void
    {
        $ilUser = $this->user;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($a_templates) {
            if ($this->requested_node_id == 0 || $this->requested_node_id == 1) {
                return;
            }

            if ($this->requested_node_id > 1) {
                $path = $this->skill_tree->getPathId($this->requested_node_id);
                if (ilSkillTreeNode::_lookupType($path[1]) == "sktp") {
                    return;
                }
            }
        }

        $ilCtrl->setParameter($this, "templates_tree", (int) $a_templates);

        if ($a_templates) {
            $this->tool_context->current()->addAdditionalData(ilSkillGSToolProvider::SHOW_TEMPLATE_TREE, true);
            $exp = new ilSkillTemplateTreeExplorerGUI($this, "showTree", $this->skill_tree->getTreeId());
        } else {
            $this->tool_context->current()->addAdditionalData(ilSkillGSToolProvider::SHOW_SKILL_TREE, true);
            $exp = new ilSkillTreeExplorerGUI($this, "showTree", $this->skill_tree->getTreeId());
        }
        if (!$exp->handleCommand()) {
            $tpl->setLeftNavContent($exp->getHTML());
        }
    }
}
