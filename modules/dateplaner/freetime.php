<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul - inbox												  |													
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2004 ILIAS open source & University of Applied Sciences Bremen|
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
* @author		Matthias Pohl <m.pohl@gmx.net> 
* @author		Frank Gruemmert <gruemmert@feuerwelt.de>
* @version		$Id$
* @module       freetime.php                            
* @modulegroup  dateplaner                   
* @package		dateplaner-frontend
*/ 

// include DP freetime functions
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.freetime.php');
		

/* ------------------------------------  generate frames --------------------------- */
// -----------------------------------------  fixed ---------------------------------//
// the left frame is not used 
$left	= '';

// right frame is curently not used
$right	= '';

// the up frame is detect by the interface 

// down frame is curently not used
$downtext = '';
// --------------------------------------  end fixed  -------------------------------//

//get start- and end-time for the view-area
$viewTsBegin = strtotime($_SESSION[DP_Starttime]);
if ($DP_Endtime=="24:00:00"){
$viewTsEnd = strtotime("23:59:59");
}else{
$viewTsEnd = strtotime($_SESSION[DP_Endtime]);
}

//convert the strings into timestamps 
$beginsek=((intval(date(H,$viewTsBegin))*3600)+intval((date(i,$viewTsBegin))*60)+intval(date(s,$viewTsBegin)));
$endsek=((intval(date(H,$viewTsEnd))*3600)+intval((date(i,$viewTsEnd))*60)+intval(date(s,$viewTsEnd)));

//round the timestamps to 15min-chunks
$startDisplayQuarters=floor($beginsek/900);
$endDisplayQuarters=ceil($endsek/900);

//tables for the results of database-query
$dStartTime = array(array());
$dEndTime = array(array());

//table for view
$dTargetTable = array(array());

//boolean variable   
$pError="";

//detect a wrong entered date
if (str2date($_POST[date2])<0){
echo "Wrong date-format error<br>";
$pError=true;
}
else{
$pError=false;
}

//detect if a groups is selected
if ($_POST[DateValuesGroup_id]==0){
echo "Please select a group first<br>";
$pError=true;
}
else{
$pError=false;
}

//if an error occured, echo a 'back'-button otherwise continue
if ($pError==true){
echo '[<a href="javascript:history.back()"><b>back</b></a>]';
}
else{
//every week begins @ monday (monday==0;... sunday==6);
$weekTs= str2date($_POST[date2]);
$weekTs= mktime(0,0,0,date("m",$weekTs),(date("d",$weekTs)-((date("w",$weekTs)+6))%7),date("Y",$weekTs)); 

// set variables 4 table-header
$S_Datum = setDateInTblHead($weekTs) ;

//get DB-result 4 current week
for ($j=0;$j<7;$j++){
	$begin= $weekTs+((24*60*60)*$j); 	//start of day 
	$end= $begin+((24*60*60)-1); //end of day
	$groupIDs=$_POST[DateValuesGroup_id];		//groupIds
	$groupDates[$j]= getGroupDatesForDisplay($groupIDs, $begin, $end, $DB); //get dates
}
//clear display-data
for ($j=0;$j<7;$j++){
	$dTargetTable[$j]=array_fill(0, 96, 0);
}


//get groupDates out of DB-result and round them to 15min chunks 
for($j=0;$j<7;$j++)
{
	for($i=0;$i<sizeof($groupDates[$j]);$i++){
		if ($groupDates[$j]!=""){		
			$tsBegin=$groupDates[$j][$i][1];
			$tsEnd=$groupDates[$j][$i][2];

			$beginsek=((intval(date(H,$tsBegin))*3600)+intval((date(i,$tsBegin))*60)+intval(date(s,$tsBegin)));
			$endsek=((intval(date(H,$tsEnd))*3600)+intval((date(i,$tsEnd))*60)+intval(date(s,$tsEnd)));

			$beginQuarter=floor($beginsek/900);
			$endQuarter=ceil($endsek/900);

			$dStartTime[$j][$i]=$beginQuarter;
			$dEndTime[$j][$i]=$endQuarter;
		}
	}
}

//prepare display-data 
for($j=0;$j<7;$j++){
	for($i=0;$i<sizeof($dStartTime[$j]);++$i){
		$start=$dStartTime[$j][$i];
		$end=$dEndTime[$j][$i];

		for($start;$start<=$end-1;++$start){
			$dTargetTable[$j][$start]+=1;
		}
	}
}
		
//display calculated data
for($i=$startDisplayQuarters; $i <=$endDisplayQuarters-1; $i++){
	$htmlBuffer=$htmlBuffer. "<TR class=\"small\">";
	// show time
	$min=$i*15;
    if($min%30==0) {$htmlBuffer=$htmlBuffer. "\n<TH class='tblheader' rowspan='2'>";
		$hour = ($min-$min%60)/60 ;
		$quarter= $min%60;
		if ($quarter==0){$quarter="00";}
		$htmlBuffer=$htmlBuffer. $hour.":".$quarter;
		$htmlBuffer=$htmlBuffer. "</TH>\n";
	}
	// show occupied and free quarters 	
	for($j=0;$j<7;$j++){
		$actTS=$weekTs+($min*60)+($j*24*60*60);	
		if ($dTargetTable[$j][$i]==0){
			$htmlBuffer=$htmlBuffer. "<TD ".$DP_CSS[tblrow02]." align=\"top\"".'>';
			$htmlBuffer=$htmlBuffer. '<a TITLE='.$DP_language[free]."&nbsp;".$hour.":".$quarter.' href="javascript:document.feedback.timestamp.value='.$actTS.';document.getElementsByName('."'feedback'".')[0].submit()'.'">';
			$htmlBuffer=$htmlBuffer. '<img src=".'.DATEPLANER_ROOT_DIR.$templatefolder."/".$actualtemplate.'/images/filler.gif" width="60" height="10" border="0"></a>'."</TD>\n"; 
		}
		else{

	  	  $htmlBuffer=$htmlBuffer. "<TD ".$DP_CSS[tblrow03]." align=\"top\"".'>';
  		  $htmlBuffer=$htmlBuffer. '<img src=".'.DATEPLANER_ROOT_DIR.$templatefolder."/".$actualtemplate.'/images/filler.gif" width="60" height="10" border="0" >'."</TD>\n";
  		  
		}
	}
	$htmlBuffer=$htmlBuffer."</TR>\n";
}
}
eval ("\$main = \"".$Gui->getTemplate("freetime_main")."\";");
// -----------------------------------------  fixed ---------------------------------//
// frameset template not used
// main template
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  end fixed --------------------------------//
exit;
?>