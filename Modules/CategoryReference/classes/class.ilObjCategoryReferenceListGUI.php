<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\ContainerReference\StandardGUIRequest;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjCategoryReferenceListGUI extends ilObjCategoryListGUI
{
    protected ?int $reference_obj_id = null;
    protected $reference_ref_id = null;
    protected bool $deleted = false;
    protected StandardGUIRequest $cont_ref_request;
    
    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        parent::__construct();
        $this->cont_ref_request = $DIC
            ->containerReference()
            ->internal()
            ->gui()
            ->standardRequest();
    }
    
    public function getIconImageType()
    {
        return 'catr';
    }

    public function getTypeIcon()
    {
        $reference_obj_id = ilObject::_lookupObjId($this->getCommandId());
        return ilObject::_getIcon(
            $reference_obj_id,
            'small'
        );
    }


    public function getCommandId()
    {
        return $this->reference_ref_id;
    }
    
    public function insertTimingsCommand()
    {
    }
    
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
        
        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }
    }
    
    public function initItem($a_ref_id, $a_obj_id, $type, $a_title = "", $a_description = "")
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
        
        parent::initItem($target_ref_id, $target_obj_id, $type, $target_title, $target_description);

        // general commands array
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
        $tree = $this->tree;

        $props = parent::getProperties();
        
        // offline
        if ($tree->isDeleted($this->ref_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("reference_deleted"));
        }

        return $props ?: array();
    }
    
    public function checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id = "")
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
    
    public function getCommandLink($a_cmd)
    {
        $ilCtrl = $this->ctrl;
        
        switch ($a_cmd) {
            case 'editReference':
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getCommandId());
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass(
                    "ilrepositorygui",
                    "ref_id",
                    $this->cont_ref_request->getRefId()
                );
                return $cmd_link;

            default:
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass(
                    "ilrepositorygui",
                    "ref_id",
                    $this->cont_ref_request->getRefId()
                );
                return $cmd_link;
        }
    }
}
