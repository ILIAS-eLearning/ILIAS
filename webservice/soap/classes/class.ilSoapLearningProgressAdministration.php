<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './webservice/soap/classes/class.ilSoapAdministration.php';

/**
 * This class handles all DB changes necessary for fraunhofer
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 */
class ilSoapLearningProgressAdministration extends ilSoapAdministration
{
	protected static $DELETE_PROGRESS_FILTER_TYPES = array('sahs', 'tst');
	
	/**
	 * Delete progress of users and objects
	 * Implemented for 
	 */
	public function deleteProgress($sid, $ref_ids, $usr_ids, $type_filter)
	{
		$this->initAuth($sid);
		$this->initIlias();

		// Check session
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		
		// Check filter
		if(array_diff((array) $type_filter, self::$DELETE_PROGRESS_FILTER_TYPES))
		{
			return $this->__raiseError('Invalid filter type given', 'Client');
		}
		include_once 'Services/User/classes/class.ilObjUser.php';
		if(!ilObjUser::userExists($usr_ids))
		{
			return $this->__raiseError('Invalid user ids given', 'Client');
		}
		
		
		$valid_refs = array();
		foreach((array) $ref_ids as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$type = ilObject::_lookupType($obj_id);
			
			// All containers
			if($GLOBALS['objDefinition']->isContainer($type))
			{
				$all_sub_objs = array();
				foreach(($type_filter) as $type_filter_item)
				{
					$sub_objs = $GLOBALS['tree']->getSubTree(
						$GLOBALS['tree']->getNodeData($ref_id),
						false,
						$type_filter_item
					);
					$all_sub_objs = array_merge($all_sub_objs, $sub_objs);
				}
				
				foreach($all_sub_objs as $child_ref)
				{
					$child_type = ilObject::_lookupType(ilObject::_lookupObjId($child_ref));
					if(!$GLOBALS['ilAccess']->checkAccess('write', '', $child_ref))
					{
						return $this->__raiseError('Permission denied for : '. $ref_id.' -> type '.$type, 'Client');
					}
					$valid_refs[] = $child_ref;
				}
				
			}
			elseif(in_array($type, $type_filter))
			{
				if(!$GLOBALS['ilAccess']->checkAccess('write','',$ref_id))
				{
					return $this->__raiseError('Permission denied for : '. $ref_id.' -> type '.$type, 'Client');
				}
				$valid_refs[] = $ref_id;
			}
			else
			{
				return $this->__raiseError('Invalid object type given for : '. $ref_id.' -> type '.$type, 'Client');
			}
		}
		
		// Delete tracking data
		foreach($valid_refs as $ref_id)
		{
			include_once './classes/class.ilObjectFactory.php';
			$obj = ilObjectFactory::getInstanceByRefId($ref_id, false);
			
			if(!$obj instanceof ilObject)
			{
				return $this->__raiseError('Invalid reference id given : '. $ref_id.' -> type '.$type, 'Client');
			}
			
			switch($obj->getType())
			{
				case 'sahs':
					include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
					$subtype = ilObjSAHSLearningModule::_lookupSubType($obj->getId());
					
					switch($subtype)
					{
						case 'scorm':
							$this->deleteScormTracking($obj->getId(),(array) $usr_ids);
							break;
							
						case 'scorm2004':
							$this->deleteScorm2004Tracking($obj->getId(), (array) $usr_ids);
							break;
					}
					break;
					
				case 'tst':
					foreach((array) $usr_ids as $usr_id)
					{
						$obj->removeTestResultsForUser($usr_id);
					}
					break;
			}
			
			// Refresh status
			include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
			ilLPStatusWrapper::_refreshStatus($obj->getId());
			
		}
		return true;
	}
	
	protected function deleteScormTracking($a_obj_id, $a_usr_ids)
	{
		global $ilDB;
		
		$query = 'DELETE FROM scorm_tracking '.
		 	'WHERE '.$ilDB->in('user_id',$a_usr_ids,false,'integer').' '.
		 	'AND obj_id = '. $ilDB->quote($a_obj_id,'integer').' ';
		$res = $ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * Delete scorm 2004 tracking
	 * @param type $a_obj_id
	 * @param type $a_usr_ids 
	 */
	protected function deleteScorm2004Tracking($a_obj_id, $a_usr_ids)
	{
		global $ilDB;
		
		$query = 'SELECT cp_node_id FROM cp_node '.
			'WHERE nodename = '. $ilDB->quote('item','text').' '.
			'AND cp_node.slm_id = '.$ilDB->quote($a_obj_id,'integer');
		$res = $ilDB->query($query);
		
		$scos = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$scos[] = $row->cp_node_id;
		}
		
		$query = 'DELETE FROM cmi_node '.
				'WHERE '.$ilDB->in('user_id',(array) $a_usr_ids,false,'integer').' '.
				'AND '.$ilDB->in('cp_node_id',$scos,false,'integer');
		$ilDB->manipulate($query);
	
	}
}
?>
