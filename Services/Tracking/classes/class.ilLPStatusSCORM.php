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

/**
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLPStatus.php';

class ilLPStatusSCORM extends ilLPStatus
{

	function ilLPStatusSCORM($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}


	function _getInProgress($a_obj_id)
	{
		include_once './Services/Tracking/classes/class.ilLPCollectionCache.php';
		include_once './Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php';

		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$users = array();

		foreach($status_info['in_progress'] as $in_progress)
		{
			$users = array_merge($users,$in_progress);
		}
		$users = array_unique($users);
		$users = array_diff($users,ilLPStatusWrapper::_getCompleted($a_obj_id));
		$users = array_diff($users,ilLPStatusWrapper::_getFailed($a_obj_id));

		return $users;
	}

	function _getCompleted($a_obj_id)
	{
		global $ilDB;

		include_once './Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php';

		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

		$items = $status_info['scos'];

		$counter = 0;
		$users = array();
		foreach($items as $sco_id)
		{
			$tmp_users = $status_info['completed'][$sco_id];

			if(!$counter++)
			{
				$users = $tmp_users;
			}
			else
			{
				$users = array_intersect($users,$tmp_users);
			}
		}

		$users = array_diff($users,ilLPStatusWrapper::_getFailed($a_obj_id));
		return $users;
	}

	function _getFailed($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

		if(!count($status_info['scos']))
		{
			return array();
		}
		$users = array();
		foreach($status_info['scos'] as $sco_id)
		{
			$users = array_merge($users,(array) $status_info['failed'][$sco_id]);
		}
		return array_unique($users);
	}

	
	function _getStatusInfo($a_obj_id)
	{
		// Which sco's determine the status
		include_once './Services/Tracking/classes/class.ilLPCollectionCache.php';
		$status_info['scos'] = ilLPCollectionCache::_getItems($a_obj_id);
		
		$status_info['num_scos'] = count($status_info['scos']);

		// Get subtype
		include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
		$status_info['subtype'] = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);
		
		switch($status_info['subtype'])
		{
			case 'hacp':
			case 'aicc':
				include_once './Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php';
				$status_info['num_completed'] = ilObjSCORMTracking::_getCountCompletedPerUser($status_info['scos'],$a_obj_id);

				include_once './Modules/ScormAicc/classes/class.ilObjAICCLearningModule.php';
				foreach(ilObjAICCLearningModule::_getTrackingItems($a_obj_id) as $item)
				{
					if(in_array($item['obj_id'],$status_info['scos']))
					{
						$status_info['scos_title']["$item[obj_id]"] = $item['title'];
					}
				}
				$info = ilObjSCORMTracking::_getProgressInfo($status_info['scos'],$a_obj_id);
				break;

			case 'scorm':
				include_once './Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php';
				$status_info['num_completed'] = ilObjSCORMTracking::_getCountCompletedPerUser($status_info['scos'],$a_obj_id);

				include_once './Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php';
				foreach($status_info['scos'] as $sco_id)
				{
					$status_info['scos_title'][$sco_id] = ilSCORMItem::_lookupTitle($sco_id);
				}
				$info = ilObjSCORMTracking::_getProgressInfo($status_info['scos'],$a_obj_id);
				break;
				
			case "scorm2004":
				include_once './Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php';
				$status_info['num_completed'] = ilSCORM2004Tracking::_getCountCompletedPerUser($status_info['scos'],$a_obj_id);
				include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';
				foreach($status_info['scos'] as $sco_id)
				{
					$status_info['scos_title'][$sco_id] = ilObjSCORM2004LearningModule::_lookupItemTitle($sco_id);
				}

				$info = ilSCORM2004Tracking::_getItemProgressInfo($status_info['scos'],$a_obj_id);
				break;
		}

		$status_info['completed'] = array();
		$status_info['failed'] = array();
		$status_info['in_progress'] = array();
		foreach($status_info['scos'] as $sco_id)
		{
			$status_info['completed'][$sco_id] = $info['completed'][$sco_id] ? $info['completed'][$sco_id] : array();
			$status_info['failed'][$sco_id] = $info['failed'][$sco_id] ? $info['failed'][$sco_id] : array();
			$status_info['in_progress'][$sco_id] = $info['in_progress'][$sco_id] ? $info['in_progress'][$sco_id] : array();
		}
//var_dump($status_info["completed"]);
		return $status_info;
	}
}	
?>