<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/ContainerReference/classes/class.ilContainerReferenceGUI.php');
/**
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilContainerReferenceGUI
 * @ilCtrl_Calls ilObjGroupReferenceGUI: ilPermissionGUI, ilInfoScreenGUI, ilPropertyFormGUI
 * @ingroup ModulesGroupReference
 */
class ilObjGroupReferenceGUI extends ilContainerReferenceGUI implements ilCtrlBaseClassInterface
{

    /**
     * ilObjGroupReferenceGUI constructor.
     * @param $a_data
     * @param int $a_id
     * @param bool $a_call_by_reference
     * @param bool $a_prepare_output
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = false)
    {
        $this->target_type = 'grp';
        $this->reference_type = 'grpr';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
    }

    /**
     * Execute command
     *
     * @access public
     */
    public function executeCommand()
    {
        parent::executeCommand();
    }
    
    
    /**
     *  Support for goto php
     *
     * @param int $a_target
     */
    public static function _goto($a_target)
    {
        include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
        $target_ref_id = ilContainerReference::_lookupTargetRefId(ilObject::_lookupObjId($a_target));
        
        include_once('./Modules/Group/classes/class.ilObjGroupGUI.php');
        ilObjGroupGUI::_goto($target_ref_id);
    }
}
