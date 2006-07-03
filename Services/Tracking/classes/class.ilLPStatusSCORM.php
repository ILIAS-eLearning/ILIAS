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
		include_once './Services/Tracking/classes/class.ilLPCollections.php';
		include_once './content/classes/SCORM/class.ilObjSCORMTracking.php';

		return  array_diff(ilObjSCORMTracking::_getInProgress(ilLPCollections::_getItems($a_obj_id),$a_obj_id),
						   ilLPStatusSCORM::_getCompleted($a_obj_id));
	}

	function _getCompleted($a_obj_id)
	{
		global $ilDB;

		include_once './Services/Tracking/classes/class.ilLPCollections.php';
		include_once './content/classes/SCORM/class.ilObjSCORMTracking.php';

		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$items = $status_info['scos'];

		$counter = 0;
		foreach($items as $sco_id)
		{
			$tmp_users = ilObjSCORMTracking::_getCompleted($sco_id,$a_obj_id);
			if(!$counter++)
			{
				$users = $tmp_users;
			}
			else
			{
				$users = array_intersect($users,$tmp_users);
			}
		}
		return (array) $users;
	}
	
	function _getStatusInfo($a_obj_id)
	{
		// Which sco's determine the status
		include_once './Services/Tracking/classes/class.ilLPCollections.php';
		$status_info['scos'] = ilLPCollections::_getItems($a_obj_id);
		$status_info['num_scos'] = count($status_info['scos']);
		
		include_once './content/classes/SCORM/class.ilObjSCORMTracking.php';
		$status_info['num_completed'] = ilObjSCORMTracking::_getCountCompletedPerUser($status_info['scos'],$a_obj_id);


		// Get subtype
		include_once './content/classes/class.ilObjSAHSLearningModule.php';
		$status_info['subtype'] = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);

		switch($status_info['subtype'])
		{
			case 'hacp':
			case 'aicc':
				include_once './content/classes/class.ilObjAICCLearningModule.php';
				foreach(ilObjAICCLearningModule::_getTrackingItems($a_obj_id) as $item)
				{
					if(in_array($item['obj_id'],$status_info['scos']))
					{
						$status_info['scos_title']["$item[obj_id]"] = $item['title'];
					}
				}
				break;
			case 'scorm':

				include_once './content/classes/SCORM/class.ilSCORMItem.php';
				foreach($status_info['scos'] as $sco_id)
				{
					$status_info['scos_title'][$sco_id] = ilSCORMItem::_lookupTitle($sco_id);
				}
		}

		foreach($status_info['scos'] as $sco_id)
		{
			include_once './content/classes/SCORM/class.ilObjSCORMTracking.php';
			$status_info['completed'][$sco_id] = ilObjSCORMTracking::_getCompleted($sco_id,$a_obj_id);
			$status_info['in_progress'][$sco_id] = ilObjSCORMTracking::_getInProgress($sco_id,$a_obj_id);
		}
		return $status_info;
	}
}	
?>