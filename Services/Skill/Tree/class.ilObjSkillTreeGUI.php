<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use \ILIAS\Skill\Service\SkillInternalManagerService;
use \ILIAS\Skill\Tree;
use \ILIAS\Skill\Access;
use \ILIAS\UI\Component\Input\Container\Form;
use \ILIAS\GlobalScreen\ScreenContext;

/**
 * Skill tree gui class
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjSkillTreeGUI: ilPermissionGUI, ilSkillProfileGUI, ilExportGUI
 */
class ilObjSkillTreeGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    protected $skill_tree;

    /**
     * @var Tree\SkillTreeManager
     */
    protected $skill_tree_manager;

    /**
     * @var Tree\SkillTreeNodeManager
     */
    protected $skill_tree_node_manager;

    /**
     * @var Access\SkillTreeAccess
     */
    protected $skill_tree_access_manager;

    /**
     * @var Access\SkillManagementAccess
     */
    protected $skill_management_access_manager;

    /**
     * @var ilBasicSkillTreeRepository
     */
    protected $skill_tree_repo;

    /**
     * @var \ILIAS\Skill\Tree\SkillTreeFactory
     */
    protected $skill_tree_factory;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $main_tpl;

    /**
     * @var ilLocatorGUI
     */
    protected $locator;

    /**
     * @var ScreenContext\ContextServices
     */
    protected $tool_context;

    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
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
        $ilCtrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->locator = $DIC["ilLocator"];

        $this->type = 'skee';
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->lng->loadLanguageModule('skmg');

        $this->skill_tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->skill_tree_factory = $DIC->skills()->internal()->factory()->tree();
        $this->skill_tree = $this->skill_tree_factory->getTreeById($this->object->getId());

        $ilCtrl->saveParameter($this, "obj_id");
        $this->requested_obj_id = (string) ($_GET["obj_id"] ?? "");

        $this->tool_context = $DIC->globalScreen()->tool()->context();
    }

    /**
     * Init
     * @param Tree\SkillTreeManager $manager
     */
    public function init(
        SkillInternalManagerService $skill_manager
    )
    {
        $this->skill_tree_manager = $skill_manager->getTreeManager();
        $this->skill_tree_node_manager = $skill_manager->getTreeNodeManager($this->object->getId());
        $this->skill_tree_access_manager = $skill_manager->getTreeAccessManager($this->object->getRefId());
        $this->skill_management_access_manager = $skill_manager->getManagementAccessManager($this->skill_tree_manager->getSkillManagementRefId());
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();
        $this->showLocator();


        if (!$this->skill_tree_access_manager->hasReadTreePermission()) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilskillrootgui':
                $skrt_gui = new ilSkillRootGUI(
                    $this->skill_tree_node_manager,
                    (int) $this->requested_obj_id
                );
                $skrt_gui->setParentGUI($this);
                $ret = $this->ctrl->forwardCommand($skrt_gui);
                break;

            case 'ilskillcategorygui':
                $this->tabs_gui->activateTab("skills");
                $scat_gui = new ilSkillCategoryGUI(
                    $this->skill_tree_node_manager,
                    (int) $this->requested_obj_id
                );
                $scat_gui->setParentGUI($this);
                $this->showTree(false, $scat_gui, "listItems");
                $ret = $this->ctrl->forwardCommand($scat_gui);
                break;

            case 'ilbasicskillgui':
                $this->tabs_gui->activateTab("skills");
                $skill_gui = new ilBasicSkillGUI(
                    $this->skill_tree_node_manager,
                    (int) $this->requested_obj_id
                );
                $skill_gui->setParentGUI($this);
                $this->showTree(false, $skill_gui, "edit");
                $ret = $this->ctrl->forwardCommand($skill_gui);
                break;

            case 'ilskilltemplatecategorygui':
                $this->tabs_gui->activateTab("skill_templates");
                $sctp_gui = new ilSkillTemplateCategoryGUI(
                    $this->skill_tree_node_manager,
                    (int) $this->requested_obj_id,
                    (int) $_GET["tref_id"]
                );
                $sctp_gui->setParentGUI($this);
                $this->showTree(((int) $_GET["tref_id"] == 0), $sctp_gui, "listItems");
                $ret = $this->ctrl->forwardCommand($sctp_gui);
                break;

            case 'ilbasicskilltemplategui':
                $this->tabs_gui->activateTab("skill_templates");
                $sktp_gui = new ilBasicSkillTemplateGUI(
                    $this->skill_tree_node_manager,
                    (int) $this->requested_obj_id,
                    (int) $_GET["tref_id"]
                );
                $sktp_gui->setParentGUI($this);
                $this->showTree(((int) $_GET["tref_id"] == 0), $sktp_gui, "edit");
                $ret = $this->ctrl->forwardCommand($sktp_gui);
                break;

            case 'ilskilltemplatereferencegui':
                $this->tabs_gui->activateTab("skills");
                $sktr_gui = new ilSkillTemplateReferenceGUI(
                    $this->skill_tree_node_manager,
                    (int) $_GET["tref_id"]
                );
                $sktr_gui->setParentGUI($this);
                $this->showTree(false, $sktr_gui, "listItems");
                $ret = $this->ctrl->forwardCommand($sktr_gui);
                break;

            case "ilskillprofilegui":
                if (!$this->skill_tree_access_manager->hasReadProfilesPermission()) {
                    return;
                }
                $ilTabs->activateTab("profiles");
                $skprof_gui = new ilSkillProfileGUI(
                    $this->skill_tree_access_manager,
                    $this->skill_tree->getTreeId()
                );
                $ret = $this->ctrl->forwardCommand($skprof_gui);
                break;

            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('permissions');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilexportgui":
                $this->tabs_gui->activateTab('export');
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                //$exp_gui->addFormat("html", "", $this, "exportHTML");
                $this->object->setType("skmg");
                $ret = $this->ctrl->forwardCommand($exp_gui);
                $this->object->setType("skee");
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSkills";
                }

                if ($cmd == "showTree") {
                    $this->showTree($_GET["templates_tree"]);
                } else {
                    $this->$cmd();
                }
                break;
        }
        return true;
    }

    /**
     * Show locator
     * @param
     * @return
     */
    protected function showLocator()
    {
        $ctrl = $this->ctrl;
        $locator = $this->locator;

        $locator->clearItems();

        $ctrl->setParameterByClass(
            "ilobjsystemfoldergui",
            "ref_id",
            SYSTEM_FOLDER_ID
        );
        $locator->addItem(
            $this->lng->txt("administration"),
            $this->ctrl->getLinkTargetByClass(array("iladministrationgui", "ilobjsystemfoldergui"), "")
        );
        $locator->addItem(
            $this->lng->txt("obj_skmg"),
            $this->getSkillManagementLink()
        );

        /*
        if ($this->object && ($this->object->getRefId() != SYSTEM_FOLDER_ID && !$a_do_not_add_object)) {
            $ilLocator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this, "view")
            );
        }*/
    }

    /**
     * Get skill management link
     * @return string
     */
    protected function getSkillManagementLink() : string
    {
        $this->ctrl->setParameterByClass(
            "ilobjskillmanagementgui",
            "ref_id",
            $this->skill_tree_manager->getSkillManagementRefId()
        );
        return $this->ctrl->getLinkTargetByClass("ilobjskillmanagementgui", "");
    }

    /**
     * Create skill tree
     */
    protected function create()
    {
        $lng = $this->lng;
        $tabs = $this->tabs;
        $mtpl = $this->main_tpl;
        $ui = $this->ui;

        $tabs->clearTargets();
        $tabs->setBackTarget(
            $lng->txt("back"),
            $this->getSkillManagementLink()
        );

        $mtpl->setContent($ui->renderer()->render($this->initTreeForm()));
    }

    /**
     * Edit skill tree
     */
    protected function edit()
    {
        $tabs = $this->tabs;
        $tabs->activateTab("settings");
        $mtpl = $this->main_tpl;
        $ui = $this->ui;

        $mtpl->setContent($ui->renderer()->render($this->initTreeForm(true)));
    }

    /**
     * Init tree form.
     * @return Form\Standard
     */
    public function initTreeForm(bool $edit = false) : Form\Standard
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $fields["title"] = $f->input()->field()->text($lng->txt("title"))->withRequired(true);
        if ($edit) {
            $fields["title"] = $fields["title"]->withValue($this->object->getTitle());
        }

        $fields["description"] = $f->input()->field()->textarea($lng->txt("description"));
        if ($edit) {
            $fields["description"] = $fields["description"]->withValue($this->object->getDescription());
        }

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("skmg_comp_tree"));

        if ($edit) {
            $form_action = $ctrl->getLinkTarget($this, "update");
        } else {
            $form_action = $ctrl->getLinkTarget($this, "save");
        }
        return $f->input()->container()->form()->standard($form_action, ["props" => $section1]);
    }

    /**
     * Create tree
     */
    public function save()
    {
        $request = $this->request;
        $form = $this->initTreeForm();
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;
        $tpl = $this->tpl;
        $ui = $this->ui;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (is_array($data["props"])) {
                $props = $data["props"];
                $this->skill_tree_manager->createTree(
                    $props["title"],
                    $props["description"]
                );
                ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            } else {
                $tpl->setContent($ui->renderer()->render($form));
                $tabs->clearTargets();
                $tabs->setBackTarget(
                    $lng->txt("back"),
                    $this->getSkillManagementLink()
                );
                return;
            }
        }
        $ctrl->redirectByClass("ilskillrootgui", "listSkills");
    }

    /**
     * Update tree
     */
    public function update()
    {
        $request = $this->request;
        $form = $this->initTreeForm();
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (is_array($data["props"])) {
                $props = $data["props"];
                $this->skill_tree_manager->updateTree(
                    $this->object,
                    $props["title"],
                    $props["description"]
                );
                ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            }
        }
        $ctrl->redirect($this, "edit");
    }

    public function delete()
    {
        $ctrl = $this->ctrl;

        $this->deleteNodes($this);
    }


    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;
        $lng = $this->lng;


        $this->tabs_gui->setBackTarget(
            $lng->txt("skmg_skill_trees"),
            $this->getSkillManagementLink()
        );

        if ($this->skill_tree_access_manager->hasReadTreePermission()) {
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

            if ($this->skill_tree_access_manager->hasReadProfilesPermission()) {
                $this->tabs_gui->addTab(
                    "profiles",
                    $lng->txt("skmg_skill_profiles"),
                    $this->ctrl->getLinkTargetByClass("ilskillprofilegui")
                );
            }

            if($this->skill_tree_access_manager->hasEditTreeSettingsPermission()) {
                $this->tabs_gui->addTab(
                    "settings",
                    $lng->txt("settings"),
                    $this->ctrl->getLinkTarget($this, "edit")
                );
            }

            if ($this->skill_tree_access_manager->hasEditTreeSettingsPermission()) {
                $this->tabs_gui->addTab(
                    "export",
                    $lng->txt("export"),
                    $this->ctrl->getLinkTargetByClass("ilexportgui", "")
                );
            }

            /*
            if (DEVMODE == 1) {
                $this->tabs_gui->addTab(
                    "test",
                    "Test (DEVMODE)",
                    $this->ctrl->getLinkTarget($this, "test")
                );
            }*/
        }

        if ($this->skill_tree_access_manager->hasEditTreePermissionsPermission()) {
            $this->tabs_gui->addTab(
                "permissions",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    /**
     * Edit skills
     */
    public function editSkills()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->activateTab("skills");

        $ilCtrl->setParameterByClass("ilobjskilltreegui", "obj_id", $this->skill_tree->readRootId());
        $ilCtrl->redirectByClass("ilskillrootgui", "listSkills");
    }


    /**
     * Save all titles of chapters/scos/pages
     */
    public function saveAllTitles($a_succ_mess = true)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (is_array($_POST["title"])) {
            foreach ($_POST["title"] as $id => $title) {
                $node_obj = ilSkillTreeNodeFactory::getInstance($id);
                if (is_object($node_obj)) {
                    // update title
                    ilSkillTreeNode::_writeTitle($id, ilUtil::stripSlashes($title));
                }
            }
            if ($a_succ_mess) {
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            }
        }
        $ilCtrl->redirect($this, "editSkills");
    }

    /**
     * Save all titles of chapters/scos/pages
     */
    public function saveAllTemplateTitles($a_succ_mess = true)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (is_array($_POST["title"])) {
            foreach ($_POST["title"] as $id => $title) {
                $node_obj = ilSkillTreeNodeFactory::getInstance($id);
                if (is_object($node_obj)) {
                    // update title
                    ilSkillTreeNode::_writeTitle($id, ilUtil::stripSlashes($title));
                }
            }
            if ($a_succ_mess) {
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            }
        }
        $ilCtrl->redirect($this, "editSkillTemplates");
    }


    /**
     * Expand all
     */
    public function expandAll($a_redirect = true)
    {
        $_GET["skexpand"] = "";
        $n_id = ($this->requested_obj_id != "")
            ? $this->requested_obj_id
            : $this->skill_tree->readRootId();
        $stree = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($n_id));
        $n_arr = array();
        foreach ($stree as $n) {
            $n_arr[] = $n["child"];
            $_SESSION["skexpand"] = $n_arr;
        }
        $this->saveAllTitles(false);
    }

    /**
     * Collapse all
     */
    public function collapseAll($a_redirect = true)
    {
        $_GET["skexpand"] = "";
        $n_id = ($this->requested_obj_id != "")
            ? $this->requested_obj_id
            : $this->skill_tree->readRootId();
        $stree = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($n_id));
        $old = $_SESSION["skexpand"];
        foreach ($stree as $n) {
            if (in_array($n["child"], $old) && $n["child"] != $n_id) {
                $k = array_search($n["child"], $old);
                unset($old[$k]);
            }
        }
        $_SESSION["skexpand"] = $old;
        $this->saveAllTitles(false);
    }

    /**
     * confirm deletion screen of skill tree nodes
     */
    public function deleteNodes($a_gui)
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;

        if (!isset($_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        $ilTabs->clearTargets();

        // check usages
        $mode = "";
        $tree_ids = array();
        $cskill_ids = array();
        foreach ($_POST["id"] as $id) {
            if (in_array(ilSkillTreeNode::_lookupType($id), array("skrt"))) {
                if (!$this->skill_management_access_manager->hasCreateTreePermission()) {
                    return;
                }
                $mode = "tree";
                $tree_node_id = $id;
                $tree_ids[] = $tree_node_id;
            }
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

        if ($mode == "tree") {
            $u = new ilSkillUsage();
            $usages = $u->getAllUsagesInfoOfTrees($tree_ids);
            //var_dump($usages); exit;
        }
        elseif ($mode == "basic" || $mode == "templates") {
            $u = new ilSkillUsage();
            $usages = $u->getAllUsagesInfoOfSubtrees($cskill_ids);
        } else {
            $this->ilias->raiseError("Skill Deletion - type mismatch.", $this->ilias->error_obj->MESSAGE);
        }

        if (count($usages) > 0) {
            $html = "";
            foreach ($usages as $k => $usage) {
                $tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage, $mode);
                $html .= $tab->getHTML() . "<br/><br/>";
            }
            $tpl->setContent($html);
            $ilCtrl->saveParameter($a_gui, "tmpmode");
            $ilToolbar->addButton(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($a_gui, "cancelDelete")
            );
            ilUtil::sendFailure($lng->txt("skmg_cannot_delete_nodes_in_use"));
            return;
        }

        // SAVE POST VALUES
        $_SESSION["saved_post"] = $_POST["id"];

        $confirmation_gui = new ilConfirmationGUI();

        $ilCtrl->setParameter($a_gui, "tmpmode", $_GET["tmpmode"]);
        $a_form_action = $this->ctrl->getFormAction($a_gui);
        $confirmation_gui->setFormAction($a_form_action);
        $confirmation_gui->setHeaderText($this->lng->txt("info_delete_sure"));

        // Add items to delete
        foreach ($_POST["id"] as $id) {
            if ($id != IL_FIRST_NODE) {
                $node_obj = ilSkillTreeNodeFactory::getInstance($id);
                if ($mode == "tree") {
                    $tree_id = $this->skill_tree_repo->getTreeIdForNodeId($id);
                    $tree_obj = $this->skill_tree_manager->getTree($tree_id);
                    $obj_title = $tree_obj->getTitle();
                } else {
                    $obj_title = $node_obj->getTitle();
                }
                $confirmation_gui->addItem(
                    "id[]",
                    $node_obj->getId(),
                    $obj_title,
                    ilUtil::getImagePath("icon_" . $node_obj->getType() . ".svg")
                );
            }
        }

        $confirmation_gui->setCancel($lng->txt("cancel"), "cancelDelete");
        if ($mode == "tree") {
            $confirmation_gui->setConfirm($lng->txt("confirm"), "confirmedDeleteTrees");
        } else {
            $confirmation_gui->setConfirm($lng->txt("confirm"), "confirmedDelete");
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }

    /**
     * cancel delete
     */
    public function cancelDelete()
    {
        $this->ctrl->redirectByClass("ilobjskillmanagementgui", "");
    }

    /**
     * Delete tree
     */
    public function confirmedDeleteTrees()
    {
        $ctrl = $this->ctrl;

        // delete all selected trees
        foreach ($_POST["id"] as $id) {
            if ($id != IL_FIRST_NODE) {
                $obj = ilSkillTreeNodeFactory::getInstance($id);
                $tree = $this->skill_tree_repo->getTreeForNodeId($id);
                $tree_obj = $this->skill_tree_manager->getTree($tree->getTreeId());
                $node_data = $tree->getNodeData($id);
                if (is_object($obj)) {
                    $obj->delete();
                }
                if ($tree->isInTree($id)) {
                    $tree->deleteTree($node_data);
                }
                $this->skill_tree_manager->deleteTree($tree_obj);
            }
        }

        // feedback
        ilUtil::sendInfo($this->lng->txt("info_deleted"), true);
        $ctrl->redirectByClass("ilobjskillmanagementgui", "");
    }

    /**
     * Delete chapters/scos/pages
     */
    public function confirmedDelete()
    {
        // delete all selected objects
        foreach ($_POST["id"] as $id) {
            if ($id != IL_FIRST_NODE) {
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
        ilUtil::sendInfo($this->lng->txt("info_deleted"), true);
    }



    //
    // Skill Templates
    //

    /**
     * Edit skill templates
     */
    public function editSkillTemplates()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->activateTab("skill_templates");
        $ilCtrl->setParameterByClass("ilobjskilltreegui", "obj_id", $this->skill_tree->readRootId());
        $ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
    }

    //
    // Tree
    //

    /**
     * Show Editing Tree
     */
    public function showTree($a_templates, $a_gui = "", $a_gui_cmd = "")
    {
        $ilUser = $this->user;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($a_templates) {
            if ($this->requested_obj_id == "" || $this->requested_obj_id == $this->skill_tree->readRootId()) {
                return;
            }

            if ($this->requested_obj_id != $this->skill_tree->readRootId()) {
                $path = $this->skill_tree->getPathId($this->requested_obj_id);
                if (ilSkillTreeNode::_lookupType($path[1]) == "sktp") {
                    return;
                }
            }
        }

        $ilCtrl->setParameter($this, "templates_tree", $a_templates);

        if ($a_templates) {
            $this->tool_context->current()->addAdditionalData(ilSkillGSToolProvider::SHOW_TEMPLATE_TREE, true);
            $this->tool_context->current()->addAdditionalData(ilSkillGSToolProvider::SKILL_TREE_ID, $this->object->getId());
            $exp = new ilSkillTemplateTreeExplorerGUI($this, "showTree", $this->object->getId());
        } else {
            $this->tool_context->current()->addAdditionalData(ilSkillGSToolProvider::SHOW_SKILL_TREE, true);
            $this->tool_context->current()->addAdditionalData(ilSkillGSToolProvider::SKILL_TREE_ID, $this->object->getId());
            $exp = new ilSkillTreeExplorerGUI($this, "showTree", $this->object->getId());
        }
        if (!$exp->handleCommand()) {
            $tpl->setLeftNavContent($exp->getHTML());
        }
    }
}
