<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* Class ilObjectOwnershipManagementGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjectOwnershipManagementGUI:
*/
class ilObjectOwnershipManagementGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilTree
     */
    protected $tree;

    protected $user_id; // [int]
    
    public function __construct($a_user_id = null)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $ilUser = $DIC->user();
        
        if ($a_user_id === null) {
            $a_user_id = $ilUser->getId();
        }
        $this->user_id = (int) $a_user_id;
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "listObjects";
                }
                $this->$cmd();
                break;
        }
        
        return true;
    }
    
    public function listObjects()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->obj_definition;

        $sel_type = '';

        $objects = ilObject::getAllOwnedRepositoryObjects($this->user_id);
        
        if (sizeof($objects)) {
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "listObjects"));
            
            include_once "Services/Form/classes/class.ilSelectInputGUI.php";
            $sel = new ilSelectInputGUI($lng->txt("type"), "type");
            $ilToolbar->addStickyItem($sel, true);
            
            include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
            $button = ilSubmitButton::getInstance();
            $button->setCaption("ok");
            $button->setCommand("listObjects");
            $ilToolbar->addStickyItem($button);

            $options = array();
            foreach (array_keys($objects) as $type) {
                // #11050
                if (!$objDefinition->isPlugin($type)) {
                    $options[$type] = $lng->txt("obj_" . $type);
                } else {
                    include_once("./Services/Component/classes/class.ilPlugin.php");
                    $options[$type] = ilObjectPlugin::lookupTxtById($type, "obj_" . $type);
                }
            }
            asort($options);
            $sel->setOptions($options);

            $sel_type = (string) $_REQUEST["type"];
            if ($sel_type) {
                $sel->setValue($sel_type);
            } else {
                $sel_type = array_keys($options);
                $sel_type = array_shift($sel_type);
            }
            $ilCtrl->setParameter($this, "type", $sel_type);
        }
        
        // #17751
        if (is_array($objects[$sel_type]) && sizeof($objects[$sel_type])) {
            ilObject::fixMissingTitles($sel_type, $objects[$sel_type]);
        }
        
        include_once "Services/Object/classes/class.ilObjectOwnershipManagementTableGUI.php";
        $tbl = new ilObjectOwnershipManagementTableGUI($this, "listObjects", $this->user_id, $objects[$sel_type]);
        $tpl->setContent($tbl->getHTML());
    }
    
    public function applyFilter()
    {
        include_once "Services/Object/classes/class.ilObjectOwnershipManagementTableGUI.php";
        $tbl = new ilObjectOwnershipManagementTableGUI($this, "listObjects", $this->user_id);
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        $this->listObjects();
    }
    
    public function resetFilter()
    {
        include_once "Services/Object/classes/class.ilObjectOwnershipManagementTableGUI.php";
        $tbl = new ilObjectOwnershipManagementTableGUI($this, "listObjects", $this->user_id);
        $tbl->resetOffset();
        $tbl->resetFilter();
        $this->listObjects();
    }
    
    protected function redirectParentCmd($a_ref_id, $a_cmd)
    {
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;
        
        $parent = $tree->getParentId($a_ref_id);
        $ilCtrl->setParameterByClass("ilRepositoryGUI", "ref_id", $parent);
        $ilCtrl->setParameterByClass("ilRepositoryGUI", "item_ref_id", $a_ref_id);
        $ilCtrl->setParameterByClass("ilRepositoryGUI", "cmd", $a_cmd);
        $ilCtrl->redirectByClass("ilRepositoryGUI");
    }
    
    protected function redirectCmd($a_ref_id, $a_class, $a_cmd = null)
    {
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $objDefinition = $this->obj_definition;
            
        $node = $tree->getNodeData($a_ref_id);
        $gui_class = "ilObj" . $objDefinition->getClassName($node["type"]) . "GUI";
        $path = array("ilRepositoryGUI", $gui_class, $a_class);
        
        // #10495 - check if object type supports ilexportgui "directly"
        if ($a_class == "ilExportGUI") {
            try {
                $ilCtrl->getLinkTargetByClass($path);
            } catch (Exception $e) {
                switch ($node["type"]) {
                    case "glo":
                        $cmd = "exportList";
                        $path = array("ilRepositoryGUI", "ilGlossaryEditorGUI", $gui_class);
                        break;

                    default:
                        $cmd = "export";
                        $path = array("ilRepositoryGUI", $gui_class);
                        break;
                }
                $ilCtrl->setParameterByClass($gui_class, "ref_id", $a_ref_id);
                $ilCtrl->setParameterByClass($gui_class, "cmd", $cmd);
                $ilCtrl->redirectByClass($path);
            }
        }
                        
        $ilCtrl->setParameterByClass($a_class, "ref_id", $a_ref_id);
        $ilCtrl->setParameterByClass($a_class, "cmd", $a_cmd);
        $ilCtrl->redirectByClass($path);
    }
    
    public function delete()
    {
        $ref_id = (int) $_REQUEST["ownid"];
        $this->redirectParentCmd($ref_id, "delete");
    }
    
    public function move()
    {
        $ref_id = (int) $_REQUEST["ownid"];
        $this->redirectParentCmd($ref_id, "cut");
    }
    
    public function export()
    {
        $ref_id = (int) $_REQUEST["ownid"];
        $this->redirectCmd($ref_id, "ilExportGUI");
    }
    
    public function changeOwner()
    {
        $ref_id = (int) $_REQUEST["ownid"];
        $this->redirectCmd($ref_id, "ilPermissionGUI", "owner");
    }
}
