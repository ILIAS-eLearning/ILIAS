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
* tracking script used to call the user tracking objects
*
* @author Arlon_yin <arlon.yin@hartung.com.cn>
* @version $Id$
*
*/
require_once "./include/inc.header.php";
require_once "./tracking/classes/class.ilUserTracking.php";
require_once "./classes/class.ilUtil.php";
require_once "./Modules/Test/classes/class.ilObjTest.php";

die("This script call is deprecated");


switch ($_GET["cmd"])
{
	case "search":
		searchForm();
		break;
	case "test":
		searchTest();
		break;
	default:
		conditionForm();
		break;
}
function conditionForm ()
{
		global $tpl,$lng,$ilias;
		$year = array(2004,2005,2006,2007);
		$month = array(1,2,3,4,5,6,7,8,9,10,11,12);
		$day = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
		//subject module
		$tpl->addBlockFile("CONTENT", "content", "tpl.usr_tracking.html");
		$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$usertracking = new ilUserTracking();
		$usertracking->_locator();
		$usertracking->_tables();
		$tpl->setVariable("SEARCH_ACTION", "tracking.php?cmd=search");
		$tpl->setVariable("TXT_SUBJECT_MODULE", $lng->txt("subject_module"));
		$tpl->setVariable("TXT_TIME_SEGMENT", $lng->txt("time_segment"));
		$tpl->setVariable("TXT_TIME", $lng->txt("time"));
		$tpl->setVariable("TXT_TIME_H", $lng->txt("time_h"));
		$tpl->setVariable("TXT_TIME_D", $lng->txt("time_d"));
		$tpl->setVariable("TXT_SEARCH_FOR", $lng->txt("search_for"));
		$tpl->setVariable("TXT_USER", $lng->txt("user"));
		$tpl->setVariable("TXT_LANGUAGE",$lng->txt("language"));
		$tpl->setVariable("TXT_LM",$lng->txt("lm"));
		$tpl->setVariable("BTN_SEARCH",$lng->txt("search"));
		
		$languages = $lng->getInstalledLanguages();
		include_once "./tracking/classes/class.ilUserTracking.php";
		$tracking = new ilUserTracking();
		$lm = $tracking->searchTitle($_SESSION["AccountId"]);

		foreach($year as $key)
		{
			$tpl->setCurrentBlock("fromyear_selection");
			$tpl->setVariable("YEARFR", $key);
			$tpl->setVariable("YEARF", $key);
			$tpl->parseCurrentBlock();
		}
		foreach($month as $key)
		{
			$tpl->setCurrentBlock("frommonth_selection");
			$tpl->setVariable("MONTHFR", $key);
			$tpl->setVariable("MONTHF", $key);
			$tpl->parseCurrentBlock();
		}
		foreach($day as $key)
		{
			$tpl->setCurrentBlock("fromday_selection");
			$tpl->setVariable("DAYFR", $key);
			$tpl->setVariable("DAYF", $key);
			$tpl->parseCurrentBlock();
		}
		foreach($day as $key)
		{
			$tpl->setCurrentBlock("today_selection");
			$tpl->setVariable("DAYTO", $key);
			$tpl->setVariable("DAYT", $key);
			$tpl->parseCurrentBlock();
		}
		foreach($month as $key)
		{
			$tpl->setCurrentBlock("tomonth_selection");
			$tpl->setVariable("MONTHTO", $key);
			$tpl->setVariable("MONTHT", $key);
			$tpl->parseCurrentBlock();
		}
		foreach($year as $key)
		{
			$tpl->setCurrentBlock("toyear_selection");
			$tpl->setVariable("YEARTO", $key);
			$tpl->setVariable("YEART", $key);
			$tpl->parseCurrentBlock();
		}
		foreach ($languages as $lang_key)
		{		
			$tpl->setCurrentBlock("language_selection");
			$tpl->setVariable("LANG", $lng->txt("lang_".$lang_key));
			$tpl->setVariable("LANGSHORT", $lang_key);
			$tpl->parseCurrentBlock();
		}
		for($i=0;$i<count($lm);$i++)
		{
			$tpl->setCurrentBlock("lm_selection");
			$tpl->setVariable("LM",$lm[$i][0]);
			$tpl->setVariable("LM_SELECT",$lng->txt($lm[$i][0]));
			$tpl->parseCurrentBlock();
		}
		//test module
		//arlon modified,if there isn't test of the login,the test tracking module will not display!
		$usertracking = new ilUserTracking();
		$result_test = $usertracking->getTestId($_SESSION["AccountId"]);
		if($result_test!="")
		{
			$tpl->addBlockFile("TESTMODULE", "testmodule", "tpl.test_tracking.html");
			$tpl->setVariable("TEST_ACTION", "tracking.php?cmd=test");
			$tpl->setVariable("TXT_TEST_MODULE", $lng->txt("test_module"));
			$tpl->setVariable("TXT_TIME_SEGMENT", $lng->txt("time_segment"));
			$tpl->setVariable("TXT_SEARCH_FOR", $lng->txt("search_for"));
			$tpl->setVariable("TXT_USER", $lng->txt("user"));
			$tpl->setVariable("TXT_TEST",$lng->txt("test"));
			$tpl->setVariable("BTN_TEST",$lng->txt("search"));
			$tracking = new ilUserTracking();
			$test = $tracking->TestTitle($_SESSION["AccountId"]);
			for($i=0;$i<count($test);$i++)
			{
				$tpl->setCurrentBlock("test_selection");
				$tpl->setVariable("TEST",$test[$i][0]);
				$tpl->setVariable("TEST_SELECT",$lng->txt($test[$i][0]));
				$tpl->parseCurrentBlock();
			}
			foreach($year as $key)
			{
				$tpl->setCurrentBlock("fromtyear_selection");
				$tpl->setVariable("YEARFR", $key);
				$tpl->setVariable("YEARF", $key);
				$tpl->parseCurrentBlock();
			}
			foreach($month as $key)
			{
				$tpl->setCurrentBlock("fromtmonth_selection");
				$tpl->setVariable("MONTHFR", $key);
				$tpl->setVariable("MONTHF", $key);
				$tpl->parseCurrentBlock();
			}
			foreach($day as $key)
			{
				$tpl->setCurrentBlock("fromtday_selection");
				$tpl->setVariable("DAYFR", $key);
				$tpl->setVariable("DAYF", $key);
				$tpl->parseCurrentBlock();
			}
			foreach($day as $key)
			{
				$tpl->setCurrentBlock("totday_selection");
				$tpl->setVariable("DAYTO", $key);
				$tpl->setVariable("DAYT", $key);
				$tpl->parseCurrentBlock();
			}
			foreach($month as $key)
			{
				$tpl->setCurrentBlock("totmonth_selection");
				$tpl->setVariable("MONTHTO", $key);
				$tpl->setVariable("MONTHT", $key);
				$tpl->parseCurrentBlock();
			}
			foreach($year as $key)
			{
				$tpl->setCurrentBlock("totyear_selection");
				$tpl->setVariable("YEARTO", $key);
				$tpl->setVariable("YEART", $key);
				$tpl->parseCurrentBlock();
			}
		}
		$tpl->show();
		

}

