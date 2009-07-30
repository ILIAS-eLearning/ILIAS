<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* registration form for new u ilLearningProgresssers
*
* @author Arlon Yin <arlon_yin@sina.com.cn>
* $Id$
*
* @package ilias-core
*/
class ilTracking {

	var $objId;
	var $userId;
	var $actionType;
	var $phpScript;
	var $clientIp;
	var $accObjType;
	var $accObjId;
	var $accSubType;
	var $accSubId;
	var $lanugage;
	var $browser;
	var $sessionId;
	var $acc_time;

	var $db;
	
	function ilTracking()
	{
		global $ilias,$tpl,$lng,$ilDB;

		$this->ilias	=& $ilias;
		$this->tpl		=& $tpl;
		$this->lng		=& $lng;
		$this->db = $ilDB;

	}

	/**
	* get last access data of current user
	*/
	function _getLastAccess()
	{
		global $ilUser, $ilDB;

		$ilDB->setLimit(1);
		$q = "SELECT * from ut_access "
		." WHERE "
		." user_id = ".$ilDB->quote($ilUser->getId(),'integer')
		." ORDER BY acc_time DESC";
		$res = $ilDB->query($q);
		return $res->fetchRow(DB_FETCHMODE_ASSOC);
	}
	
	function _hasEntry($a_obj_id, $a_obj_type,$a_sub_id = 0, $a_sub_type = "")
	{
		global $ilDB;
		
		// We query for the session_id since it is more unique than the user_id. 
		
		$query = "SELECT COUNT(id) as num_entries FROM ut_access ".
			"WHERE session_id = ".$ilDB->quote(session_id(), "text")." ".
			"AND acc_obj_id = ".$ilDB->quote($a_obj_id, "integer")." ".
			"AND acc_sub_id = ".$ilDB->quote($a_sub_id, "text")." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $row->num_entries ? true : false;
	}

	/**
	* track access to an object by current user
	*
	* @param	int			$a_obj_id			object id
	* @param	string		$a_obj_type			object type (e.g. "lm")
	* @param	int			$a_sub_id			subobject id
	* @param	string		$a_sub_type			subobject type (e.g. "pg")
	* @param	string		$a_action_type		"read", "write", ...
	*/
	function _trackAccess($a_obj_id, $a_obj_type,$a_sub_id = 0, $a_sub_type = "", $a_action_type = "read")
	{
		global $ilUser, $ilDB;


		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(!ilObjUserTracking::_enabledTracking() and !ilObjUserTracking::_enabledLearningProgress())
		{
			return false;
		}

		include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
		ilLearningProgress::_tracProgress($ilUser->getId(),$a_obj_id,$a_obj_type);

		if (ilObjUserTracking::_enabledUserRelatedData())
		{
			$user_id = $ilUser->getId();
		}
		else
		{
			$user_id = 0;
		}

		$client_ip = getenv("REMOTE_ADDR");
		$script = substr($_SERVER["SCRIPT_FILENAME"], strlen(IL_ABSOLUTE_PATH) - 1,
			strlen($_SERVER["SCRIPT_FILENAME"]) - strlen(IL_ABSOLUTE_PATH) + 1);
		$language = $ilUser->getLanguage();
		$session_id = session_id();

		#$last_access = ilTracking::_getLastAccess();


		if(ilTracking::_hasEntry($a_obj_id, $a_obj_type,$a_sub_id, $a_sub_type))
		{
			return true;
		}
		$q = "INSERT INTO ut_access ("
			."id,"
			."user_id, action_type, php_script, client_ip,"
			."acc_obj_type, acc_obj_id, acc_sub_type, acc_sub_id,"
			."language, browser, session_id, acc_time, ut_month"
			.") VALUES ("
			.$ilDB->quote($ilDB->nextId('ut_access'),'integer').','
			.$ilDB->quote($user_id, "integer").","
			.$ilDB->quote($a_action_type, "text").","
			.$ilDB->quote($script, "text").","
			.$ilDB->quote($client_ip, "text").","
			.$ilDB->quote($a_obj_type, "text").","
			.$ilDB->quote($a_obj_id, "integer").","
			.$ilDB->quote($a_sub_type, "text").","
			.$ilDB->quote($a_sub_id, "integer").","
			.$ilDB->quote($language, "text").","
			.$ilDB->quote(substr($_SERVER["HTTP_USER_AGENT"],0.255)).","
			.$ilDB->quote($session_id, "text").", "
			.$ilDB->quote(ilUtil::now(), "timestamp").", "
			.$ilDB->quote(substr(ilUtil::now(), 0, 7), "text")
			.")";
	   $ilDB->manipulate($q);
	   
		/*
		if(($session_id == $last_access["session_id"]) &&
			($a_obj_id == $last_access["acc_obj_id"]) &&
			($a_obj_type == $last_access["acc_obj_type"]) &&
			($a_sub_id == $last_access["acc_sub_id"]) &&
			($a_sub_type == $last_access["acc_sub_type"])
			)
		{
			return true;
		}
		else
		{
			$q = "INSERT INTO ut_access ("
				."user_id, action_type, php_script, client_ip,"
				."acc_obj_type, acc_obj_id, acc_sub_type, acc_sub_id,"
				."language, browser, session_id, acc_time"
				.") VALUES ("
				.$ilDB->quote($user_id).","
				.$ilDB->quote($a_action_type).","
				.$ilDB->quote($script).","
				.$ilDB->quote($client_ip).","
				.$ilDB->quote($a_obj_type).","
				.$ilDB->quote($a_obj_id).","
				.$ilDB->quote($a_sub_type).","
				.$ilDB->quote($a_sub_id).","
				.$ilDB->quote($language).","
				.$ilDB->quote($_SERVER["HTTP_USER_AGENT"]).","
				.$ilDB->quote($session_id).", now()"
				.")";
		   $ilDB->query($q);
		}
		*/
	}
	function TestTitle($user_id)
	{
		global $ilDB;
		
		$q = " SELECT title from object_data "
			." WHERE type = ".$ilDB->quote("tst", "text")
			." AND owner = ".$ilDB->quote($user_id ,'integer');
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}



