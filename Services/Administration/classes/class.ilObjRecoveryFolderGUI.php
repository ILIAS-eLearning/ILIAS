<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjRecoveryFolderGUI
 *
 * @author Sascha Hofmann <shofmann@databay.de>
 * @ilCtrl_Calls ilObjRecoveryFolderGUI: ilPermissionGUI
 */
class ilObjRecoveryFolderGUI extends ilContainerGUI
{
    /**
     * @var ilRbacAdmin
     */
    protected $rbacadmin;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
    * Constructor
    * @access public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->rbacadmin = $DIC->rbac()->admin();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->type = "recf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }
    
    /**
    * save object
    * @access	public
    */
    public function saveObject()
    {
        $rbacadmin = $this->rbacadmin;

        // create and insert forum in objecttree
        $newObj = parent::saveObject();

        // put here object specific stuff
            
        // always send a message
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        exit();
    }

    public function removeFromSystemObject()
    {
        $ru = new ilRepUtilGUI($this);
        $ru->removeObjectsFromSystem($_POST["id"], true);
        $this->ctrl->redirect($this, "view");
    }
    
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }

    
    public function showPossibleSubObjects()
    {
        $this->sub_objects = "";
    }
    
    /**
    * Get Actions
    */
    public function getActions()
    {
        // standard actions for container
        return array(
            "cut" => array("name" => "cut", "lng" => "cut"),
            "clear" => array("name" => "clear", "lng" => "clear"),
            "removeFromSystem" => array("name" => "removeFromSystem", "lng" => "btn_remove_system")
        );
    }
} // END class.ilObjRecoveryFolderGUI
