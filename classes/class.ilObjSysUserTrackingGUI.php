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
* Class ilObjSysUserTrackingGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";
require_once "tracking/classes/class.ilUserTracking.php";

class ilObjSysUserTrackingGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSysUserTrackingGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "trac";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff

		// always send a message
		sendInfo($this->lng->txt("object_added"),true);

		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}


	/**
	* display tracking settings form
	*/
	function settingsObject()
	{
		global $tpl,$lng,$ilias;

		// tracking settings
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tracking_settings.html");
		$tpl->setVariable("FORMACTION", "adm_object?ref_id=".$_GET["ref_id"].
			"&cmd=gateway");
		$tpl->setVariable("TXT_TRACKING_SETTINGS", $this->lng->txt("tracking_settings"));
		$tpl->setVariable("TXT_ACTIVATE_TRACKING", $this->lng->txt("activate_tracking"));
		$tpl->setVariable("TXT_USER_RELATED_DATA", $this->lng->txt("save_user_related_data"));
		$tpl->setVariable("TXT_NUMBER_RECORDS", $this->lng->txt("number_of_records"));
		$tpl->setVariable("NUMBER_RECORDS", $this->object->getRecordsTotal());
		$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		if($this->object->_enabledTracking())
		{
			$this->tpl->setVariable("ACT_TRACK_CHECKED", " checked=\"1\" ");
		}

		if($this->object->_enabledUserRelatedData())
		{
			$this->tpl->setVariable("USER_RELATED_CHECKED", " checked=\"1\" ");
		}

		$tpl->parseCurrentBlock();

	}

	/**
	* save user tracking settings
	*/
	function saveSettingsObject()
	{
		// (de)activate tracking
		if ($_POST["act_track"] == "y")
		{
			$this->object->enableTracking(true);
		}
		else
		{
			$this->object->enableTracking(false);
		}

		// (de)activate tracking of user related data
		if ($_POST["user_related"] == "y")
		{
			$this->object->enableUserRelatedData(true);
		}
		else
		{
			$this->object->enableUserRelatedData(false);
		}

		sendinfo($this->lng->txt("msg_obj_modified"), true);
		ilUtil::redirect("adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=settings");
	}

	/**
	* display tracking query form
	*/
	function trackingDataQueryFormObject()
	{
		global $tpl,$lng,$ilias;
		$year = array(2004,2005,2006,2007);
		$month = array(1,2,3,4,5,6,7,8,9,10,11,12);
		$day = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
		//subject module
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_tracking.html");
		//$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$usertracking = new ilUserTracking();
		//$usertracking->_locator();
		//$usertracking->_tables();
		$tpl->setVariable("SEARCH_ACTION", "adm_object.php?ref_id=".$_GET["ref_id"].
			"&cmd=gateway");
		$tpl->setVariable("TXT_TRACKING_DATA", $lng->txt("tracking_data"));
		$tpl->setVariable("TXT_TIME_SEGMENT", $lng->txt("time_segment"));
		$tpl->setVariable("TXT_STATISTIC", $lng->txt("statistic"));
		$tpl->setVariable("TXT_STATISTIC_H", $lng->txt("hours_of_day"));
		$tpl->setVariable("TXT_STATISTIC_D", $lng->txt("days_of_period"));
		$tpl->setVariable("TXT_STATISTIC_U", $lng->txt("user_access"));
		$tpl->setVariable("TXT_USER_LANGUAGE",$lng->txt("user_language"));
		$tpl->setVariable("TXT_LM",$lng->txt("lm"));
		$tpl->setVariable("TXT_SHOW_TR_DATA",$lng->txt("query_data"));
		$tpl->setVariable("TXT_TRACKED_OBJECTS",$lng->txt("tracked_objects"));

		$languages = $lng->getInstalledLanguages();
		include_once "./tracking/classes/class.ilUserTracking.php";
		$tracking = new ilUserTracking();

		// get all learning modules
		$lms = ilObject::_getObjectsDataForType("lm", true);

		foreach($year as $key)
		{
			$tpl->setCurrentBlock("fromyear_selection");
			$tpl->setVariable("YEARFR", $key);
			$tpl->setVariable("YEARF", $key);
			if ($_SESSION["il_track_yearf"] == $key)
			{
				$tpl->setVariable("YEARF_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($month as $key)
		{
			$tpl->setCurrentBlock("frommonth_selection");
			$tpl->setVariable("MONTHFR", $key);
			$tpl->setVariable("MONTHF", $key);
			if ($_SESSION["il_track_monthf"] == $key)
			{
				$tpl->setVariable("MONTHF_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($day as $key)
		{
			$tpl->setCurrentBlock("fromday_selection");
			$tpl->setVariable("DAYFR", $key);
			$tpl->setVariable("DAYF", $key);
			if ($_SESSION["il_track_dayf"] == $key)
			{
				$tpl->setVariable("DAYF_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($day as $key)
		{
			$tpl->setCurrentBlock("today_selection");
			$tpl->setVariable("DAYTO", $key);
			$tpl->setVariable("DAYT", $key);
			if ($_SESSION["il_track_dayt"] == $key)
			{
				$tpl->setVariable("DAYT_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($month as $key)
		{
			$tpl->setCurrentBlock("tomonth_selection");
			$tpl->setVariable("MONTHTO", $key);
			$tpl->setVariable("MONTHT", $key);
			if ($_SESSION["il_track_montht"] == $key)
			{
				$tpl->setVariable("MONTHT_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($year as $key)
		{
			$tpl->setCurrentBlock("toyear_selection");
			$tpl->setVariable("YEARTO", $key);
			$tpl->setVariable("YEART", $key);
			if ($_SESSION["il_track_yeart"] == $key)
			{
				$tpl->setVariable("YEART_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}

		// language selection
		$tpl->setCurrentBlock("language_selection");
		$tpl->setVariable("LANG", $lng->txt("any_language"));
		$tpl->setVariable("LANGSHORT", "0");
		$tpl->parseCurrentBlock();
		foreach ($languages as $lang_key)
		{
			$tpl->setCurrentBlock("language_selection");
			$tpl->setVariable("LANG", $lng->txt("lang_".$lang_key));
			$tpl->setVariable("LANGSHORT", $lang_key);
			if ($_SESSION["il_track_language"] == $lang_key)
			{
				$tpl->setVariable("LANG_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}

		// statistic type
		if ($_SESSION["il_track_stat"] == "d")
		{
			$tpl->setVariable("D_CHK", " checked=\"1\" ");
		}
		else if ($_SESSION["il_track_stat"] == "u")
		{
			$tpl->setVariable("U_CHK", " checked=\"1\" ");
		}
		else
		{
			$tpl->setVariable("H_CHK", " checked=\"1\" ");
		}

		// tracked object type
		if ($_SESSION["il_object_type"] == "tst")
		{
			$tpl->setVariable("TST_CHK", " checked=\"1\" ");
		}
		else
		{
			$tpl->setVariable("LM_CHK", " checked=\"1\" ");
		}

		// learning module selection
		$tpl->setCurrentBlock("lm_selection");
		$tpl->setVariable("LM", 0);
		$tpl->setVariable("LM_SELECT", $this->lng->txt("all_lms"));
		$tpl->parseCurrentBlock();
		foreach ($lms as $lm)
		{
			$tpl->setCurrentBlock("lm_selection");
			$tpl->setVariable("LM", $lm["id"]);
			$tpl->setVariable("LM_SELECT", $lm["title"]." [".$lm["id"]."]");
			if ($_SESSION["il_track_lm"] == $lm["id"])
			{
				$tpl->setVariable("LM_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}


		//test module
		//arlon modified,if there isn't test of the login,the test tracking module will not display!
		$usertracking = new ilUserTracking();
		$result_test = $usertracking->getTestId($_SESSION["AccountId"]);

		$tpl->setVariable("TXT_TEST",$lng->txt("test"));
		$tracking = new ilUserTracking();

		//$test = $tracking->TestTitle($_SESSION["AccountId"]);

		$tsts = ilObject::_getObjectsDataForType($type, true);
		$tpl->setCurrentBlock("test_selection");
		$tpl->setVariable("TEST", 0);
		$tpl->setVariable("TEST_SELECT", $this->lng->txt("all_tsts"));
		$tpl->parseCurrentBlock();
		foreach($tsts as $tst)
		{
			$tpl->setCurrentBlock("test_selection");
			$tpl->setVariable("TEST", $tst["id"]);
			$tpl->setVariable("TEST_SELECT", $tst["title"]." [".$tst["id"]."]");
			$tpl->parseCurrentBlock();
		}

	}

	/**
	* output tracking data
	*/
	function outputTrackingDataObject()
	{
		global $tpl,$lng,$ilias;

		// save selected values in session
		$_SESSION["il_track_yearf"] = $_POST["yearf"];
		$_SESSION["il_track_yeart"] = $_POST["yeart"];
		$_SESSION["il_track_monthf"] = $_POST["monthf"];
		$_SESSION["il_track_montht"] = $_POST["montht"];
		$_SESSION["il_track_dayf"] = $_POST["dayf"];
		$_SESSION["il_track_dayt"] = $_POST["dayt"];
		$_SESSION["il_track_stat"] = $_POST["stat"];
		$_SESSION["il_track_language"] = $_POST["language"];
		$_SESSION["il_track_lm"] = $_POST["lm"];
		$_SESSION["il_track_tst"] = $_POST["tst"];
		$_SESSION["il_object_type"] = $_POST["object_type"];

		$yearf = $_POST["yearf"];
		$monthf = $_POST["monthf"];
		$dayf = $_POST["dayf"];
		$yeart = $_POST["yeart"];
		$montht= $_POST["montht"];
		$dayt = $_POST["dayt"];
		$from = $yearf."-".$monthf."-".$dayf;
		$to = $yeart."-".$montht."-".$dayt;
		//$from = mktime($monthf,$dayf,$yearf);
		//$to = mktime($montht,$dayt,$yeart);
		//$from = strtotime($from);
		//$to = strtotime($to);
		//echo $from;
		//echo "<br>";
		//echo $to;
		if(($yearf > $yeart)or($yearf==$yeart and $monthf>$montht)or($yearf==$yeart and $monthf==$montht and $dayf>$dayt))
		{
			$this->ilias->raiseError($lng->txt("msg_err_search_time"),
				$this->ilias->error_obj->MESSAGE);
		}

		/*
		if($_POST["stat"]!='h' and $_POST["stat"]!='d')
		{
			$this->ilias->raiseError($lng->txt("msg_no_search_time"),
				$this->ilias->error_obj->MESSAGE);
		}*/

		$usertracking = new ilUserTracking();
		//$result_id = $usertracking->getSubId($_SESSION["AccountId"]);
		$condition = $this->getCondition()." and acc_time >='".$from."' and acc_time< '".$to."'";

		if(count($usertracking->countResults($condition))== 0)
		{
			$this->ilias->raiseError($lng->txt("msg_no_search_result"),
				$this->ilias->error_obj->MESSAGE);
		}

		include_once "./classes/class.ilTableGUI.php";
		$tbl = new ilTableGUI();
		$tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table_ut.html");
		$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		//$usertracking->_locator();
		//$usertracking->_tables();
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		// user access statistic
		if($_POST["stat"] == "u")	// user access
		{
			$title_new = array("login", "learning module", "language","client ip","time");

			// condition
			$condition = $this->getCondition()." and b.acc_time>='".$from."' and b.acc_time<'".$to."'";

			$this->data["data"] = $usertracking->searchResults($condition);
			$this->maxcount = count($this->data["data"]);

			// check if result is given
			if(count($this->data["data"])<1)
			{
				$this->ilias->raiseError($lng->txt("msg_no_search_result"),
					$this->ilias->error_obj->MESSAGE);
			}

			$tbl->setTitle($lng->txt("search_result"),0,0);
			foreach ($title_new as $val)
			{
				$header_names[] = $lng->txt($val);
			}
			$tbl->setHeaderNames($header_names);
			//$tbl->setColumnWidth(array("15","75%","25%"));
			$tbl->setMaxCount($this->maxcount);
			$tbl->render();
			if (is_array($this->data["data"]))
			{
			//table cell
				for ($i=0; $i < count($this->data["data"]); $i++)
				{
					$data = $this->data["data"][$i];
					$css_row = $i%2==0?"tblrow1":"tblrow2";
					foreach ($this->data["data"][$i] as $key => $val)
					{
						if($val=="")
						{
							$val=0;
						}
						$tpl->setCurrentBlock("text");
						$tpl->setVariable("TEXT_CONTENT", $val);
						$tpl->parseCurrentBlock();
						$tpl->setCurrentBlock("table_cell");
						$tpl->parseCurrentBlock();
					} //foreach
					$tpl->setCurrentBlock("tbl_content");
					$tpl->setVariable("CSS_ROW", $css_row);
					$tpl->parseCurrentBlock();
				} //for
				//$tpl->show();
			}
		}
		else //user not selected
		{
			$title_new = array("count","learning module","language","time");

			include_once "./classes/class.ilTableGUI.php";
			$tbl = new ilTableGUI();
			$tbl->setTitle($lng->txt("search_result"),0,0);
			foreach ($title_new as $val)
			{
				$header_names[] = $lng->txt($val);
			}
			$tbl->setHeaderNames($header_names);

			if($_POST["stat"]=='h')
			{
				$num = 24;
				$tbl->setMaxCount($num);
			}
			else
			{
				$num = $usertracking->numDay($from,$to);
				$from1 = $usertracking->addDay($from);
				if ($_POST["lm"] == 0)
				{
					$tbl->setMaxCount($num * count($lms));
				}
				else
				{
					$tbl->setMaxCount($num);
				}
			}
			$tbl->render();

			// contition
			$condition = $this->getCondition();

			if($_POST["stat"]=='h')		//hours of day
			{
				$time = $usertracking->selectTime($from,$to,$condition);
				for($i=0;$i<24;$i++)
				{
					$k = $i+1;

					// count number of accesses in hour $i
					$cou = 0;
					for($j=0;$j<count($time);$j++)
					{
						$time1 = strtotime($time[$j][0]);
						$day = date("d",$time1);
						$month = date("m",$time1);
						$year = date("Y",$time1);
						$hour = date("H",$time1);
						$min = date("i",$time1);
						$sec = date("s",$time1);
						$numb = date("H",mktime($hour,$min,$sec,$month,$day,$year));
						$numb = intval($numb);
						if($numb >=$i and $numb <$k)
						{
							$cou=$cou+1;
						}
					}
					$data[0] = $cou;
					if ($_POST["lm"] != 0) //lm selected
					{
						$data[1] = $_POST["lm"];
					}
					else
					{
						$data[1] = "all of your subjects!";
					}
					$data[2] = $_POST["language"];
					$data[3] = $i.":00:00  ~  ".$k.":00:00";
					$css_row = $i%2==0?"tblrow1":"tblrow2";
					foreach ($data as $key => $val)
					{

						$tpl->setCurrentBlock("text");
						$tpl->setVariable("TEXT_CONTENT", $val);
						$tpl->parseCurrentBlock();
						$tpl->setCurrentBlock("table_cell");
						$tpl->parseCurrentBlock();
					}
					$tpl->setCurrentBlock("tbl_content");
					$tpl->setVariable("CSS_ROW", $css_row);
					$tpl->parseCurrentBlock();

				} //for
			}
			else //day selected
			{
				for($i=0;$i<$num;$i++)
				{
					$data[0] = $usertracking->countNum($from,$from1,$condition);
					if ($_POST["lm"] != 0) //lm selected
					{
						$data[1] = $_POST["lm"];
					}
					else
					{
						$data[1] = "all of your subjects!";
					}
					$data[2] = $_POST["language"];
					$data[3] = $from."  ~  ".$from1;
					$css_row = $i%2==0?"tblrow1":"tblrow2";

					foreach ($data as $key => $val)
					{
						$tpl->setCurrentBlock("text");
						$tpl->setVariable("TEXT_CONTENT", $val);
						$tpl->parseCurrentBlock();
						$tpl->setCurrentBlock("table_cell");
						$tpl->parseCurrentBlock();
					}
					$tpl->setCurrentBlock("tbl_content");
					$tpl->setVariable("CSS_ROW", $css_row);
					$tpl->parseCurrentBlock();
					$from = $from1;
					$from1 = $usertracking->addDay($from);
				} //for
			}
		}//else
	}

	/**
	* get complete condition string
	*/
	function getCondition()
	{
		$lang_cond = $this->getLanguageCondition();
//echo ":$lang_cond:";
		if ($lang_cond == "")
		{
			return $this->getObjectCondition();
		}
		else
		{
			return $lang_cond." AND ".$this->getObjectCondition();
		}
	}


	/**
	* get object condition string
	*/
	function getObjectCondition()
	{
		global $ilDB;

		$type = $_POST["object_type"];

		$condition = "acc_obj_type = ".$ilDB->quote($type);

		if($_POST[$type] != 0)
		{
			$condition.= " and acc_obj_id = ".
				$ilDB->quote($_POST[$type]);
		}
		else
		{
			$objs = ilObject::_getObjectsDataForType($type, true);
//echo count($objs).":";
			if (count($objs) > 0)
			{
				$condition.= " and (";
				$or = "";
				foreach($objs as $obj)
				{
					$condition = $condition." $or acc_obj_id=".
						$obj["id"];
					$or = " or ";
				}
				$condition.= ")";
			}
		}

		return $condition;
	}

	/**
	* get language condition string
	*/
	function getLanguageCondition()
	{
		global $ilDB;

		if ($_POST["language"] != "0")
		{
			return "ut_access.language =".$ilDB->quote($_POST["language"]);
		}

		return "";
	}



} // END class.ilObj<module_name>
?>
