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
* Object statistics garbage collection
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjectStatisticsCheck.php 29621 2011-06-22 16:51:14Z jluetzen $
*
* @package ilias
*/

class ilCronObjectStatisticsCheck
{
	function __construct()
	{
		global $ilLog,$ilDB;

		$this->log =& $ilLog;
		$this->db =& $ilDB;
		
		// all date related operations are based on this timestamp
		// should be midnight of yesterday (see gatherUserData()) to always have full day
		$this->date = strtotime("yesterday");
	}

	function check()
	{
		$this->gatherCourseLPData();
		$this->gatherTypesData();
		$this->gatherUserData();
	}
	
	function gatherCourseLPData()
	{
		global $tree, $ilDB;
				
		// process all courses
		$all_courses = array_keys(ilObject::_getObjectsByType("crs"));	
		if($all_courses)
		{
			// gather objects in trash
			$trashed_objects = $tree->getSavedNodeObjIds($all_courses);
			
			include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
			include_once "Modules/Course/classes/class.ilCourseParticipants.php";
			include_once "Services/Tracking/classes/class.ilLPStatusWrapper.php";				
			foreach($all_courses as $crs_id)
			{				
				// trashed objects will not change
				if(!in_array($crs_id, $trashed_objects))
				{
					// only if LP is active
					$mode = ilLPObjSettings::_lookupMode($crs_id);
					if($mode == LP_MODE_DEACTIVATED || $mode == LP_MODE_UNDEFINED)
					{
						continue;
					}
									
					// only save once per day
					$ilDB->manipulate("DELETE FROM obj_lp_stat WHERE".
						" obj_id = ".$ilDB->quote($crs_id, "integer").
						" AND fulldate = ".$ilDB->quote(date("Ymd", $this->date), "integer"));
					
					$members = new ilCourseParticipants($crs_id);
					$members = $members->getMembers();	
					
					$in_progress = count(ilLPStatusWrapper::_lookupInProgressForObject($crs_id, $members));
					$completed = count(ilLPStatusWrapper::_lookupCompletedForObject($crs_id, $members));
					$failed = count(ilLPStatusWrapper::_lookupFailedForObject($crs_id, $members));
					
					// calculate with other values - there is not direct method
					$not_attempted = count($members) - $in_progress - $completed - $failed;
					
					$set = array(
						"type" => array("text", "crs"),
						"obj_id" => array("integer", $crs_id),
						"yyyy" => array("integer", date("Y", $this->date)),
						"mm" => array("integer", date("m", $this->date)),
						"dd" => array("integer", date("d", $this->date)),
						"fulldate" => array("integer", date("Ymd", $this->date)),
						"mem_cnt" => array("integer", count($members)),
						"in_progress" => array("integer", $in_progress),
						"completed" => array("integer", $completed),
						"failed" => array("integer", $failed),
						"not_attempted" => array("integer", $not_attempted)												
						);	
					
					$ilDB->insert("obj_lp_stat", $set);
				}						
			}
		}
	}
	
	function gatherTypesData()
	{
		global $ilDB;
		
		include_once "Services/Tracking/classes/class.ilTrQuery.php";
		$data = ilTrQuery::getObjectTypeStatistics();		
		foreach($data as $type => $item)
		{			
			// only save once per day
			$ilDB->manipulate("DELETE FROM obj_type_stat WHERE".
				" type = ".$ilDB->quote($type, "text").
				" AND fulldate = ".$ilDB->quote(date("Ymd", $this->date), "integer"));
			
			$set = array(
				"type" => array("text", $type),
				"yyyy" => array("integer", date("Y", $this->date)),
				"mm" => array("integer", date("m", $this->date)),
				"dd" => array("integer", date("d", $this->date)),
				"fulldate" => array("integer", date("Ymd", $this->date)),
				"cnt_references" => array("integer", (int)$item["references"]),
				"cnt_objects" => array("integer", (int)$item["objects"]),
				"cnt_deleted" => array("integer", (int)$item["deleted"])										
				);	

			$ilDB->insert("obj_type_stat", $set);
		}
	}
	
	function gatherUserData()
	{
		global $ilDB;
		
		$to = mktime(23, 59, 59, date("m", $this->date), date("d", $this->date), date("Y", $this->date));
					
		$sql = "SELECT COUNT(DISTINCT(usr_id)) counter,obj_id FROM read_event".
			" WHERE last_access >= ".$ilDB->quote($this->date, "integer").
			" AND last_access <= ".$ilDB->quote($to, "integer").
			" GROUP BY obj_id";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{		
			// only save once per day
			$ilDB->manipulate("DELETE FROM obj_user_stat".
				" WHERE fulldate = ".$ilDB->quote(date("Ymd", $this->date), "integer").
				" AND obj_id = ".$ilDB->quote($row["obj_id"], "integer"));

			$iset = array(
				"obj_id" => array("integer", $row["obj_id"]),
				"yyyy" => array("integer", date("Y", $this->date)),
				"mm" => array("integer", date("m", $this->date)),
				"dd" => array("integer", date("d", $this->date)),
				"fulldate" => array("integer", date("Ymd", $this->date)),	
				"counter" => array("integer", $row["counter"])
				);	

			$ilDB->insert("obj_user_stat", $iset);	
		}
	}
}
?>
