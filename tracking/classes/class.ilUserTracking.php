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
* registration form for new users
*
* @author Arlon Yin <arlon_yin@sina.com.cn>
*
* @package ilias-core
*/



class ilUserTracking {

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
	var $accTime;
	
	function ilUserTracking()
	{
		global $ilias,$tpl,$lng;

		$this->ilias	=& $ilias;
		$this->tpl		=& $tpl;
		$this->lng		=& $lng;

	}

	function getaccSubTitle($obj_id) {
		$q = "SELECT title FROM object_data "
			." WHERE "
			." obj_id = ".$obj_id;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];	
	}
	function getaccSubType($obj_id) {
		$q = "SELECT type FROM object_data "
			." WHERE "
			." obj_id = ".$obj_id;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];	
	}

	function getaccSubId($ref_id) {
		$q = "SELECT obj_id FROM object_reference "
			." WHERE "
			." ref_id = ".$ref_id;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}

	function getLanguage($user_id) {
		$q = "SELECT value from usr_pref "
			." WHERE "
			." keyword = 'language' "
			." AND "
			." usr_id = ".$user_id;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	function getSessionId($user_id) {
		$q = "SELECT session_id from usr_session "
		." WHERE "
		." user_id = ".$user_id;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	function currentSessionId($user_id) {
		$q = "SELECT session_id from ut_access "
		." WHERE "
		." user_id = ".$user_id
		." order by acctime desc limit 1 ";
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	function currentId($user_id){
		$q = "SELECT acc_obj_id from ut_access "
		." WHERE "
		." user_id = ".$user_id
		." order by acctime desc limit 1 ";
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
		

	function insertUserTracking($user_id,$ref_id,$client_ip) 
	{
		if(($this->getSessionId($user_id) == $this->currentSessionId($user_id)) and ($this->getaccSubId($ref_id)==$this->currentId($user_id)))
		{
			return true;
		}
		else
		{
			if(strstr($_SERVER["HTTP_USER_AGENT"], "MSIE"))
			{
				$browser = 'msie';
			}
			else if(strstr($_SERVER["HTTP_USER_AGENT"], "NETS"))
			{
				$browser = 'netscrape';
			}
			else
			{
				$browser = 'others';
			}
			$q = "INSERT INTO ut_access ("
				."user_id,client_ip,"
				."acc_obj_type,acc_obj_id,language,browser,session_id,acctime"
				.") VALUES ("
				."'".$user_id."',"
				."'".$client_ip."',"
				."'".$this->getaccSubType($this->getaccSubId($ref_id))."',"
				."'".$this->getaccSubId($ref_id)."',"
				."'".$this->getLanguage($user_id)."',"
				."'".$browser."',"
				."'".$this->getSessionId($user_id)."','"
				.date("y-m-d H:i")
				."')";
		   $this->ilias->db->query($q);
		}
	}
	function TestTitle($user_id)
	{
		$q = " SELECT title from object_data "
			." WHERE type = 'tst'"
			." AND owner = ".$user_id;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	function searchTitle($user_id)
	{
		$q = " SELECT title from object_data "
			." WHERE type = 'lm '"
			." AND owner = ".$user_id;
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
		$q = "SELECT obj_id from object_data "
		." WHERE type = 'lm' and "
		." owner = ".$id;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	function getSubTest($id)
	{
		$q = "SELECT obj_id from object_data "
		." WHERE type = 'tst' and "
		." owner = ".$id;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
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
	function countResults($condition)
	{
		$q = "SELECT count(*) from ut_access "
		." WHERE "
		.$condition;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	function searchResults($condition)
	{
		$q = "SELECT a.login,b.acc_obj_type,b.language,b.client_ip,b.acctime "
			." FROM usr_data as a,ut_access as b "
			." WHERE a.usr_id=b.user_id "
			." AND ".$condition;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	function searchTestResults($condition)
	{
		$q = "SELECT a.login,b.acc_obj_type,b.client_ip,b.acctime "
			." FROM usr_data as a,ut_access as b "
			." WHERE a.usr_id=b.user_id "
			." AND ".$condition;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	function searchUserId($condition)
	{
		$q = "SELECT user_id from ut_access where ".$condition;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	function searchTestId($condition)
	{
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
		$q = "select obj_id from object_data where type = 'tst' and title = '".$test."'";
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	function countNum($from,$from1,$condition)
	{
		$q = "SELECT count(*) from ut_access "
			." WHERE (acctime > '".$from
			."' AND acctime <='".$from1."')"
			." AND ".$condition;
			//echo $condition;echo "<br>";
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	function selectTime($from,$to,$condition)
	{
		$q = "SELECT acctime from ut_access "
			." WHERE (acctime >= '".$from
			."' AND acctime <='".$to."')"
			." AND ".$condition;
			echo $q;
			echo "<br>";
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	function getlm($id)
	{
		$q = "SELECT title from object_data "
		." WHERE "
		." type = 'lm' "
		." and "
		." owner = ".$id;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	function getTest($id)
	{
		$q = "SELECT title from object_data "
		." WHERE "
		." type = 'tst' "
		." and "
		." owner = ".$id;
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}

	//This function should be added into the gui class.
	function _tables()
	{
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("personal_desktop"));
		$this->tpl->addBlockFile("TABS","tabs","tpl.tabs.html");
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK","user_personaldesktop.php");
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("overview"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK","user_profile.php");
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("personal_profile"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK","dataplaner.php");
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("calendar"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK","usr_bookmarks.php");
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("bookmarks"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK","tracking.php");
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("usertracking"));
		$this->tpl->parseCurrentBlock();
	}
	function _locator()
	{
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("usertracking"));
		$this->tpl->setVariable("LINK_ITEM", "tracking.php");
		$this->tpl->parseCurrentBlock();
	}
}
?>