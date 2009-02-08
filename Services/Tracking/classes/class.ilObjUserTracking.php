<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Class ilObjUserTracking
*
* @author Arlon Yin <arlon_yin@hotmail.com>
* @author Alex Killing <alex.killing@gmx.de>
* @author Jens Conze <jc@databay.de>
*
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

define('UT_INACTIVE_BOTH',0);
define('UT_ACTIVE_BOTH',1);
define('UT_ACTIVE_UT',2);
define('UT_ACTIVE_LP',3);

include_once "classes/class.ilObject.php";

class ilObjUserTracking extends ilObject
{
	var $valid_time_span = null;

	// BEGIN ChangeEvent
	/**
	 * This variable holds the enabled state of the change event tracking.
	 */
	private $is_change_event_tracking_enabled = null;
	// BEGIN ChangeEvent


	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjUserTracking($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "trac";
		$this->ilObject($a_id,$a_call_by_reference);

		define("DEFAULT_TIME_SPAN",60*5);
		$this->__readSettings();
	}


	
	function setActivationStatus($a_status)
	{
		$this->status = $a_status;
	}
	
	function getActivationStatus()
	{
		return $this->status;
	}

	/**
	* enable user tracking
	*/
	function enableTracking($a_enable)
	{
		echo 'deprecated';

		$this->tracking_enabled = (bool) $a_enable;

		return true;
	}
	
	function enabledTracking()
	{
		return ($this->status == UT_ACTIVE_UT) || ($this->status == UT_ACTIVE_BOTH);
	}

	/**
	* check wether learing progress is enabled or not
	*/
	function _enabledTracking()
	{
		global $ilias;

		$status = $ilias->getSetting("enable_tracking");

		return ($status == UT_ACTIVE_UT) || ($status == UT_ACTIVE_BOTH);
	}

	function enabledLearningProgress()
	{
		return ($this->status == UT_ACTIVE_LP) || ($this->status == UT_ACTIVE_BOTH);
	}

	/**
	* check wether learing progress is enabled or not
	*/
	function _enabledLearningProgress()
	{
		global $ilias;

		$status = $ilias->getSetting("enable_tracking");

		return ($status == UT_ACTIVE_LP) || ($status == UT_ACTIVE_BOTH);
	}

	/**
	* enable tracking of user related data
	*/
	function enableUserRelatedData($a_enable)
	{
		$this->tracking_user_related = (bool) $a_enable;
	}

	function enabledUserRelatedData()
	{
		return $this->tracking_user_related ? true : false;
	}


	/**
	* check wether user related tracking is enabled or not
	*/
	function _enabledUserRelatedData()
	{
		global $ilSetting;
		return (boolean) $ilSetting->get('save_user_related_data');
	}

	function setValidTimeSpan($a_time_span)
	{
		$this->valid_time = (int) $a_time_span;

		return true;
	}

	function getValidTimeSpan()
	{
		return (int) $this->valid_time;
	} 
	
	function _getValidTimeSpan()
	{
		global $ilias;
		
		return (int) $ilias->getSetting("tracking_time_span",DEFAULT_TIME_SPAN);
	}

	// BEGIN ChangeEvent
	/**
	* Sets the changeEventTrackingEnabled property.
	* 
	* @param	boolean	new value
	* @return	void
	*/
	public function setChangeEventTrackingEnabled($newValue)
	{
		$this->is_change_event_tracking_enabled = $newValue;
	}
	/**
	* Gets the changeEventTrackingEnabled property.
	* 
	* @return	boolean	value
	*/
	public function isChangeEventTrackingEnabled()
	{
		return $this->is_change_event_tracking_enabled;
	}
	// END ChangeEvent

	function updateSettings()
	{
		global $ilias;

		$ilias->setSetting("enable_tracking",$this->getActivationStatus());
		$ilias->setSetting("save_user_related_data",$this->enabledUserRelatedData() ? 1 : 0);
		$ilias->setSetting("tracking_time_span",$this->getValidTimeSpan());

		// BEGIN ChangeEvent
		require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
		if ($this->is_change_event_tracking_enabled != ilChangeEvent::_isActive())
		{
			if ($this->is_change_event_tracking_enabled)
			{
				ilChangeEvent::_activate();
			}
			else
			{
				ilChangeEvent::_deactivate();
			}
		}
		// END ChangeEvent


		return true;
	}

	function validateSettings()
	{
		if(!is_numeric($time = $this->getValidTimeSpan()) or
		   $time < 1 or
		   $time > 9999)
		{
			return false;
		}
		return true;
	}

