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
	
	const PROGRESS_FILTER_ALL = 0;
	const PROGRESS_FILTER_IN_PROGRESS = 1;
	const PROGRESS_FILTER_COMPLETED = 2;
	const PROGRESS_FILTER_FAILED = 3;
	
	const USER_FILTER_ALL = -1;
	
	/**
	 * Delete progress of users and objects
	 * Implemented for 
	 */
	public function deleteProgress($sid, $ref_ids, $usr_ids, $type_filter, $progress_filter)
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
		if(!in_array(self::USER_FILTER_ALL, $usr_ids) and !ilObjUser::userExists($usr_ids))
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
			
			// filter users
			$valid_users = $this->applyProgressFilter($obj->getId(), (array) $usr_ids, (array) $progress_filter);
			
			switch($obj->getType())
			{
				case 'sahs':
					include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
					$subtype = ilObjSAHSLearningModule::_lookupSubType($obj->getId());
					
					switch($subtype)
					{
						case 'scorm':
							$this->deleteScormTracking($obj->getId(),(array) $valid_users);
							break;
							
						case 'scorm2004':
							$this->deleteScorm2004Tracking($obj->getId(), (array) $valid_users);
							break;
					}
					break;
					
				case 'tst':
					foreach((array) $valid_users as $usr_id)
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
	
	/**
	 * Apply progress filter
	 * @param int $obj_id
	 * @param array $usr_ids
	 * @param array $filter
	 * 
	 * @return array $filtered_users
	 */
	protected function applyProgressFilter($obj_id, Array $usr_ids, Array $filter)
	{
		include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
		

		$all_users = array();
		if(in_array(self::USER_FILTER_ALL, $usr_ids))
		{
			$all_users = array_unique(
					array_merge(
						ilLPStatusWrapper::_getInProgress($obj_id),
						ilLPStatusWrapper::_getCompleted($obj_id),
						ilLPStatusWrapper::_getFailed($obj_id)
					)
				);
		}
		else
		{
			$all_users = $usr_ids;
		}

		if(!$filter or in_array(self::PROGRESS_FILTER_ALL, $filter))
		{
			$GLOBALS['log']->write(__METHOD__.': Deleting all progress data');
			return $all_users;
		}
		
		$filter_users = array();
		if(in_array(self::PROGRESS_FILTER_IN_PROGRESS, $filter))
		{
			$GLOBALS['log']->write(__METHOD__.': Filtering  in progress.');
			$filter_users = array_merge($filter, ilLPStatusWrapper::_getInProgress($obj_id));
		}
		if(in_array(self::PROGRESS_FILTER_COMPLETED, $filter))
		{
			$GLOBALS['log']->write(__METHOD__.': Filtering  completed.');
			$filter_users = array_merge($filter, ilLPStatusWrapper::_getCompleted($obj_id));
		}
		if(in_array(self::PROGRESS_FILTER_FAILED, $filter))
		{
			$GLOBALS['log']->write(__METHOD__.': Filtering  failed.');
			$filter_users = array_merge($filter, ilLPStatusWrapper::_getFailed($obj_id));
		}
		
		// Build intersection
		return array_intersect($all_users, $filter_users);
	}
	
	/**
	 * Delete SCORM Tracking
	 * @global type $ilDB
	 * @param type $a_obj_id
	 * @param type $a_usr_ids
	 * @return boolean
	 */
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
	
	/**
	 * Get learning progress changes
	 */
	public function getLearningProgressChanges($sid, $timestamp, $include_ref_ids, $type_filter)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		global $rbacsystem, $tree, $ilLog;

		// check administrator
		$types = "";
		if (is_array($type_filter))
		{
			$types = implode($type_filter, ",");
		}
		
		// output lp changes as xml
		try
		{
			include_once './Services/Tracking/classes/class.ilLPXmlWriter.php';
			$writer = new ilLPXmlWriter(true);
			$writer->setTimestamp($timestamp);
			$writer->setIncludeRefIds($include_ref_ids);
			$writer->setTypeFilter($type_filter);
			$writer->write();
		
			return $writer->xmlDumpMem(true);
		}
		catch(UnexpectedValueException $e)
		{
			return $this->__raiseError($e->getMessage(), 'Client');
		}
	}

}
?>
