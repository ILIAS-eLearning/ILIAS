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

use Psr\Http\Message\ServerRequestInterface;

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
    /**
     * @var null|object
     */
    public $node_object;
    public bool $in_use = false;
    public bool $use_checked = false;
    public ilAccessHandler $access;
    protected ServerRequestInterface $request;
    protected int $requested_ref_id;
    protected int $requested_obj_id;
    protected string $requested_backcmd;
    protected int $requested_tmpmode;

    public function __construct(int $a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->locator = $DIC["ilLocator"];
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();
        $ilAccess = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->request = $DIC->http()->request();

        $this->node_object = null;
        $this->access = $ilAccess;

        $params = $this->request->getQueryParams();
        $this->requested_ref_id = (int) ($params["ref_id"] ?? 0);
        $this->requested_obj_id = (int) ($params["obj_id"] ?? 0);
        $this->requested_backcmd = (string) ($params["backcmd"] ?? "");
        $this->requested_tmpmode = (int) ($params["tmpmode"] ?? 0);

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

    public function checkPermissionBool(string $a_perm) : bool
    {
        return $this->access->checkAccess($a_perm, "", $this->requested_ref_id);
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

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            $this->redirectToParent();
        }
        
        $items = ilUtil::stripSlashesArray($_POST["id"]);
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
            ilUtil::sendInfo($lng->txt("skmg_insert_please_choose_one_type_only"), true);
            $this->redirectToParent();
        }

        ilSkillTreeNode::clipboardCut(1, $items);

        ilEditClipboard::setAction("cut");

        ilUtil::sendInfo($lng->txt("skmg_selected_items_have_been_cut"), true);
        
        ilSkillTreeNode::saveChildsOrder(
            $this->requested_obj_id,
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

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            $this->redirectToParent();
        }

        $items = ilUtil::stripSlashesArray($_POST["id"]);
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
            ilUtil::sendInfo($lng->txt("skmg_insert_please_choose_one_type_only"), true);
            $this->redirectToParent();
        }
        ilSkillTreeNode::clipboardCopy(1, $items);

        // @todo: move this to a service since it can be used here, too
        ilEditClipboard::setAction("copy");
        ilUtil::sendInfo($lng->txt("skmg_selected_items_have_been_copied"), true);

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

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $this->getParentGUI()->confirmedDelete(false);
        ilSkillTreeNode::saveChildsOrder(
            $this->requested_obj_id,
            [],
            $this->requested_tmpmode
        );

        $this->redirectToParent();
    }

    public function setLocator() : void
    {
        $ilLocator = $this->locator;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        
        $ilLocator->addRepositoryItems($this->requested_ref_id);
        $this->getParentGUI()->addLocatorItems();
        
        if ($this->requested_obj_id > 0) {
            $tree = new ilSkillTree();
            $path = $tree->getPathFull($this->requested_obj_id);
            for ($i = 1; $i < count($path); $i++) {
                switch ($path[$i]["type"]) {
                    case "scat":
                        $ilCtrl->setParameterByClass(
                            "ilskillcategorygui",
                            "obj_id",
                            $path[$i]["child"]
                        );
                        $ilLocator->addItem(
                            $path[$i]["title"],
                            $ilCtrl->getLinkTargetByClass(
                                "ilskillmanagementgui",
                                "ilskillcategorygui"
                            ),
                            "",
                            0,
                            $path[$i]["type"]
                        );
                        break;

                    case "skll":
                        $ilCtrl->setParameterByClass(
                            "ilbasicskillgui",
                            "obj_id",
                            $path[$i]["child"]
                        );
                        $ilLocator->addItem(
                            $path[$i]["title"],
                            $ilCtrl->getLinkTargetByClass(
                                "ilskillmanagementgui",
                                "ilbasicskillgui"
                            ),
                            "",
                            0,
                            $path[$i]["type"]
                        );
                        break;
                        
                }
            }
        }
        $ilCtrl->setParameter($this, "obj_id", $this->requested_obj_id);
        
        $tpl->setLocator();
    }

    public function setSkillNodeDescription() : void
    {
        $tpl = $this->tpl;

        $desc = "";
        if (is_object($this->node_object)) {
            $tree = new ilSkillTree();
            $path = $this->node_object->getSkillTree()->getSkillTreePath(
                $this->node_object->getId(),
                $this->tref_id
            );
            $sep = "";
            foreach ($path as $p) {
                if (in_array($p["type"], array("scat", "skll", "sktr"))) {
                    $desc .= $sep . $p["title"];
                    $sep = " > ";
                }
            }
        }
        $tpl->setDescription($desc);
    }

    /**
     * Create skill tree node
     */
    public function create() : void
    {
        $tpl = $this->tpl;
        
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
        $a_form->addItem($radg);
    }

    public function editProperties() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if ($this->isInUse()) {
            ilUtil::sendInfo($lng->txt("skmg_skill_in_use"));
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
        $values["status"] = $this->node_object->getStatus();
        
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

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $this->initForm("create");
        if ($this->form->checkInput()) {
            $this->saveItem();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            ilSkillTreeNode::saveChildsOrder(
                $this->requested_obj_id,
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

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $this->initForm("edit");
        if ($this->form->checkInput()) {
            $this->updateItem();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
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
        
        // order nr
        $ni = new ilNumberInputGUI($lng->txt("skmg_order_nr"), "order_nr");
        $ni->setInfo($lng->txt("skmg_order_nr_info"));
        $ni->setMaxLength(6);
        $ni->setSize(6);
        $ni->setRequired(true);
        if ($a_mode == "create") {
            $tree = new ilSkillTree();
            $max = $tree->getMaxOrderNr($this->requested_obj_id);
            $ni->setValue($max + 10);
        }
        $this->form->addItem($ni);
        
        // save and cancel commands
        if ($this->checkPermissionBool("write")) {
            if ($a_mode == "create") {
                $this->form->addCommandButton("save", $lng->txt("save"));
                $this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
                $this->form->setTitle($lng->txt("skmg_create_" . $this->getType()));
            } else {
                $this->form->addCommandButton("update", $lng->txt("save"));
                $this->form->setTitle($lng->txt("skmg_edit_" . $this->getType()));
            }
        }
        
        $ilCtrl->setParameter($this, "obj_id", $this->requested_obj_id);
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function cancelSave() : void
    {
        $this->redirectToParent();
    }

    /**
     * Redirect to parent (identified by current obj_id)
     */
    public function redirectToParent(bool $a_tmp_mode = false) : void
    {
        $ilCtrl = $this->ctrl;
        
        if ($this->requested_tmpmode) {
            $a_tmp_mode = true;
        }
        
        $t = ilSkillTreeNode::_lookupType($this->requested_obj_id);

        switch ($t) {
            case "skrt":
                $ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", $this->requested_obj_id);
                if ($a_tmp_mode) {
                    $ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
                } else {
                    $ilCtrl->redirectByClass("ilskillrootgui", "listSkills");
                }
                break;

            case "sctp":
                $ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $this->requested_obj_id);
                $ilCtrl->redirectByClass("ilskilltemplatecategorygui", "listItems");
                break;

            case "scat":
                $ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", $this->requested_obj_id);
                $ilCtrl->redirectByClass("ilskillcategorygui", "listItems");
                break;
        }
    }

    public function saveOrder() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        ilSkillTreeNode::saveChildsOrder(
            $this->requested_obj_id,
            $_POST["order"],
            $this->requested_tmpmode
        );
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $this->redirectToParent($this->requested_tmpmode);
    }

    /**
     * Insert basic skills from clipboard
     */
    public function insertBasicSkillClip() : void
    {
        $nodes = ilSkillTreeNode::insertItemsFromClip("skll", $this->requested_obj_id);
        $this->redirectToParent();
    }

    /**
     * Insert skill categories from clipboard
     */
    public function insertSkillCategoryClip() : void
    {
        $nodes = ilSkillTreeNode::insertItemsFromClip("scat", $this->requested_obj_id);
        $this->redirectToParent();
    }
    
    /**
     * Insert skill template references from clipboard
     */
    public function insertTemplateReferenceClip() : void
    {
        $nodes = ilSkillTreeNode::insertItemsFromClip("sktr", $this->requested_obj_id);
        $this->redirectToParent();
    }
    
    /**
     * Insert skill template from clipboard
     */
    public function insertSkillTemplateClip() : void
    {
        $nodes = ilSkillTreeNode::insertItemsFromClip("sktp", $this->requested_obj_id);
        $this->redirectToParent();
    }

    /**
     * Insert skill template category from clipboard
     */
    public function insertTemplateCategoryClip() : void
    {
        $nodes = ilSkillTreeNode::insertItemsFromClip("sctp", $this->requested_obj_id);
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
        $usages = $usage_info->getAllUsagesInfoOfSubtree($base_skill_id . ":" . $this->tref_id);

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

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            $this->redirectToParent();
        }

        $exp = new ilExport();
        $conf = $exp->getConfig("Services/Skill");
        $conf->setSelectedNodes($_POST["id"]);
        $exp->exportObject("skmg", ilObject::_lookupObjId($this->requested_ref_id));

        $ilCtrl->redirectByClass(array("iladministrationgui", "ilobjskillmanagementgui", "ilexportgui"), "");
    }
}