	/**
	* get total number of tracking records
	*/
	function getRecordsTotal()
	{
		global $ilDB;

		$q = "SELECT count(*) AS cnt FROM ut_access";
		$cnt_set = $ilDB->query($q);

		$cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $cnt_rec["cnt"];
	}

	/**
	* get total number of accesses per month
	*/
	function getMonthTotalOverview()
	{
		global $ilDB;

		$q = "SELECT count(*) as cnt, count(distinct user_id) as user_cnt, date_format(acc_time,'%Y-%m') AS month FROM ut_access".
			" GROUP BY month ORDER BY month DESC";
		$min_set = $ilDB->query($q);
		$months = array();
		while ($min_rec = $min_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$months[] = array("month" => $min_rec["month"],
				"cnt" => $min_rec["cnt"], "user_cnt" => $min_rec["user_cnt"]);
		}
		return $months;
	}

	/**
	* get total number of records older than given month (YYYY-MM)
	*/
	function getTotalOlderThanMonth($a_month)
	{
		global $ilDB;

		$q = "SELECT count(*) as cnt, date_add('$a_month-01', INTERVAL 1 MONTH) as d FROM ut_access WHERE acc_time <= ".
			"date_add('$a_month-01', INTERVAL 1 MONTH)";

		$cnt_set = $ilDB->query($q);
		$cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC);
//echo "cnt:".$cnt_rec["cnt"].":date:".$cnt_rec["d"].":";

