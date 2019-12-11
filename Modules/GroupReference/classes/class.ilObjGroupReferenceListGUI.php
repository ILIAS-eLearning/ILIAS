<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Group/classes/class.ilObjGroupListGUI.php";

/**
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilObjGroupListGUI
 *
 * @ingroup ModulesGroupReference
*/
class ilObjGroupReferenceListGUI extends ilObjGroupListGUI
{
    /** @var null|int */
    protected $reference_obj_id = null;
    /** @var null|int */
    protected $reference_ref_id = null;
    /** @var bool */
    protected $deleted = false;
    
    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getIconImageType()
    {
        return 'grpr';
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
     * @return int|null
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
        $this->type = "grp";
        $this->gui_class_name = "ilobjgroupgui";
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }
    }
    
    
    
    /**
    * inititialize new item
    * Group reference inits the group item
    *
    * @param	int			$a_ref_id		reference id
    * @param	int			$a_obj_id		object id
    * @param	string		$a_title		title
    * @param	string		$a_description	description
    */
    public function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $tree = $DIC['tree'];
        
        $this->reference_ref_id = $a_ref_id;
        $this->reference_obj_id = $a_obj_id;
        
        
        include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
        $target_obj_id = ilContainerReference::_lookupTargetId($a_obj_id);
        
        $target_ref_ids = ilObject::_getAllReferences($target_obj_id);
        $target_ref_id = current($target_ref_ids);
        $target_title = ilContainerReference::_lookupTitle($a_obj_id);
        $target_description = ilObject::_lookupDescription($target_obj_id);

        $this->deleted = $tree->isDeleted($target_ref_id);
        
        $this->conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($target_ref_id, $target_obj_id);

        
        parent::initItem($target_ref_id, $target_obj_id, $target_title, $target_description);

        // general commands array
        include_once('./Modules/GroupReference/classes/class.ilObjGroupReferenceAccess.php');
        $this->commands = ilObjGroupReferenceAccess::_getCommands($this->reference_ref_id);
        
        if ($ilAccess->checkAccess('write', '', $this->reference_ref_id) or $this->deleted) {
            $this->info_screen_enabled = false;
        } else {
            $this->info_screen_enabled = true;
        }
    }
    
    public function getProperties()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];

        $props = parent::getProperties();

        // offline
        if ($this->deleted) {
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
     * @return string
     */
    public function getCommandLink($a_cmd)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
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
