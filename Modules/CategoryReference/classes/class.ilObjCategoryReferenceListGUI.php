<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once "./Modules/Category/classes/class.ilObjCategoryListGUI.php";

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ModulesCategoryReference
*/
class ilObjCategoryReferenceListGUI extends ilObjCategoryListGUI
{
    protected $reference_obj_id = null;
    protected $reference_ref_id = null;
    
    protected $deleted = false;
    
    /**
     * Constructor
     *
     * @access public
     *
     */
    public function __construct()
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        parent::__construct();
    }
    
    public function getIconImageType()
    {
        return 'catr';
    }

    /**
     * @inheritdoc
     */
    public function getTypeIcon()
    {
        $reference_obj_id = ilObject::_lookupObjId($this->getCommandId());
        return ilObject::_getIcon(
            $reference_obj_id,
            'small'
        );
    }



    /**
     * get command id
     *
     * @access public
     * @param
     * @return
     */
    public function getCommandId()
    {
        return $this->reference_ref_id;
    }
    
    /**
     * no activation for links
     * @return type
     */
    public function insertTimingsCommand()
    {
        return;
    }
    
    /**
    * initialisation
    */
    public function init()
    {
        $this->copy_enabled = true;
        $this->static_link_enabled = false;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "cat";
        $this->gui_class_name = "ilobjcategorygui";
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }
    }
    
    
    
    /**
    * inititialize new item
    * Course reference inits the course item
    *
    * @param	int			$a_ref_id		reference id
    * @param	int			$a_obj_id		object id
    * @param	string		$a_title		title
    * @param	string		$a_description	description
    */
    public function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
    {
        $ilAccess = $this->access;
        $tree = $this->tree;
        
        $this->reference_ref_id = $a_ref_id;
        $this->reference_obj_id = $a_obj_id;
        
        include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
        $target_obj_id = ilContainerReference::_lookupTargetId($a_obj_id);
        
        $target_ref_ids = ilObject::_getAllReferences($target_obj_id);
        $target_ref_id = current($target_ref_ids);
        $target_title = ilContainerReference::_lookupTitle($a_obj_id);
        $target_description = ilObject::_lookupDescription($target_obj_id);
        
        $this->deleted = $tree->isDeleted($target_ref_id);
        
        
    
        parent::initItem($target_ref_id, $target_obj_id, $target_title, $target_description);

        // general commands array
        include_once('./Modules/CategoryReference/classes/class.ilObjCategoryReferenceAccess.php');
        $this->commands = ilObjCategoryReferenceAccess::_getCommands($this->reference_ref_id);

        if ($ilAccess->checkAccess('write', '', $this->reference_ref_id) or $this->deleted) {
            $this->info_screen_enabled = false;
        } else {
            $this->info_screen_enabled = true;
        }
    }
    
    
    public function getProperties()
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        $tree = $this->tree;

        $props = parent::getProperties();
        
        // offline
        if ($tree->isDeleted($this->ref_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("reference_deleted"));
        }

        return $props ? $props : array();
    }
    
    /**
     *
     * @param
     * @return
     */
    public function checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id="")
    {

        // Check edit reference against reference edit permission
        switch ($a_cmd) {
            case 'editReference':
                return parent::checkCommandAccess($a_permission, $a_cmd, $this->getCommandId(), $a_type, $a_obj_id);
        }

        switch ($a_permission) {
            case 'copy':
            case 'delete':
                // check against target ref_id
                return parent::checkCommandAccess($a_permission, $a_cmd, $this->getCommandId(), $a_type, $a_obj_id);
            
            default:
                // check against reference
                return parent::checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id);
        }
    }
    
    /**
     * get command link
     *
     * @access public
     * @param string $a_cmd
     * @return
     */
    public function getCommandLink($a_cmd)
    {
        $ilCtrl = $this->ctrl;
        
        switch ($a_cmd) {
            case 'editReference':
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getCommandId());
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                return $cmd_link;

            default:
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                return $cmd_link;
        }
    }
}