		return $cnt_rec["cnt"];
	}

	/**
	* get total number of records older than given month (YYYY-MM)
	*/
	function getAccessTotalPerUser($a_condition, $a_searchTermsCondition="",$a_objectCondition="")
	{
		global $ilDB;

		$q = "SELECT count(*) as cnt, user_id FROM ut_access "
			.($a_searchTermsCondition != "" ? $a_searchTermsCondition : " WHERE ")
			.$a_condition
			.$a_objectCondition
			." GROUP BY user_id";

		$cnt_set = $ilDB->query($q);

		$acc = array();
		while ($cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$name = ilObjUser::_lookupName($cnt_rec["user_id"]);

			if ($cnt_rec["user_id"] != 0)
			{
				$acc[] = array("user_id" => $cnt_rec["user_id"],
					"name" => $name["lastname"].", ".$name["firstname"],
					"cnt" => $cnt_rec["cnt"]);
			}
		}
		return $acc;
	}
	
	/**
	* get total number of records older than given month (YYYY-MM)
	*/
	function getAccessTotalPerObj($a_condition, $a_searchTermsCondition="")
	{
		global $ilDB;
		$q = "SELECT count(acc_obj_id) AS cnt, acc_obj_id FROM ut_access "
			.($a_searchTermsCondition != "" ? $a_searchTermsCondition : " WHERE ")
			.$a_condition
			." GROUP BY acc_obj_id";
		$cnt_set = $ilDB->query($q);
		//echo "q:".$q;

		$acc = array();
		while ($cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($cnt_rec["cnt"] != "")
			{
				
				$acc[] = array("id" => $cnt_rec["acc_obj_id"],
					"title" => ilObject::_lookupTitle($cnt_rec["acc_obj_id"]),
					"author" => $this->getOwnerName($cnt_rec["acc_obj_id"]),
					"duration" => $this->getDuration($cnt_rec["acc_obj_id"]),
					"cnt" => $cnt_rec["cnt"]);
			}
		}
		return $acc;
	}

	function getDuration($a_obj_id)
	{
		global $ilDB;
		$q = "SELECT AVG(spent_seconds) FROM read_event"
			." WHERE obj_id = " . $ilDB->quote($a_obj_id)
			." GROUP BY obj_id";
		$res = $ilDB->query($q);
		$data = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $data["spent_seconds"];
	}

	/**
	* get per user of records older than given month (YYYY-MM)
	*/
	function getAccessPerUserDetail($a_condition, $a_searchTermsCondition="",$a_objectCondition="")
	{
		global $ilDB;

		$q = "SELECT id, user_id,client_ip,acc_obj_id,language ,acc_time FROM ut_access "
			.($a_searchTermsCondition != "" ? $a_searchTermsCondition : " WHERE ")
			.$a_condition
			.$a_objectCondition
			." GROUP BY id";

		$cnt_set = $ilDB->query($q);
		$acc = array();
		while($cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$name = ilObjUser::_lookupName($cnt_rec["user_id"]);

			if ($cnt_rec["user_id"] != 0)
			{
				$acc[] = array("user_id" => $cnt_rec["user_id"],
					"name" => $name["lastname"].", ".$name["firstname"],
					"client_ip" => $cnt_rec["client_ip"],
					"acc_obj_id" => ilObject::_lookupTitle($cnt_rec["acc_obj_id"]),
					"language" => $cnt_rec["language"],
					"acc_time" => $cnt_rec["acc_time"]
					);
			}
		}

		return $acc;
	}
	/**
	* delete tracking data of month (YYYY-MM) and before
	*/
	function deleteTrackingDataBeforeMonth($a_month)
	{
		global $ilDB;

		$q = "DELETE FROM ut_access WHERE acc_time <= ".
			"date_add('$a_month-01', INTERVAL 1 MONTH)";

		$ilDB->query($q);
	}


	/**
	* get all author
	*/
	function allAuthor($a_type,$type)
	{
		global $ilDB;

		$q = "SELECT distinct A.obj_id,A.type,A.title FROM object_data as A,object_data as B WHERE A.type = ".
			$ilDB->quote($a_type)." AND A.obj_id = B.owner AND B.type=".$ilDB->quote($type);
		//echo $q;
		$author = $ilDB->query($q);
		$all = array();
		while ($aauthor = $author->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$all[] = array("title" => $aauthor["title"],
					"obj_id" =>$aauthor["obj_id"]);
		}
		return $all;
	}

	/**
	* get author's all lm or tst
	*/
	function authorLms($id,$type)
	{
		global $ilDB;

		$q = "SELECT title,obj_id FROM object_data WHERE owner = ".$ilDB->quote($id)." and type=".$ilDB->quote($type);
		//echo $q."<br>";
		$lms = $ilDB->query($q);
		$all = array();
		while ($alms = $lms->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$all[] = array("title" => $alms["title"],
					"obj_id" =>$alms["obj_id"]);
		}
		return $all;
		
	}

	/**
	* get obj_id of some object
	*/
	function getObjId($title,$type)
	{
		global $ilDB;
		$q ="SELECT obj_id FROM object_data WHERE type = ".$ilDB->quote($type)." and title=".$ilDB->quote($title);
		$id = $ilDB->query($q);
		$obj_id = $id->fetchRow(DB_FETCHMODE_ASSOC);
		return $obj_id["obj_id"];
	}
	
	/**
	* get Test_id of some test
	*/
	function getTestId($id)
	{
		$q = "select obj_id from object_data "
		." where type = 'tst' and "
		." owner = ".$id;
		$res = $this->ilias->db->query($q);
		for ($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	
	/**
	* Return the counts of search results
	*/
	function countResults($condition)
	{
		$q = "SELECT count(*) from ut_access "
		." WHERE "
		.$condition;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	
	/**
	* Return the owner name of the object
	*/
	function getOwnerName($id)
	{
		$q =" select A.login from usr_data as A, object_data as B where A.usr_id=B.owner and B.obj_id = ".$id;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}

	// PRIVATE
	function __readSettings()
	{
		global $ilias;

		#$this->enableTracking($ilias->getSetting("enable_tracking",0));
		$this->status = $ilias->getSetting('enable_tracking',UT_INACTIVE_BOTH);
		$this->enableUserRelatedData($ilias->getSetting("save_user_related_data",0));
		$this->setValidTimeSpan($ilias->getSetting("tracking_time_span",DEFAULT_TIME_SPAN));

		// BEGIN ChangeEvent
		require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
		$this->is_change_event_tracking_enabled = ilChangeEvent::_isActive();
		// END ChangeEvent

		return true;
	}

	function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM ut_access WHERE user_id = '".$a_usr_id."'";
		$ilDB->query($query);

		$query = "DELETE FROM read_event WHERE usr_id = '".$a_usr_id."'";
		$ilDB->query($query);

		$query = "DELETE FROM write_event WHERE usr_id = '".$a_usr_id."'";
		$ilDB->query($query);
		
		$query = "DELETE FROM ut_lp_filter WHERE usr_id = '".$a_usr_id."'";
		$ilDB->query($query);
		
		$query = "DELETE FROM ut_lp_marks WHERE usr_id = '".$a_usr_id."'";
		$ilDB->query($query);

		$st = $ilDB->prepareManip("DELETE FROM ut_online WHERE usr_id = ?",
			array("integer"));
		$ilDB->execute($st, array($a_usr_id));

		return true;
	}

		
} // END class.ilObjUserTracking
?>
