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

use ILIAS\Skill\Service\SkillAdminGUIRequest;
use ILIAS\Skill\Tree;
use ILIAS\Skill\Access\SkillTreeAccess;

/**
 * Basic GUI class for skill tree nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTreeNodeGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilLocatorGUI $locator;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;
    protected ilTree $tree;
    protected ilPropertyFormGUI $form;
    protected object $parentgui;
    public ?object $node_object = null;
    protected int $tref_id = 0;
    public bool $in_use = false;
    public bool $use_checked = false;
    public ilAccessHandler $access;
    protected Tree\SkillTreeNodeManager $skill_tree_node_manager;
    protected SkillTreeAccess $tree_access_manager;
    protected ilSkillTreeRepository $tree_repo;
    protected int $skill_tree_id = 0;
    protected ilTabsGUI $tabs;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_ref_id = 0;
    protected int $requested_node_id = 0;
    protected string $requested_backcmd = "";
    protected int $requested_tmpmode = 0;
    protected int $base_skill_id = 0;

    /**
     * @var int[]
     */
    protected array $requested_node_ids = [];

    /**
     * @var int[]
     */
    protected array $requested_node_order = [];

    public function __construct(Tree\SkillTreeNodeManager $node_manager, int $a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->locator = $DIC["ilLocator"];
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();
        $ilAccess = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->tabs = $DIC->tabs();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();

        $this->node_object = null;
        $this->access = $ilAccess;

        $this->requested_ref_id = $this->admin_gui_request->getRefId();
        $this->requested_node_id = $this->admin_gui_request->getNodeId();
        $this->requested_backcmd = $this->admin_gui_request->getBackCommand();
        $this->requested_tmpmode = $this->admin_gui_request->getTemplateMode();
        $this->requested_node_ids = $this->admin_gui_request->getNodeIds();
        $this->requested_node_order = $this->admin_gui_request->getOrder();

        $this->skill_tree_node_manager = $node_manager;
        $this->tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager($this->requested_ref_id);
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->skill_tree_id = $this->tree_repo->getTreeIdForNodeId($this->requested_node_id);

        if ($a_node_id > 0 &&
            $this->getType() == ilSkillTreeNode::_lookupType($a_node_id)) {
            $this->readNodeObject($a_node_id);
        }
    }

    public function isInUse() : bool
    {
        if (!is_object($this->node_object)) {
            return false;
        }
        if ($this->use_checked) {
            return $this->in_use;
        }
        $cskill_ids = ilSkillTreeNode::getAllCSkillIdsForNodeIds(array($this->node_object->getId()));
        $u = new ilSkillUsage();
        $usages = $u->getAllUsagesInfoOfSubtrees($cskill_ids);
        if (count($usages) > 0) {
            $this->in_use = true;
        } else {
            $this->in_use = false;
        }
        return $this->in_use;
    }

    public function setParentGUI(object $a_parentgui) : void
    {
        $this->parentgui = $a_parentgui;
    }

    public function getParentGUI() : object
    {
        return $this->parentgui;
    }

    /**
     * Get node object instance
     */
    public function readNodeObject(int $a_node_id) : void
    {
        $this->node_object = ilSkillTreeNodeFactory::getInstance($a_node_id);
    }

    public function saveAllTitles() : void
    {
        $ilCtrl = $this->ctrl;
        
        $this->getParentGUI()->saveAllTitles(false);
        $ilCtrl->redirect($this, "showOrganization");
    }

    /**
     * Delete nodes in the hierarchy
     */
    public function deleteNodes() : void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "backcmd", $this->requested_backcmd);
        $this->getParentGUI()->deleteNodes($this);
    }

    /**
     * Copy items to clipboard, then cut them from the current tree
     */
    public function cutItems() : void
    {
        $lng = $this->lng;

        if (empty($this->requested_node_ids)) {
            $this->redirectToParent();
        }

        $items = $this->requested_node_ids;
        $todel = [];			// delete IDs < 0 (needed for non-js editing)
        foreach ($items as $k => $item) {
            if ($item < 0) {
                $todel[] = $k;
            }
        }
        foreach ($todel as $k) {
            unset($items[$k]);
        }

        if (!ilSkillTreeNode::uniqueTypesCheck($items)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_insert_please_choose_one_type_only"), true);
            $this->redirectToParent();
        }

        $this->skill_tree_node_manager->clipboardCut($items);

        $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_selected_items_have_been_cut"), true);

        $this->skill_tree_node_manager->saveChildsOrder(
            $this->requested_node_id,
            [],
            $this->requested_tmpmode
        );

        $this->redirectToParent();
    }

    /**
     * Copy items to clipboard
     */
    public function copyItems() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (empty($this->requested_node_ids)) {
            $this->redirectToParent();
        }

        $items = $this->requested_node_ids;
        $todel = [];				// delete IDs < 0 (needed for non-js editing)
        foreach ($items as $k => $item) {
            if ($item < 0) {
                $todel[] = $k;
            }
        }
        foreach ($todel as $k) {
            unset($items[$k]);
        }
        if (!ilSkillTreeNode::uniqueTypesCheck($items)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_insert_please_choose_one_type_only"), true);
            $this->redirectToParent();
        }
        $this->skill_tree_node_manager->clipboardCopy($items);

        $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_selected_items_have_been_copied"), true);

        $this->redirectToParent();
    }

    public function cancelDelete() : void
    {
        $ilCtrl = $this->ctrl;
        
        $this->redirectToParent();
    }

    /**
     * confirmed delete
     */
    public function confirmedDelete() : void
    {
        $ilCtrl = $this->ctrl;

        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $this->getParentGUI()->confirmedDelete(false);
        $this->skill_tree_node_manager->saveChildsOrder(
            $this->requested_node_id,
            [],
            $this->requested_tmpmode
        );

        $this->redirectToParent();
    }

    public function setSkillNodeDescription() : void
    {
        $tpl = $this->tpl;

        $tpl->setDescription($this->skill_tree_node_manager->getWrittenPath($this->node_object->getId(), $this->tref_id));
    }

    /**
     * Create skill tree node
     */
    public function create() : void
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $tabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $tabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "redirectToParent")
        );
        
        $this->initForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    public function addStatusInput(ilPropertyFormGUI $a_form) : void
    {
        $lng = $this->lng;

        // status
        $radg = new ilRadioGroupInputGUI($lng->txt("skmg_status"), "status");
        foreach (ilSkillTreeNode::getAllStatus() as $k => $op) {
            $op = new ilRadioOption($op, $k, ilSkillTreeNode::getStatusInfo($k));
            $radg->addOption($op);
        }
        $radg->setValue((string) ilSkillTreeNode::STATUS_PUBLISH);
        $a_form->addItem($radg);
    }

    public function editProperties() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if ($this->isInUse()) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_skill_in_use"));
        }

        $this->initForm("edit");
        $this->getPropertyValues();
        $tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Get property values for edit form
     */
    public function getPropertyValues() : void
    {
        $values = [];
        
        $values["title"] = $this->node_object->getTitle();
        $values["description"] = $this->node_object->getDescription();
        $values["order_nr"] = $this->node_object->getOrderNr();
        $values["self_eval"] = $this->node_object->getSelfEvaluation();
        $values["status"] = (string) $this->node_object->getStatus();
        
        $this->form->setValuesByArray($values);
    }
    
    /**
     * Save skill tree node
     *
     */
    public function save() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $this->initForm("create");
        if ($this->form->checkInput()) {
            $this->saveItem();
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $this->skill_tree_node_manager->saveChildsOrder(
                $this->requested_node_id,
                [],
                in_array($this->getType(), array("sktp", "sctp"))
            );
            $this->afterSave();
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
        }
    }

    public function afterSave() : void
    {
        $this->redirectToParent();
    }
    
    
    /**
     * Update skill tree node
     *
     */
    public function update() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $this->initForm("edit");
        if ($this->form->checkInput()) {
            $this->updateItem();
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $this->afterUpdate();
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
        }
    }

    public function afterUpdate() : void
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirect($this, "editProperties");
    }

    public function initForm(string $a_mode = "edit") : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form = new ilPropertyFormGUI();
    
        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(200);
        $ti->setSize(50);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($lng->txt("description"), "description");
        $ta->setRows(5);
        $this->form->addItem($ta);
        
        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("save", $lng->txt("save"));
            $this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
            $this->form->setTitle($lng->txt("skmg_create_" . $this->getType()));
        } else {
            $this->form->addCommandButton("update", $lng->txt("save"));
            $this->form->setTitle($lng->txt("skmg_edit_" . $this->getType()));
        }
        
        $ilCtrl->setParameter($this, "node_id", $this->requested_node_id);
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function cancelSave() : void
    {
        $this->redirectToParent();
    }

    /**
     * Redirect to parent (identified by current node_id)
     */
    public function redirectToParent(bool $a_tmp_mode = false) : void
    {
        $ilCtrl = $this->ctrl;
        
        if ($this->requested_tmpmode) {
            $a_tmp_mode = true;
        }
        
        $t = ilSkillTreeNode::_lookupType($this->requested_node_id);

        switch ($t) {
            case "skrt":
                $ilCtrl->setParameterByClass("ilskillrootgui", "node_id", $this->requested_node_id);
                if ($a_tmp_mode) {
                    $ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
                } else {
                    $ilCtrl->redirectByClass("ilskillrootgui", "listSkills");
                }
                break;

            case "sctp":
                $ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "node_id", $this->requested_node_id);
                $ilCtrl->redirectByClass("ilskilltemplatecategorygui", "listItems");
                break;

            case "scat":
                $ilCtrl->setParameterByClass("ilskillcategorygui", "node_id", $this->requested_node_id);
                $ilCtrl->redirectByClass("ilskillcategorygui", "listItems");
                break;
        }
    }

    public function saveOrder() : void
    {
        $lng = $this->lng;

        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $this->skill_tree_node_manager->saveChildsOrder(
            $this->requested_node_id,
            $this->requested_node_order,
            $this->requested_tmpmode
        );
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $this->redirectToParent($this->requested_tmpmode);
    }

    public function insertBasicSkillClip() : void
    {
        $this->skill_tree_node_manager->insertItemsFromClip("skll", $this->requested_node_id);
        $this->redirectToParent();
    }

    public function insertSkillCategoryClip() : void
    {
        $this->skill_tree_node_manager->insertItemsFromClip("scat", $this->requested_node_id);
        $this->redirectToParent();
    }

    public function insertTemplateReferenceClip() : void
    {
        $this->skill_tree_node_manager->insertItemsFromClip("sktr", $this->requested_node_id);
        $this->redirectToParent();
    }

    public function insertSkillTemplateClip() : void
    {
        $this->skill_tree_node_manager->insertItemsFromClip("sktp", $this->requested_node_id);
        $this->redirectToParent();
    }

    public function insertTemplateCategoryClip() : void
    {
        $this->skill_tree_node_manager->insertItemsFromClip("sctp", $this->requested_node_id);
        $this->redirectToParent();
    }

    public function setTitleIcon() : void
    {
        $tpl = $this->tpl;
        
        $obj_id = (is_object($this->node_object))
            ? $this->node_object->getId()
            :0;
        $tpl->setTitleIcon(
            ilSkillTreeNode::getIconPath(
                $obj_id,
                $this->getType(),
                "",
                (ilSkillTreeNode::_lookupStatus($obj_id) == ilSkillTreeNode::STATUS_DRAFT)
            )
        );
    }

    ////
    //// Usage
    ////

    public function addUsageTab(ilTabsGUI $a_tabs) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $a_tabs->addTab(
            "usage",
            $lng->txt("skmg_usage"),
            $ilCtrl->getLinkTarget($this, "showUsage")
        );
    }

    public function showUsage() : void
    {
        $tpl = $this->tpl;

        $this->setTabs("usage");

        $usage_info = new ilSkillUsage();
        $base_skill_id = ($this->base_skill_id > 0)
            ? $this->base_skill_id
            : $this->node_object->getId();
        $usages = $usage_info->getAllUsagesInfoOfSubtree($base_skill_id, $this->tref_id);

        $html = "";
        foreach ($usages as $k => $usage) {
            $tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage);
            $html .= $tab->getHTML() . "<br/><br/>";
        }

        $tpl->setContent($html);
    }

    public function addObjectsTab(ilTabsGUI $a_tabs) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $a_tabs->addTab(
            "objects",
            $lng->txt("skmg_assigned_objects"),
            $ilCtrl->getLinkTarget($this, "showObjects")
        );
    }

    public function showObjects() : void
    {
        $tpl = $this->tpl;

        $this->setTabs("objects");

        $base_skill_id = ($this->base_skill_id > 0)
            ? $this->base_skill_id
            : $this->node_object->getId();
        $usage_info = new ilSkillUsage();
        $objects = $usage_info->getAssignedObjectsForSkill($base_skill_id, $this->tref_id);

        $tab = new ilSkillAssignedObjectsTableGUI($this, "showObjects", $objects);

        $tpl->setContent($tab->getHTML());
    }

    public function exportSelectedNodes() : void
    {
        $ilCtrl = $this->ctrl;

        if (empty($this->requested_node_ids)) {
            $this->redirectToParent();
        }

        $exp = new ilExport();
        $conf = $exp->getConfig("Services/Skill");
        $conf->setSelectedNodes($this->requested_node_ids);
        $conf->setSkillTreeId($this->skill_tree_id);
        $exp->exportObject("skmg", ilObject::_lookupObjId($this->requested_ref_id));

        $ilCtrl->redirectByClass(array("ilobjskilltreegui", "ilexportgui"), "");
    }
}
