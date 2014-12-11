<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLPStatus.php';

/**
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPStatusCollectionTLT.php 40252 2013-03-01 12:21:49Z jluetzen $
*
* @package ilias-tracking
*
*/

class ilLPStatusCollectionTLT extends ilLPStatus
{
	function _getInProgress($a_obj_id)
	{		
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		
		$users = array();
		if(is_array($status_info['in_progress']))
		{
			foreach($status_info['in_progress'] as $in_progress)
			{
				$users = array_merge($users,$in_progress);
			}
			$users = array_unique($users);
		}
		
		$users = array_diff($users,ilLPStatusWrapper::_getCompleted($a_obj_id));		
		
		return $users;
	}

	function _getCompleted($a_obj_id)
	{		
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

		$counter = 0;
		$users = array();
		foreach($status_info['items'] as $item_id)
		{
			$tmp_users = $status_info['completed'][$item_id];

			if(!$counter++)
			{
				$users = $tmp_users;
			}
			else
			{
				$users = array_intersect($users,$tmp_users);
			}
		}
		$users = array_unique($users);
		
		return $users;
	}
	
	function _getStatusInfo($a_obj_id, $a_include_tlt_data = false)
	{
		global $ilDB;
		
		$status_info = array();
								
		include_once "Services/Object/classes/class.ilObjectLP.php";
		$olp = ilObjectLP::getInstance($a_obj_id);
		$collection = $olp->getCollectionInstance();
		if($collection)
		{			
			$status_info["items"] = $collection->getItems($a_obj_id);
							
			include_once './Services/MetaData/classes/class.ilMDEducational.php';	
			foreach($status_info["items"] as $item_id)
			{
				$status_info["in_progress"][$item_id] = array();
				$status_info["completed"][$item_id] = array();

				$status_info["tlt"][$item_id] = ilMDEducational::_getTypicalLearningTimeSeconds($a_obj_id, $item_id);
			}
		
			$ref_ids = ilObject::_getAllReferences($a_obj_id);
			$ref_id = end($ref_ids);			
			$possible_items = $collection->getPossibleItems($ref_id);				
			$chapter_ids = array_intersect(array_keys($possible_items),
				$status_info["items"]);

			// fix order (adapt from possible items)
			$status_info["items"] = $chapter_ids;

			if($chapter_ids)
			{					
				foreach($chapter_ids as $item_id)
				{
					$status_info["item_titles"][$item_id] = $possible_items[$item_id]["title"];
				}

				$set = $ilDB->query("SELECT obj_id,usr_id,spent_seconds".
					" FROM lm_read_event".
					" WHERE ".$ilDB->in("obj_id", $chapter_ids, "", "integer"));
				while($row = $ilDB->fetchAssoc($set))
				{						
					if($row["spent_seconds"] < $status_info["tlt"][$row["obj_id"]])
					{
						$status_info["in_progress"][$row["obj_id"]][] = $row["usr_id"];													
					}
					else
					{
						$status_info["completed"][$row["obj_id"]][] = $row["usr_id"];								
					}

					if($a_include_tlt_data)
					{
						$status_info["tlt_users"][$row["obj_id"]][$row["usr_id"]] = $row["spent_seconds"];
					}
				}										
			}
		}		
		
		if(!$a_include_tlt_data)
		{
			unset($status_info["tlt"]);		
		}
		
		return $status_info;
	}
	
	function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
	{
		$info = self::_getStatusInfo($a_obj_id);		
				
		$completed_once = false;
		
		if(is_array($info["completed"]))
		{
			$completed = true;			
			foreach($info["completed"] as $user_ids)
			{
				// must have completed all items to complete collection
				if(!in_array($a_user_id, $user_ids))
				{
					$completed = false;
					break;
				}
				else
				{
					$completed_once = true;
				}
			}
			if($completed)
			{
				return self::LP_STATUS_COMPLETED_NUM;
			}	
		}
		
		// #14997
		if($completed_once)
		{
			return self::LP_STATUS_IN_PROGRESS_NUM;
		}
		
		if(is_array($info["in_progress"]))
		{
			foreach($info["in_progress"] as $user_ids)
			{
				if(in_array($a_user_id, $user_ids))
				{
					return self::LP_STATUS_IN_PROGRESS_NUM;
				}
			}
		}
		
		return self::LP_STATUS_NOT_ATTEMPTED_NUM;
	}
}	

?>