<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
 * Repository GUI Utilities
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesRepository
 *
 * @ilCtrl_Calls ilRepUtilGUI: ilPropertyFormGUI
 *
 */
class ilRepUtilGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTree
     */
    protected $tree;


    /**
     * @var null | \ilLogger
     */
    private $logger = null;


    /**
    * Constructor
    *
    * @param	object		parent gui object
    * @param	string		current parent command (like in table2gui)
    */
    public function __construct($a_parent_gui, $a_parent_cmd = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->obj_definition = $DIC["objDefinition"];
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->parent_gui = $a_parent_gui;
        $this->parent_cmd = $a_parent_cmd;

        $this->logger = $DIC->logger()->rep();
    }

    /**
     * @throws \ilCtrlException
     */
    public function executeCommand()
    {
        global $DIC;

        $logger = $DIC->logger()->rep();
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case "ilpropertyformgui":
                $form = $this->initFormTrashTargetLocation();
                $this->ctrl->forwardCommand($form);
                break;

            default:
                $cmd = $this->ctrl->getCmd('cancel');
                $this->$cmd();
                break;

        }
    }

    /**
     * Cancel action
     */
    protected function cancel()
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * @param \ilPropertyFormGUI|null $form
     * @return bool
     */
    public function restoreToNewLocation(\ilPropertyFormGUI $form = null)
    {
        $this->lng->loadLanguageModule('rep');

        if (isset($_POST['trash_id'])) {
            $trash_ids = (array) $_POST['trash_id'];
        } elseif (isset($_REQUEST['trash_ids'])) {
            $trash_ids = explode(',', $_POST['trash_ids']);
        }

        $this->ctrl->setParameter($this, 'trash_ids', implode(',', $trash_ids));

        if (!count($trash_ids)) {
            \ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        if (!$form instanceof \ilPropertyFormGUI) {
            $form = $this->initFormTrashTargetLocation();
        }
        \ilUtil::sendInfo($this->lng->txt('rep_target_location_info'));
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Perform restore to new location
     */
    public function doRestoreToNewLocation()
    {
        $trash_ids = [];
        if (isset($_REQUEST['trash_ids'])) {
            $trash_ids = explode(',', $_REQUEST['trash_ids']);
        }

        $form = $this->initFormTrashTargetLocation();
        if (!$form->checkInput() && count($trash_ids)) {
            $this->lng->loadLanguageModule('search');
            \ilUtil::sendFailure($this->lng->txt('search_no_selection'), true);
            $this->ctrl->returnToParent($this);
        }

        try {
            \ilRepUtil::restoreObjects($form->getInput('target_id'), $trash_ids);
            \ilUtil::sendSuccess($this->lng->txt('msg_undeleted'), true);
            $this->ctrl->returnToParent($this);
        } catch (\ilRepositoryException $e) {
            \ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->returnToParent($this);
        }
    }

    /**
     * @return \ilPropertyFormGUI
     */
    protected function initFormTrashTargetLocation()
    {
        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $target = new \ilRepositorySelector2InputGUI(
            $this->lng->txt('rep_target_location'),
            'target_id',
            false
        );
        $target->setRequired(true);

        $explorer = new \ilRepositorySelectorExplorerGUI(
            [
                \ilAdministrationGUI::class,
                get_class($this->parent_gui),
                \ilRepUtilGUI::class,
                \ilPropertyFormGUI::class,
                \ilFormPropertyDispatchGUI::class,
                \ilRepositorySelector2InputGUI::class
            ],
            'handleExplorerCommand',
            $target,
            'root_id',
            'rep_exp_sel_' . $target->getPostVar()
        );
        $explorer->setSelectMode($target->getPostVar() . "_sel", false);
        $explorer->setRootId(ROOT_FOLDER_ID);
        $explorer->setTypeWhiteList(['root','cat','crs','grp','fold']);
        $target->setExplorerGUI($explorer);

        $form->addItem($target);
        $form->addCommandButton('doRestoreToNewLocation', $this->lng->txt('btn_undelete'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }
    
    
    /**
    * Show delete confirmation table
    */
    public function showDeleteConfirmation($a_ids, $a_supress_message = false)
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $objDefinition = $this->obj_definition;

        if (!is_array($a_ids) || count($a_ids) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            return false;
        }
        
        // Remove duplicate entries
        $a_ids = array_unique((array) $a_ids);

        $cgui = new ilConfirmationGUI();

        if (!$a_supress_message) {
            $msg = $lng->txt("info_delete_sure");
            
            if (!$ilSetting->get('enable_trash')) {
                $msg .= "<br/>" . $lng->txt("info_delete_warning_no_trash");
            }
            
            $cgui->setHeaderText($msg);
        }
        $cgui->setFormAction($ilCtrl->getFormAction($this->parent_gui));
        $cgui->setCancel($lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($lng->txt("confirm"), "confirmedDelete");
        
        $form_name = "cgui_" . md5(uniqid());
        $cgui->setFormName($form_name);

        $deps = array();
        foreach ($a_ids as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $type = ilObject::_lookupType($obj_id);
            $title = call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'), $obj_id);
            $alt = ($objDefinition->isPlugin($type))
                ? $lng->txt("icon") . " " . ilObjectPlugin::lookupTxtById($type, "obj_" . $type)
                : $lng->txt("icon") . " " . $lng->txt("obj_" . $type);
            
            $title .= $this->handleMultiReferences($obj_id, $ref_id, $form_name);
            
            $cgui->addItem(
                "id[]",
                $ref_id,
                $title,
                ilObject::_getIcon($obj_id, "small", $type),
                $alt
            );

            ilObject::collectDeletionDependencies($deps, $ref_id, $obj_id, $type);
        }
        $deps_html = "";

        if (is_array($deps) && count($deps) > 0) {
            include_once("./Services/Repository/classes/class.ilRepDependenciesTableGUI.php");
            $tab = new ilRepDependenciesTableGUI($deps);
            $deps_html = "<br/><br/>" . $tab->getHTML();
        }
        
        $tpl->setContent($cgui->getHTML() . $deps_html);
        return true;
    }
    
    /**
     * Build subitem list for multiple references
     *
     * @param int $a_obj_id
     * @param int $a_ref_id
     * @param string $a_form_name
     * @return string
     */
    public function handleMultiReferences($a_obj_id, $a_ref_id, $a_form_name)
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $tree = $this->tree;
                                
        // process
    
        $all_refs = ilObject::_getAllReferences($a_obj_id);
        if (sizeof($all_refs) > 1) {
            $lng->loadLanguageModule("rep");
            
            $may_delete_any = 0;
            $counter = 0;
            $items = array();
            foreach ($all_refs as $mref_id) {
                // not the already selected reference, no refs from trash
                if ($mref_id != $a_ref_id && !$tree->isDeleted($mref_id)) {
                    if ($ilAccess->checkAccess("read", "", $mref_id)) {
                        $may_delete = false;
                        if ($ilAccess->checkAccess("delete", "", $mref_id)) {
                            $may_delete = true;
                            $may_delete_any++;
                        }
                                                
                        $items[] = array("id" => $mref_id,
                            "path" => array_shift($this->buildPath(array($mref_id))),
                            "delete" => $may_delete);
                    } else {
                        $counter++;
                    }
                }
            }

            
            // render

            $tpl = new ilTemplate("tpl.rep_multi_ref.html", true, true, "Services/Repository");

            $tpl->setVariable("TXT_INTRO", $lng->txt("rep_multiple_reference_deletion_intro"));
            
            if ($may_delete_any) {
                $tpl->setVariable("TXT_INSTRUCTION", $lng->txt("rep_multiple_reference_deletion_instruction"));
            }
            
            if ($items) {
                $var_name = "mref_id[]";
                
                foreach ($items as $item) {
                    if ($item["delete"]) {
                        $tpl->setCurrentBlock("cbox");
                        $tpl->setVariable("ITEM_NAME", $var_name);
                        $tpl->setVariable("ITEM_VALUE", $item["id"]);
                        $tpl->parseCurrentBlock();
                    } else {
                        $tpl->setCurrentBlock("item_info");
                        $tpl->setVariable("TXT_ITEM_INFO", $lng->txt("rep_no_permission_to_delete"));
                        $tpl->parseCurrentBlock();
                    }
                    
                    $tpl->setCurrentBlock("item");
                    $tpl->setVariable("ITEM_TITLE", $item["path"]);
                    $tpl->parseCurrentBlock();
                }
                
                if ($may_delete_any > 1) {
                    $tpl->setCurrentBlock("cbox");
                    $tpl->setVariable("ITEM_NAME", "sall_" . $a_ref_id);
                    $tpl->setVariable("ITEM_VALUE", "");
                    $tpl->setVariable("ITEM_ADD", " onclick=\"il.Util.setChecked('" .
                        $a_form_name . "', '" . $var_name . "', document." . $a_form_name .
                        ".sall_" . $a_ref_id . ".checked)\"");
                    $tpl->parseCurrentBlock();
                    
                    $tpl->setCurrentBlock("item");
                    $tpl->setVariable("ITEM_TITLE", $lng->txt("select_all"));
                    $tpl->parseCurrentBlock();
                }
            }
            
            if ($counter) {
                $tpl->setCurrentBlock("add_info");
                $tpl->setVariable(
                    "TXT_ADDITIONAL_INFO",
                    sprintf($lng->txt("rep_object_references_cannot_be_read"), $counter)
                );
                $tpl->parseCurrentBlock();
            }

            return $tpl->get();
        }
    }
    
    /**
    * Get trashed objects for a container
    *
    * @param	interger	ref id of container
    */
    public function showTrashTable($a_ref_id)
    {
        $tpl = $this->tpl;
        $tree = $this->tree;
        $lng = $this->lng;
        
        $objects = $tree->getSavedNodeData($a_ref_id);
        
        if (count($objects) == 0) {
            ilUtil::sendInfo($lng->txt("msg_trash_empty"));
            return;
        }
        include_once("./Services/Repository/classes/class.ilTrashTableGUI.php");
        $ttab = new ilTrashTableGUI($this->parent_gui, "trash");
        $ttab->setData($objects);
        
        $tpl->setContent($ttab->getHTML());
    }

    /**
     * Restore objects from trash
     *
     * @param    int        current ref id
     * @param    int[]        array of ref ids to be restored
     */
    public function restoreObjects($a_cur_ref_id, $a_ref_ids)
    {
        $lng = $this->lng;
        $lng->loadLanguageModule('rep');
        
        if (!is_array($a_ref_ids) || count($a_ref_ids) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            return false;
        }

        $tree_trash_queries = new \ilTreeTrashQueries();
        if ($tree_trash_queries->isTrashedTrash($a_ref_ids)) {
            \ilUtil::sendFailure($this->lng->txt('rep_failure_trashed_trash'), true);
            return false;
        }
        try {

            // find parent foreach node
            $by_location = [];
            foreach ($a_ref_ids as $deleted_node_id) {
                $target_id = $tree_trash_queries->findRepositoryLocationForDeletedNode($deleted_node_id);
                if ($target_id) {
                    $by_location[$target_id][] = $deleted_node_id;
                }
            }
            foreach ($by_location as $target_id => $deleted_node_ids) {
                \ilRepUtil::restoreObjects($target_id, $deleted_node_ids);
            }
            ilUtil::sendSuccess($lng->txt("msg_undeleted"), true);
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            return false;
        }
        return true;
    }
    
    /**
    * Delete objects
    */
    public function deleteObjects($a_cur_ref_id, $a_ref_ids)
    {
        $ilSetting = $this->settings;
        $lng = $this->lng;
        
        if (!is_array($a_ref_ids) || count($a_ref_ids) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            return false;
        } else {
            include_once("./Services/Repository/classes/class.ilRepUtil.php");
            try {
                ilRepUtil::deleteObjects($a_cur_ref_id, $a_ref_ids);
                if ($ilSetting->get('enable_trash')) {
                    ilUtil::sendSuccess($lng->txt("info_deleted"), true);
                } else {
                    ilUtil::sendSuccess($lng->txt("msg_removed"), true);
                }
            } catch (Exception $e) {
                ilUtil::sendFailure($e->getMessage(), true);
                return false;
            }
        }
    }
    
    /**
    * Remove objects from system
    */
    public function removeObjectsFromSystem($a_ref_ids, $a_from_recovery_folder = false)
    {
        $lng = $this->lng;
        
        if (!is_array($a_ref_ids) || count($a_ref_ids) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            return false;
        } else {
            include_once("./Services/Repository/classes/class.ilRepUtil.php");
            try {
                ilRepUtil::removeObjectsFromSystem($a_ref_ids, $a_from_recovery_folder);
                ilUtil::sendSuccess($lng->txt("msg_removed"), true);
            } catch (Exception $e) {
                ilUtil::sendFailure($e->getMessage(), true);
                return false;
            }
        }

        return true;
    }
    
    /**
     * Build path with deep-link
     *
     * @param	array	$ref_ids
     * @return	array
     */
    protected function buildPath($ref_ids)
    {
        $tree = $this->tree;

        include_once 'Services/Link/classes/class.ilLink.php';
        
        if (!count($ref_ids)) {
            return false;
        }
        
        $result = array();
        foreach ($ref_ids as $ref_id) {
            $path = "";
            $path_full = $tree->getPathFull($ref_id);
            foreach ($path_full as $idx => $data) {
                if ($idx) {
                    $path .= " &raquo; ";
                }
                if ($ref_id != $data['ref_id']) {
                    $path .= $data['title'];
                } else {
                    $path .= ('<a target="_top" href="' .
                              ilLink::_getLink($data['ref_id'], $data['type']) . '">' .
                              $data['title'] . '</a>');
                }
            }

            $result[] = $path;
        }
        return $result;
    }

    /**
     * Confirmation for trash
     *
     * @param array $a_ids ref_ids
     */
    public function confirmRemoveFromSystemObject($a_ids)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;
        $tpl = $this->tpl;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ilCtrl->getFormAction($this->parent_gui));
        $cgui->setCancel($lng->txt("cancel"), "trash");
        $cgui->setConfirm($lng->txt("confirm"), "removeFromSystem");
        $cgui->setFormName("trash_confirmation");
        $cgui->setHeaderText($lng->txt("info_delete_sure"));

        foreach ($a_ids as $id) {
            $obj_id = ilObject::_lookupObjId($id);
            $type = ilObject::_lookupType($obj_id);
            $title = call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'), $obj_id);
            $alt = ($objDefinition->isPlugin($type))
                ? $lng->txt("icon") . " " . ilObjectPlugin::lookupTxtById($type, "obj_" . $type)
                : $lng->txt("icon") . " " . $lng->txt("obj_" . $type);

            $cgui->addItem(
                "trash_id[]",
                $id,
                $title,
                ilObject::_getIcon($obj_id, "small", $type),
                $alt
            );
        }

        $tpl->setContent($cgui->getHTML());
    }
}
