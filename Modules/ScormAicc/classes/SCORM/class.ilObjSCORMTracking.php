<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Class ilObjSCORMTracking
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMTracking
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjSCORMTracking()
	{
		global $ilias;

	}

	function extractData()
	{
		$this->insert = array();
		if (is_array($_GET["iL"]))
		{
			foreach($_GET["iL"] as $key => $value)
			{
				$this->insert[] = array("left" => $value, "right" => $_GET["iR"][$key]);
			}
		}
		if (is_array($_POST["iL"]))
		{
			foreach($_POST["iL"] as $key => $value)
			{
				$this->insert[] = array("left" => $value, "right" => $_POST["iR"][$key]);
			}
		}

		$this->update = array();
		if (is_array($_GET["uL"]))
		{
			foreach($_GET["uL"] as $key => $value)
			{
				$this->update[] = array("left" => $value, "right" => $_GET["uR"][$key]);
			}
		}
		if (is_array($_POST["uL"]))
		{
			foreach($_POST["uL"] as $key => $value)
			{
				$this->update[] = array("left" => $value, "right" => $_POST["uR"][$key]);
			}
		}
	}

	function store($obj_id=0, $sahs_id=0, $extractData=1)
	{
		global $ilDB, $ilUser;

		if (empty($obj_id))
		{
			$obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
		}
		
		if (empty($sahs_id))
			$sahs_id = ($_GET["sahs_id"] != "")	? $_GET["sahs_id"] : $_POST["sahs_id"];
			
		if ($extractData==1)
			$this->extractData();

		if (is_object($ilUser))
		{
			$user_id = $ilUser->getId();
		}

		// writing to scorm test log
		$f = fopen("./Modules/ScormAicc/log/scorm.log", "a");
		fwrite($f, "\nCALLING SCORM store()\n");
		if ($obj_id <= 1)
		{
			fwrite($f, "Error: No obj_id given.\n");
		}
		else
		{
			foreach($this->insert as $insert)
			{
				$q = "SELECT * FROM scorm_tracking WHERE ".
					" user_id = ".$ilDB->quote($user_id).
					" AND sco_id = ".$ilDB->quote($sahs_id).
					" AND lvalue = ".$ilDB->quote($insert["left"]).
					" AND obj_id = ".$ilDB->quote($obj_id);
				$set = $ilDB->query($q);
				if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
				{
					fwrite($f, "Error Insert, left value already exists. L:".$insert["left"].",R:".
						$insert["right"].",sahs_id:".$sahs_id.",user_id:".$user_id."\n");
				}
				else
				{
					$q = "INSERT INTO scorm_tracking (user_id, sco_id, obj_id, lvalue, rvalue) VALUES ".
						"(".$ilDB->quote($user_id).",".$ilDB->quote($sahs_id).",".
						$ilDB->quote($obj_id).",".
						$ilDB->quote($insert["left"]).",".$ilDB->quote($insert["right"]).")";
					$ilDB->query($q);
					fwrite($f, "Insert - L:".$insert["left"].",R:".
						$insert["right"].",sahs_id:".$sahs_id.",user_id:".$user_id."\n");
				}
			}
			foreach($this->update as $update)
			{
				$q = "SELECT * FROM scorm_tracking WHERE ".
					" user_id = ".$ilDB->quote($user_id).
					" AND sco_id = ".$ilDB->quote($sahs_id).
					" AND lvalue = ".$ilDB->quote($update["left"]).
					" AND obj_id = ".$ilDB->quote($obj_id);
				$set = $ilDB->query($q);
				if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$q = "REPLACE INTO scorm_tracking (user_id, sco_id, obj_id, lvalue, rvalue) VALUES ".
						"(".$ilDB->quote($user_id).",".$ilDB->quote($sahs_id).",".
						$ilDB->quote($obj_id).",".
						$ilDB->quote($update["left"]).",".$ilDB->quote($update["right"]).")";
					$ilDB->query($q);
					fwrite($f, "Update - L:".$update["left"].",R:".
						$update["right"].",sahs_id:".$sahs_id.",user_id:".$user_id."\n");
				}
				else
				{
					fwrite($f, "ERROR Update, left value does not exist. L:".$update["left"].",R:".
						$update["right"].",sahs_id:".$sahs_id.",user_id:".$user_id."\n");
				}
			}
		}
		fclose($f);
	}

	function _insertTrackData($a_sahs_id, $a_lval, $a_rval, $a_obj_id)
	{
		global $ilDB, $ilUser;

		$q = "INSERT INTO scorm_tracking (user_id, sco_id, lvalue, rvalue, obj_id) ".
			" VALUES (".$ilDB->quote($ilUser->getId()).",".$ilDB->quote($a_sahs_id).
			",".$ilDB->quote($a_lval).",".$ilDB->quote($a_rval).
			",".$ilDB->quote($a_obj_id).")";
		$ilDB->query($q);

	}


	function _getInProgress($scorm_item_id,$a_obj_id)
	{
		global $ilDB;

		if(is_array($scorm_item_id))
		{
			$where = "WHERE sco_id IN(";
			$where .= implode(",",ilUtil::quoteArray($scorm_item_id));
			$where .= ") ";
			$where .= ("AND obj_id = ".$ilDB->quote($a_obj_id)." ");
			   
		}
		else
		{
			$where = "WHERE sco_id = ".$ilDB->quote($scorm_item_id)." ";
			$where .= ("AND obj_id = ".$ilDB->quote($a_obj_id)." ");
		}
				

		$query = "SELECT user_id,sco_id FROM scorm_tracking ".
			$where.
			"GROUP BY user_id, sco_id";
		

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$in_progress[$row->sco_id][] = $row->user_id;
		}
		return is_array($in_progress) ? $in_progress : array();
	}

	function _getCompleted($scorm_item_id,$a_obj_id)
	{
		global $ilDB;

		if(is_array($scorm_item_id))
		{
			$where = "WHERE sco_id IN(".implode(",",ilUtil::quoteArray($scorm_item_id)).") ";
		}
		else
		{
			$where = "sco_id = ".$ilDB->quote($scorm_item_id)." ";
		}

		$query = "SELECT DISTINCT(user_id) FROM scorm_tracking ".
			$where.
			"AND obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND lvalue = 'cmi.core.lesson_status' ".
			"AND ( rvalue = 'completed' ".
			"OR rvalue = 'passed')";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
	}

	function _getFailed($scorm_item_id,$a_obj_id)
	{
		global $ilDB;

		if(is_array($scorm_item_id))
		{
			$where = "WHERE sco_id IN('".implode("','",$scorm_item_id)."') ";
		}
		else
		{
			$where = "sco_id = '".$scorm_item_id."' ";
		}

		$query = "SELECT DISTINCT(user_id) FROM scorm_tracking ".
			$where.
			"AND obj_id = '".$a_obj_id."' ".
			"AND lvalue = 'cmi.core.lesson_status' ".
			"AND rvalue = 'failed'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
	}

	function _getCountCompletedPerUser($a_scorm_item_ids,$a_obj_id)
	{
		global $ilDB;

		$where = "WHERE sco_id IN(";
		$where .= implode(",",ilUtil::quoteArray($a_scorm_item_ids));
		$where .= ") ";
		$where .= ("AND obj_id = ".$ilDB->quote($a_obj_id));
		

		$query = "SELECT user_id, COUNT(user_id) as completed FROM scorm_tracking ".
			$where.
			" AND lvalue = 'cmi.core.lesson_status' ".
			"AND (rvalue = 'completed' OR ".
			"rvalue = 'passed') ".
			"GROUP BY user_id";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$users[$row->user_id] = $row->completed;
		}

		return $users ? $users : array();
	}

	function _getProgressInfo($sco_item_ids,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM scorm_tracking ".
			"WHERE sco_id IN(".implode(",",ilUtil::quoteArray($sco_item_ids)).") ".
			"AND obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND lvalue = 'cmi.core.lesson_status'";

		$res = $ilDB->query($query);

		$info['completed'] = array();
		$info['failed'] = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			switch($row->rvalue)
			{
				case 'completed':
				case 'passed':
					$info['completed'][$row->sco_id][] = $row->user_id;
					break;

				case 'failed':
					$info['failed'][$row->sco_id][] = $row->user_id;
					break;
			}
		}
		$info['in_progress'] = ilObjSCORMTracking::_getInProgress($sco_item_ids,$a_obj_id);

		return $info;
	}
			

} // END class.ilObjSCORMTracking
?>