function searchTest()
{
	global $tpl,$lng,$ilias;
	
	$yearf = $_POST["Fobject"]["yearf"];
	$monthf = $_POST["Fobject"]["monthf"];
	$dayf = $_POST["Fobject"]["dayf"];
	$yeart = $_POST["Fobject"]["yeart"];
	$montht= $_POST["Fobject"]["montht"];
	$dayt = $_POST["Fobject"]["dayt"];
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
		sendInfo($lng->txt("msg_err_search_time"),true);
		header("location:tracking.php");
		exit();
	}
	if($_POST["time"]!='ht' and $_POST["time"]!='dt')
	{
		sendInfo($lng->txt("msg_no_search_time"),true);
		header("location:tracking.php");
		exit();
	}
	$usertracking = new ilUserTracking();
	$result_id = $usertracking->getSubTest($_SESSION["AccountId"]);
	$condition = "(acc_obj_id =". $result_id[0][0];
	for($i=1;$i < count($result_id);$i++)
	{
	 $condition = $condition." or acc_obj_id=".$result_id[$i][0];
	}
	$condition = $condition. " ) and acc_time >='".$from."' and acc_time< '".$to."'";

	if(count($usertracking->countResults($condition))== 0)
	{
		sendInfo($lng->txt("msg_no_search_result").true);
		header("location:tracking.php");
		exit();
	}
	include_once "./classes/class.ilTableGUI.php";
	$tbl = new ilTableGUI();

	$tpl->addBlockfile("CONTENT", "content", "tpl.table_ut.html");
	$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	$usertracking->_locator();
	$usertracking->_tables();
	$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");
	if($_POST["search_for"]["usr"]=="usr")//user selected
	{
		$title_new = array("login", "test module","client ip","time");
		if($_POST["search_for"]["learn"]=="test")
		{
			$condition = "b.acc_obj_type = '".$_POST["test"]."'";
		}
		else
		{
				$result_id = $usertracking->getSubTest($_SESSION["AccountId"]);
				$condition = "(b.acc_obj_id =". $result_id[0][0];
				for($i=1;$i < count($result_id);$i++)
				{
					$condition = $condition." or b.acc_obj_id=".$result_id[$i][0];
				}
				$condition = $condition.")";
		}
		
		$condition = $condition." and b.acc_time>='".$from."' and b.acc_time<'".$to."'";
		$this->data["data"] = $usertracking->searchTestResults($condition);
		$this->maxcount = count($this->data["data"]);
		if(count($this->data["data"])<1)
		{
			sendInfo($lng->txt("msg_no_search_result"),true);
			header("location:tracking.php");
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
			$tpl->show();
		}
		
	}
	else //user not selected
	{
		$title_new = array("count","test module","time");
		if($_POST["search_for"]["learn"]=="test")//lm selected
		{
			$condition = "acc_obj_type = '".$_POST["test"]."'";
			if($_POST["time"]=='ht')//hour selected
			{
				$num = 24;
			}
			else //day selected
			{
				$num = $usertracking->numDay($from,$to);
				$from1 = $usertracking->addDay($from);
			}
			include_once "./classes/class.ilTableGUI.php";
			$tbl = new ilTableGUI();	
			$tbl->setTitle($lng->txt("search_result"),0,0);
			foreach ($title_new as $val)
			{
				$header_names[] = $lng->txt($val);
			}
			$tbl->setHeaderNames($header_names);
			$tbl->setMaxCount($num);
			$tbl->render();
			if($_POST["time"]=='ht')//hour selected 
			{
				$time = $usertracking->selectTime($from,$to,$condition);
				for($i=0;$i<24;$i++)
				{
					$k = $i+1;
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
					$data[1] = $_POST["test"];
					$data[2] = $i.":00:00  ~  ".$k.":00:00";
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
					$data[1] = $_POST["test"];
					$data[2] = $from."  ~  ".$from1;
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
			$tpl->show();
		}
		else //test not selected
		{
			if($_POST["time"]=='ht')
			{
				$num = 24;
			}
			else
			{
				$num = $usertracking->numDay($from,$to);
				$from1 = $usertracking->addDay($from);
				$num1 = $num * count($usertracking->getTest($_SESSION["AccountId"]));
			}
			$result = $usertracking->getTest($_SESSION["AccountId"]);
			if(count($result)<1)
			{
				sendInfo($lng->txt("msg_no_search_result"),true);
				header("location:tracking.php");
			}
			$sm = $result[0][0];
			for($t=1;$t < count($result);$t++)
			{
				$sm = $sm.",".$result[$t][0];
			}
			include_once "./classes/class.ilTableGUI.php";
			$tbl = new ilTableGUI();
			$tbl->setTitle($lng->txt("search_result"),0,0);
			foreach ($title_new as $val)
			{
				$header_names[] = $lng->txt($val);
			}
			$tbl->setHeaderNames($header_names);
			if($_POST["time"]=='ht')
			{
				$tbl->setMaxCount($num);
				$tbl->render();
				$result_id = $usertracking->getSubTest($_SESSION["AccountId"]);
				$condition = "(acc_obj_id =". $result_id[0][0];
				for($i=1;$i < count($result_id);$i++)
				{
					$condition = $condition." or acc_obj_id=".$result_id[$i][0];
				}
				$condition = $condition.")'";
				$time = $usertracking->selectTime($from,$to,$condition);
				
				for($i=0;$i<24;$i++)
				{	
					$k = $i+1;
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
					//$data[1] = $sm;
					$data[1] = "all of your subjects!";
					$data[2] = $i.":00:00  ~  ".$k.":00:00";
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
			else
			{
				$tbl->setMaxCount($num1);
				$tbl->render();
				$from1 = $usertracking->addDay($from);
				/*
				$lm = $usertracking->getTest($_SESSION["AccountId"]);
				$condition2 = $condition." and (acc_obj_type = '".$lm[0][0]."'";
				for($j=1;$j<count($usertracking->getTest($_SESSION["AccountId"]));$j++)
				{
					$condition2 = $condition2." or acc_obj_type = '".$lm[$j][0]."'";
				}
				$condition2 = $condition2.")";
				*/
				for($i=0;$i<$num;$i++)
				{	
					$data[$i][0] = $usertracking->countNum($from,$from1,$condition);
					$data[$i][1] = "all of your tests!";
					$data[$i][2] = $from."  ~  ".$from1;
					$css_row = $i%2==0?"tblrow1":"tblrow2";
					foreach ($data[$i] as $key => $val)
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
				}//for		
			} 
			$tpl->show();
		}
	}//else	
}
function searchForm ()
{
	global $tpl,$lng,$ilias;
	
	$yearf = $_POST["Fobject"]["yearf"];
	$monthf = $_POST["Fobject"]["monthf"];
	$dayf = $_POST["Fobject"]["dayf"];
	$yeart = $_POST["Fobject"]["yeart"];
	$montht= $_POST["Fobject"]["montht"];
	$dayt = $_POST["Fobject"]["dayt"];
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
		sendInfo($lng->txt("msg_err_search_time"),true);
		header("location:tracking.php");
		exit();
	}
	if($_POST["time"]!='h' and $_POST["time"]!='d')
	{
		sendInfo($lng->txt("msg_no_search_time"),true);
		header("location:tracking.php");
		exit();
	}
	$usertracking = new ilUserTracking();
	$result_id = $usertracking->getSubId($_SESSION["AccountId"]);
	$condition = "(acc_obj_id =". $result_id[0][0];
	for($i=1;$i < count($result_id);$i++)
	{
	 $condition = $condition." or acc_obj_id=".$result_id[$i][0];
	}
	$condition = $condition. " ) and acc_time >='".$from."' and acc_time< '".$to."'";

	if(count($usertracking->countResults($condition))== 0)
	{
		sendInfo($lng->txt("msg_no_search_result").true);
		header("location:tracking.php");
		exit();
	}
	include_once "./classes/class.ilTableGUI.php";
	$tbl = new ilTableGUI();

	$tpl->addBlockfile("CONTENT", "content", "tpl.table_ut.html");
	$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	$usertracking->_locator();
	$usertracking->_tables();
	$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");
	if($_POST["search_for"]["usr"]=="usr")//user selected
	{
		$title_new = array("login", "learning module", "language","client ip","time");
		$lan = $_POST["Fobject"]["language"];
		if($_POST["search_for"]["learn"]=="lm")
		{
			$condition = "b.acc_obj_type = '".$_POST["lm"]."'";
			$condition = $condition." and b.language ='".$lan."'";
		}
		else
		{
				$result_id = $usertracking->getSubId($_SESSION["AccountId"]);
				$lan = $_POST["Fobject"]["language"];
				$condition = "(b.acc_obj_id =". $result_id[0][0];
				for($i=1;$i < count($result_id);$i++)
				{
					$condition = $condition." or b.acc_obj_id=".$result_id[$i][0];
				}
				$condition = $condition.") and b.language = '".$lan."'";
		}
		
		$condition = $condition." and b.acc_time>='".$from."' and b.acc_time<'".$to."'";
		$this->data["data"] = $usertracking->searchResults($condition);
		$this->maxcount = count($this->data["data"]);
		if(count($this->data["data"])<1)
		{
			sendInfo($lng->txt("msg_no_search_result"),true);
			header("location:tracking.php");
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
			$tpl->show();
		}
		
	}
	else //user not selected
	{
		$title_new = array("count","learning module","language","time");
		if($_POST["search_for"]["learn"]=="lm")//lm selected
		{
			$condition = "acc_obj_type = '".$_POST["lm"]."'";
			$lan = $_POST["Fobject"]["language"];
			$condition = $condition." and language = '".$lan."'";
			if($_POST["time"]=='h')//hour selected
			{
				$num = 24;
			}
			else //day selected
			{
				$num = $usertracking->numDay($from,$to);
				$from1 = $usertracking->addDay($from);
			}
			include_once "./classes/class.ilTableGUI.php";
			$tbl = new ilTableGUI();	
			$tbl->setTitle($lng->txt("search_result"),0,0);
			foreach ($title_new as $val)
			{
				$header_names[] = $lng->txt($val);
			}
			$tbl->setHeaderNames($header_names);
			$tbl->setMaxCount($num);
			$tbl->render();
			if($_POST["time"]=='h')//hour selected 
			{
				$time = $usertracking->selectTime($from,$to,$condition);
				for($i=0;$i<24;$i++)
				{
					$k = $i+1;
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
					$data[1] = $_POST["lm"];
					$data[2] = $_POST["Fobject"]["language"];
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
					$data[1] = $_POST["lm"];
					$data[2] = $_POST["Fobject"]["language"];
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
			$tpl->show();
		}
		else //lm not selected
		{
			if($_POST["time"]=='h')
			{
				$num = 24;
			}
			else
			{
				$num = $usertracking->numDay($from,$to);
				$from1 = $usertracking->addDay($from);
				$num1 = $num * count($usertracking->getlm($_SESSION["AccountId"]));
			}
			$result = $usertracking->getlm($_SESSION["AccountId"]);
			if(count($result)<1)
			{
				sendInfo($lng->txt("msg_no_search_result"),true);
				header("location:tracking.php");
			}
			$sm = $result[0][0];
			for($t=1;$t < count($result);$t++)
			{
				$sm = $sm.",".$result[$t][0];
			}
			$lan = $_POST["Fobject"]["language"];
			$condition ="language = '".$lan."'";
			include_once "./classes/class.ilTableGUI.php";
			$tbl = new ilTableGUI();
			$tbl->setTitle($lng->txt("search_result"),0,0);
			foreach ($title_new as $val)
			{
				$header_names[] = $lng->txt($val);
			}
			$tbl->setHeaderNames($header_names);
			if($_POST["time"]=='h')
			{
				$tbl->setMaxCount($num);
				$tbl->render();
				$result_id = $usertracking->getSubId($_SESSION["AccountId"]);
				$lan = $_POST["Fobject"]["language"];
				$condition = "(acc_obj_id =". $result_id[0][0];
				for($i=1;$i < count($result_id);$i++)
				{
					$condition = $condition." or acc_obj_id=".$result_id[$i][0];
				}
				$condition = $condition.") and language = '".$lan."'";
				$time = $usertracking->selectTime($from,$to,$condition);
				
				for($i=0;$i<24;$i++)
				{	
					$k = $i+1;
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
					//$data[1] = $sm;
					$data[1] = "all of your subjects!";
					$data[2] = $_POST["Fobject"]["language"];
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
			else
			{
				$tbl->setMaxCount($num1);
				$tbl->render();
				$lan = $_POST["Fobject"]["language"];
				$condition ="language = '".$lan."'";
				$from1 = $usertracking->addDay($from);
				$lm = $usertracking->getlm($_SESSION["AccountId"]);
				$condition2 = $condition." and (acc_obj_type = '".$lm[0][0]."'";
				for($j=1;$j<count($usertracking->getlm($_SESSION["AccountId"]));$j++)
				{
					$condition2 = $condition2." or acc_obj_type = '".$lm[$j][0]."'";
				}
				$condition2 = $condition2.")";
				for($i=0;$i<$num;$i++)
				{	
					$data[$i][0] = $usertracking->countNum($from,$from1,$condition2);
					$data[$i][1] = "all of your subjects!";
					$data[$i][2] = $_POST["Fobject"]["language"];
					$data[$i][3] = $from."  ~  ".$from1;
					$css_row = $i%2==0?"tblrow1":"tblrow2";
					foreach ($data[$i] as $key => $val)
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
				}//for		
			} 
			$tpl->show();
		}
	}//else	
}
?>
