<?php
/** 
* @ilCtrl_Calls ilObjStudyProgrammeReferenceGUI: ilPermissionGUI, ilInfoScreenGUI, ilPropertyFormGUI
*/
class ilObjStudyProgrammeReferenceGUI extends ilContainerReferenceGUI
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
		$this->target_type = 'prg';
		$this->reference_type = 'prgr';
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
	}

	/**
	 *  Support for goto php
	 *
	 * @param int $a_target
	 */
	public static function _goto($a_target)
	{
		$target_ref_id = ilContainerReference::_lookupTargetRefId(ilObject::_lookupObjId($a_target));
		ilObjStudyProgrammeGUI::_goto($target_ref_id);
	}

	/**
	 * save object
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function saveObject()
	{
		$ilAccess = $this->access;
		
		if(!(int) $_REQUEST['target_id'])
		{

			$this->createObject();
			return false;	
		}
		if(!$ilAccess->checkAccess('visible','',(int) $_REQUEST['target_id']))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			$this->createObject();
			return false;	
		}
		if($this->tryingToCreateCircularReference((int)$_REQUEST['target_id'],(int)$_REQUEST['ref_id'])) {
			ilUtil::sendFailure($this->lng->txt('prgr_may_not_create_circular_reference'));
			$this->createObject();
			return false;
		}
		parent::saveObject();
	}


	protected function tryingToCreateCircularReference($obj_to_be_referenced, $reference_position)
	{
		if($reference_position === $obj_to_be_referenced) {
			return true;
		}
		$queque = [$reference_position];
		while($parent = array_shift($queque)) {
			$p_parent = (int)$this->tree->getParentId($parent);
			if($p_parent === $obj_to_be_referenced) {
				return true;
			}
			if(ilObject::_lookupType($p_parent,true) === 'prg') {
				array_push($queque, $p_parent);
			}
			foreach(ilContainerReference::_lookupSourceIds(ilObject::_lookupObjId($parent)) as $parent_ref_obj_id) {
				$parent_ref_ref_id = (int)array_shift(ilObject::_getAllReferences($parent_ref_obj_id));
				$parent_ref_loc = (int)$this->tree->getParentId($parent_ref_ref_id);
				if($parent_ref_loc === $obj_to_be_referenced) {
					return true;
				}
				if(ilObject::_lookupType($parent_ref_loc,true) === 'prg') {
					array_push($queque, $parent_ref_loc);
				}
			}
		}
		return false;
	}
}