<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
* Class ilObjRecoveryFolderGUI
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjRecoveryFolderGUI: ilPermissionGUI
*
* @extends ilObjectGUI
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
        $rbacsystem = $this->rbacsystem;
        
        include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
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
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret =&$this->ctrl->forwardCommand($perm_gui);
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