	function numDay($from,$to)
	{
		$from = strtotime($from);
		$to = strtotime($to);
		$dayf = date ("d",$from);
		$dayt = date ("d",$to);
		$yearf = date ("Y",$from); 
		$yeart = date ("Y",$to); 
		$montht = date ("m",$to); 
		$monthf = date ("m",$from); 
		$ret = ( mktime(0,0,0,$montht,$dayt,$yeart) - mktime(0,0,0,$monthf,$dayf,$yearf))/(3600*24); 
		return $ret; 
	}
	function numHour($from,$to)
	{
		$from = strtotime($from);
		$to = strtotime($to);
		$dayf = date ("d",$from); 
		$dayt = date ("d",$to);
		$yearf = date ("Y",$from); 
		$yeart = date ("Y",$to); 
		$montht = date ("m",$to); 
		$monthf = date ("m",$from); 
		$hourt = date ("h",$to);
		$hourf = date ("h",$from);
		$ret = (mktime($hourt,0,0,$montht,$dayt,$yeart)-mktime($hourf,0,0,$monthf,$dayf,$yearf))/3600; 
		$ret = strftime($ret);
		return $ret; 
	}
	function addHour($time)
	{
		$time = strtotime($time);
		$day = date("d",$time);
		$month = date("m",$time);
		$year = date("Y",$time);
		$hour = date("H",$time);
		$min = date("i",$time);
		$sec = date("s",$time);
		$hour = $hour+1;
		$ret = date("H:i:s", mktime($hour,$min,$sec,$month,$day,$year));
		return $ret;
	}
	function addDay($time)
	{
		$time = strtotime($time);
		$day = date("d",$time);
		$month = date("m",$time);
		$year = date("y",$time);
		$min = date("i",$time);
		$hour = date("h",$time);
		$sec = date("s",$time);
		$day = $day + 1;
		$ret = date ("Y-m-d", mktime($hour,$min,$sec,$month,$day,$year));
		return $ret;
	}

	function getSubId($id)
	{
		global $ilDB;
		
		$q = "SELECT obj_id from object_data "
		." where type = ".$ilDB->quote("lm", "text")." and "
		." owner = ".$ilDB->quote($id, "integer");
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	function getSubTest($id)
	{
		global $ilDB;
		
		$q = "SELECT obj_id from object_data "
		." where type = ".$ilDB->quote("tst", "text")." and "
		." owner = ".$ilDB->quote($id, "integer");
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	
	function getTestId($id)
	{
		global $ilDB;
		
		$q = "select obj_id from object_data "
		." where type = ".$ilDB->quote("tst", "text")." and "
		." owner = ".$ilDB->quote($id, "integer");
		$res = $this->ilias->db->query($q);
		for ($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	
	function countResults($condition)
	{
		global $ilDB;
		
		$q = "SELECT count(*) from ut_access "
		." WHERE "
		.$condition;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	
	function searchResults($condition)
	{
		global $ilDB;
		
		$q = "SELECT a.login,b.acc_obj_type,b.language,b.client_ip,b.acc_time "
			." FROM usr_data a,ut_access b "
			." WHERE a.usr_id=b.user_id "
			." AND ".$condition;
//echo $q;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	
	function searchTestResults($condition)
	{
		global $ilDB;
		
		$q = "SELECT a.login,b.acc_obj_type,b.client_ip,b.acc_time "
			." FROM usr_data a,ut_access b "
			." WHERE a.usr_id=b.user_id "
			." AND ".$condition;
//echo $q;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	
	function searchUserId($condition)
	{
		global $ilDB;
		
		$q = "SELECT user_id from ut_access where ".$condition;
//echo $q;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	
	function searchTestId($condition)
	{
		global $ilDB;
		
		$q = "select user_fi from tst_active where ".$condition;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	
	function getPerTestId($test)
	{
		global $ilDB;
		
		$q = "select obj_id from object_data where type = ".
			$ilDB->quote("tst", "text")." and title = ".$ilDB->quote($test, "text");
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	
	function countNum($from,$from1,$condition)
	{
		global $ilDB;
		
		$q = "SELECT count(*) from ut_access "
			." WHERE (acc_time > ".$ilDB->quote($from, "timestamp")
			." AND acc_time <= ".$ilDB->quote($from1, "integer").")"
			." AND ".$condition;
			//echo $condition;echo "<br>";
//echo $q;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	
	function selectTime($from,$to,$condition)
	{
		global $ilDB;
		
		$q = "SELECT acc_time from ut_access "
			." WHERE (acc_time > ".$ilDB->quote($from, "timestamp")
			." AND acc_time <= ".$ilDB->quote($to, "integer").")"
			." AND ".$condition;
//echo $q;
//echo "<br>";
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}

	function getTest($id)
	{
		global $ilDB;
		
		$q = "SELECT title from object_data "
		." WHERE "
		." type = ".$ilDB->quote("tst", "text")
		." and "
		." owner = ".$ilDB->quote($id, "integer");
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
}
?>
