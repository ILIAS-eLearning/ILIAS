<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul														  |													
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
* Functions for date.php
*
* this file should manages date actions
*
* @author		Bartosz Tyrakowski <tyra@freenet.de> 
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$ 
* @module       inc.date.php                            
* @modulegroup  dateplaner                    
* @package		dateplaner-functions
*/

/**
* 	void function setDateView ($DateValues, $flag )
* 	
* 	This function is generating the main-view of a date in three diferent views, for new, update and update with read only.
*
* 	@param		array $DateValues , $flag
* 	@global		array $DP_language , $templatefolder, $actualtemplate,  $DP_CSS 
* 	@global		array $DP_CSS			( contains CSS Strings from the conf.gui file )
*	@global		sting $actualtemplate		( current template )
*	@global		string $templatefolder		( current used template folder )
*	@return		string $dateContent
*/
function setDateView ($DateValues, $flag, $DB)
{
	global $DP_language, $templatefolder, $actualtemplate,  $DP_CSS;

	$Gui		= new Gui();
	$ttd		= new TimestampToDate;
	
	$Array		= setJs();
	$DateValues[popupcall_1] = $Array[1] ;
	$DateValues[popupcall_2] = $Array[2] ;
	$DateValues[popupcall_3] = $Array[3] ;
	if(!$DateValues[text])		$DateValues[text]		 = $DateValues[DateArray][text];
	if(!$DateValues[shorttext]) $DateValues[shorttext]	 = $DateValues[DateArray][shorttext];
	if($DateValues[whole_day] == "1") $DateValues[checked] = "checked";

	$date2 = $DateValues[date2];
	$date4 = $DateValues[date4];
	$date6 = $DateValues[date6];


	switch($flag) {
		case 'i': // insert date
			eval ("\$tab_start_date = \"".$Gui->getTemplate("date_startdate")."\";");
			eval ("\$tab_end_date = \"".$Gui->getTemplate("date_enddate")."\";");
			if($DateValues[rotation]) $DateValues[$DateValues[rotation]] = "selected";
			eval ("\$tab_rotation = \"".$Gui->getTemplate("date_rotation")."\";");
			$DateValues[Groupform] = setGroups($DateValues[group_id], $DB);
			eval ("\$tab_group = \"".$Gui->getTemplate("date_group")."\";");
			$DateValues[Keywordform] = setKeywords($DateValues[keyword_id], $DB);
			eval ("\$tab_keyword = \"".$Gui->getTemplate("date_keyword")."\";");
			eval ("\$tab_text = \"".$Gui->getTemplate("date_text")."\";");
			eval ("\$btn_in = \"".$Gui->getTemplate("date_btnin")."\";");
			eval ("\$btn_exit = \"".$Gui->getTemplate("date_btnexit")."\";");
			break; 
		case 'ud_w': // update/delete a wirtable date

			$DateValues[old_keyword_id]		= $DateValues[DateArray][old_keyword_id];
			eval ("\$tab_start_date = \"".$Gui->getTemplate("date_startdate")."\";");
			eval ("\$tab_end_date = \"".$Gui->getTemplate("date_enddate")."\";");
			if($DateValues[rotation]) {
				$DateValues[$DateValues[rotation]] = "selected";
			}else {
				$date6 = $DateValues[date6];
				$DateValues[$DateValues[DateArray][rotation]] = "selected";
			}
			eval ("\$tab_rotation = \"".$Gui->getTemplate("date_rotation")."\";");
			eval ("\$tab_group = \"".$Gui->getTemplate("date_group_ro")."\";");
			if($DateValues[keyword_id]) {
				$DateValues[Keywordform] = setKeywords($DateValues[keyword_id],$DB);
			}else {
				$DateValues[Keywordform] = setKeywords($DateValues[DateArray][keyword_id],$DB);
			}
			eval ("\$tab_keyword = \"".$Gui->getTemplate("date_keyword")."\";");
			eval ("\$tab_text = \"".$Gui->getTemplate("date_text")."\";");
			//Format Date Text for Priview Output
			$DateValues = parseDataForOutput ($DateValues);
			$DP_language[dv_message] = $DP_language[dv_message].$DP_language[dv_preview];
			eval ("\$tab_pretext = \"".$Gui->getTemplate("date_text_ro")."\";");
			eval ("\$btn_del = \"".$Gui->getTemplate("date_btndel")."\";");
			eval ("\$btn_upd = \"".$Gui->getTemplate("date_btnupd")."\";");
			eval ("\$btn_exit = \"".$Gui->getTemplate("date_btnexit")."\";");
			break;
		case 'ud_ro': // update/delete a read only date
			
			//Format Date Text for Output
			$DateValues = parseDataForOutput ($DateValues);
			
			$DateValues[start_ts]			= $DateValues[DateArray][begin]; 
			$DateValues[end_ts]				= $DateValues[DateArray][end];
			$DateValues[end_rotation_ts]	= $DateValues[DateArray][end_rotation];
			$DateValues[keyword_id]			= $DateValues[DateArray][keyword_id];
			$DateValues[old_keyword_id]		= $DateValues[DateArray][old_keyword_id];

			
			if($DateValues[whole_day] != "1") {
				$ttd->ttd($DateValues[DateArray][begin]);
				$DateValues[starttime]			= $ttd->middletime ;
				eval ("\$tab_start_date = \"".$Gui->getTemplate("date_startdate_ro")."\";");
				$ttd->ttd($DateValues[DateArray][end]);
				$DateValues[endtime] = $ttd->middletime ;
				$DateValues[whole_day_name]		= "no";
				eval ("\$tab_end_date = \"".$Gui->getTemplate("date_enddate_ro")."\";");
			}else {
				$ttd->ttd($DateValues[DateArray][begin]);
				$DateValues[starttime]			= $ttd->shorttime ;
				$DateValues[whole_day_name]		= "yes";
				eval ("\$tab_start_date = \"".$Gui->getTemplate("date_startdate_ro")."\";");
			}

			if($DateValues[DateArray][rotation] != 0 ) {
				$ttd->ttd($DateValues[DateArray][end_rotation]);
				$DateValues[end_rotationtime] = $ttd->shorttime ;
				eval ("\$tab_rotation = \"".$Gui->getTemplate("date_rotation_ro")."\";");
			}else {
				eval ("\$tab_rotation = \"".$Gui->getTemplate("date_rotation_ro")."\";");
			}
			eval ("\$tab_group = \"".$Gui->getTemplate("date_group_ro")."\";");

			$DateValues[Keywordform] = setKeywords($DateValues[DateArray][keyword_id],$DB);
			eval ("\$tab_keyword = \"".$Gui->getTemplate("date_keyword")."\";");
			eval ("\$tab_text = \"".$Gui->getTemplate("date_text_ro")."\";");
			eval ("\$btn_del = \"".$Gui->getTemplate("date_btndel")."\";");
			eval ("\$btn_upd = \"".$Gui->getTemplate("date_btnupd")."\";");
			eval ("\$btn_exit = \"".$Gui->getTemplate("date_btnexit")."\";");
			break;
		default :
	
	}

	eval ("\$dateContent = \"".$Gui->getTemplate("date_content")."\";");

	Return $dateContent;	
	
}// end func

/**
* 	void function parseDataForOutput (Array DateValues)
* 	
* 	Formate the user Date Date for the html Output.
*
* 	@param		array $DateValues
*	@return		array $DateValues
*/
function  parseDataForOutput ($DateValues) {
	
	// parse text for html links and e-mail adresses
	$text = $DateValues[text];
	$urlsearch[]="/([^]_a-z0-9-=\"'\/])((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
	$urlsearch[]="/^((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
	$urlreplace[]="\\1<A HREF='\\2\\4' target='_blank'>\\2\\4</A>";
	$urlreplace[]="<A HREF='\\1\\3' target='_blank'>\\1\\3</A>";
	$emailsearch[]="/([\s])([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$emailsearch[]="/^([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$emailreplace[]="\\1<a href='mailto:\\2'>\\2</a>";
	$emailreplace[]="<a href='mailto:\\0'>\\0</a>";
	$text = preg_replace($urlsearch, $urlreplace, $text);
	if (strpos($text, "@")) $text = preg_replace($emailsearch, $emailreplace, $text);
	// parse text for line breaks
	$text = str_replace("\r\n","<br>" , $text);
	// parse text for images links
	$text = preg_replace("!\[img\](.*)\[/img\]!U","<img alt='\\1' src='\\1'>",$text);
	$DateValues[text] = $text;
	// parse text for html links and e-mail adresses

	$text = $DateValues[shorttext];
	// parse shorttext for html links and e-mail adresses
	$urlsearch[]="/([^]_a-z0-9-=\"'\/])((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
	$urlsearch[]="/^((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
	$urlreplace[]="\\1<A HREF='\\2\\4' target='_blank'>\\2\\4</A>";
	$urlreplace[]="<A HREF='\\1\\3' target='_blank'>\\1\\3</A>";
	$emailsearch[]="/([\s])([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$emailsearch[]="/^([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$emailreplace[]="\\1<a href='mailto:\\2'>\\2</a>";
	$emailreplace[]="<a href='mailto:\\0'>\\0</a>";
	$text = preg_replace($urlsearch, $urlreplace, $text);
	if (strpos($text, "@")) $text = preg_replace($emailsearch, $emailreplace, $text);
	$text = str_replace("\r\n","<br>" , $text);
	$DateValues[shorttext] = $text;

	Return $DateValues;

}// end func

/**
* 	void function setJs ()
* 	
* 	This function generates the small pop-up calendars in the dateview or newdateview for date selection.
*
* 	@global		array $DP_language
*	@return		array $Array
*/
function setJs() {
	global $DP_language;

	$Array[1] = '
<script language=JavaScript>
			var cal2 = new CalendarPopup();  
			cal2.showYearNavigation(); 			cal2.setMonthNames(\''.$DP_language[long_01].'\',\''.$DP_language[long_02].'\',\''.$DP_language[long_03].'\',\''.$DP_language[long_04].'\',\''.$DP_language[long_05].'\',\''.$DP_language[long_06].'\',\''.$DP_language[long_07].'\',\''.$DP_language[long_08].'\',\''.$DP_language[long_09].'\',\''.$DP_language[long_10].'\',\''.$DP_language[long_11].'\',\''.$DP_language[long_12].'\'); 
			cal2.setDayHeaders(\''.$DP_language[Su_short].'\',\''.$DP_language[Mo_short].'\',\''.$DP_language[Tu_short].'\',\''.$DP_language[We_short].'\',\''.$DP_language[Th_short].'\',\''.$DP_language[Fr_short].'\',\''.$DP_language[Sa_short].'\');
			cal2.setWeekStartDay(1);
			cal2.setTodayText("'.$DP_language[today].'");
</script>
';

	$Array[2] = '
<script language=JavaScript >
			var cal4 = new CalendarPopup();	cal4.setMonthNames(\''.$DP_language[long_01].'\',\''.$DP_language[long_02].'\',\''.$DP_language[long_03].'\',\''.$DP_language[long_04].'\',\''.$DP_language[long_05].'\',\''.$DP_language[long_06].'\',\''.$DP_language[long_07].'\',\''.$DP_language[long_08].'\',\''.$DP_language[long_09].'\',\''.$DP_language[long_10].'\',\''.$DP_language[long_11].'\',\''.$DP_language[long_12].'\'); 
			cal4.setDayHeaders(\''.$DP_language[Su_short].'\',\''.$DP_language[Mo_short].'\',\''.$DP_language[Tu_short].'\',\''.$DP_language[We_short].'\',\''.$DP_language[Th_short].'\',\''.$DP_language[Fr_short].'\',\''.$DP_language[Sa_short].'\');
			cal4.setWeekStartDay(1);
			cal4.setTodayText("'.$DP_language[today].'");			
			cal4.showYearNavigation(); 
</script>
';

	$Array[3] = '
<script language=JavaScript >
			var cal6 = new CalendarPopup();	cal6.setMonthNames(\''.$DP_language[long_01].'\',\''.$DP_language[long_02].'\',\''.$DP_language[long_03].'\',\''.$DP_language[long_04].'\',\''.$DP_language[long_05].'\',\''.$DP_language[long_06].'\',\''.$DP_language[long_07].'\',\''.$DP_language[long_08].'\',\''.$DP_language[long_09].'\',\''.$DP_language[long_10].'\',\''.$DP_language[long_11].'\',\''.$DP_language[long_12].'\'); 
			cal6.setDayHeaders(\''.$DP_language[Su_short].'\',\''.$DP_language[Mo_short].'\',\''.$DP_language[Tu_short].'\',\''.$DP_language[We_short].'\',\''.$DP_language[Th_short].'\',\''.$DP_language[Fr_short].'\',\''.$DP_language[Sa_short].'\');
			cal6.setWeekStartDay(1);
			cal6.setTodayText("'.$DP_language[today].'");			
			cal6.showYearNavigation(); 
</script>
';
	Return $Array ;
}// end func

/**
* 	void function setKeywords ()
* 	
* 	Generates the selection of the keyword. 
*
* 	@param		array $keyword_id
* 	@param		$DB (object of th db class ) 
*	@return		string $Keywordform
*/
function setKeywords($keyword_id,$DB) {
	$Keywords = getKeywordContent($DB);
	if($Keywords) {
		for ($j = 0 ; $j < count($Keywords) ; $j++) {
			if($keyword_id == $Keywords[$j][0]) {
				$Keywordform = $Keywordform.'<option selected value="'.$Keywords[$j][0].'">'.$Keywords[$j][1].'</option>';
			}else {
				$Keywordform = $Keywordform.'<option value="'.$Keywords[$j][0].'">'.$Keywords[$j][1].'</option>';
			}
		}
	}
	Return $Keywordform;
	
}// end func

/**
* 	void function setGroups ()
* 	
* 	Generates the selection of a group.
*
* 	@param		string $groupID
* 	@param		$DB (object of th db class ) 
*	@return		string $Groupform
*/
function setGroups($groupID,$DB) {
	$Groups = getGroupContent($DB);
	if($Groups) {
		for ($i = 0 ; $i < count($Groups) ; $i++) {
			if($groupID and $groupID == $Groups[$i][0] ) {
				$Groupform = $Groupform.'<option selected value="'.$Groups[$i][0].'">'.$Groups[$i][1].'</option>';
			}else {
				$Groupform = $Groupform.'<option value="'.$Groups[$i][0].'">'.$Groups[$i][1].'</option>';
			}
		}
	}
	Return $Groupform;

}// end func

/**
* 	void function getGroupContent ()
* 	
* 	Calls the DB-function getUserGroups, and returns the result (all groups of a user).
*
* 	@param		$DB (object of th db class ) 
* 	@global		string $DP_UId     ( actual User ID )
*	@return		array $Groups
*/
function getGroupContent($DB) {
	global $DP_UId  ;
	$Groups = $DB->getUserGroups ($DP_UId);

	Return $Groups;

}// end func

/**
* 	void function getKeywordContent ()
* 	
* 	Calls the DB-function getKeywords, and returns the result  (all keywords of a user).
*
* 	@param		$DB (object of th db class ) 
* 	@global		string $DP_UId     ( actual User ID )
*	@return		array $Keywords
*/
function getKeywordContent($DB) {
	global $DP_UId ;
	$Keywords	= $DB->getKeywords ($DP_UId);

	Return $Keywords;

}// end func

/**
* 	void function parseData ()
* 	
* 	Validates the user input from the dayview-form.
*
* 	@param		array $DateValues
* 	@param		string $Start_date
* 	@param		string $End_date
* 	@param		string $End_rotation
* 	@global		array $DP_language 
*	@return		array $Valid
*/
function  parseData ($DateValues, $Start_date, $End_date, $End_rotation) {
	global $DP_language;
	if($DateValues[start_ts] == "-1") $Valid[] = $DP_language[ERROR_STARTDATE];
	if($DateValues[whole_day] !=1 and $DateValues[end_ts] == "-1") $Valid[] = $DP_language[ERROR_ENDDATE];
	if($DateValues[end_rotation_ts] == "-1") $Valid[] = $DP_language[ERROR_ROTATIONEND];
	if($DateValues[whole_day] !=1 and $DateValues[start_ts] > $DateValues[end_ts]) $Valid[] = $DP_language[ERROR_END_START]; 
	if($DateValues[end_rotation_ts] and $DateValues[end_rotation_ts] < $DateValues[start_ts]) $Valid[] = $DP_language[ERROR_ROT_END_START]; 

	if(	$Start_date[0] != date ("d", mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2])) or
		$Start_date[1] != date ("m", mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2])) or 
		$Start_date[2] != date ("Y", mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2]))) 
	{
		 $Valid[] = $DP_language[ERROR_STARTDATE];
	}
	if(	$End_date and $DateValues[begin_h] != date ("H", mktime($DateValues[begin_h],0,0,$Start_date[1],$Start_date[0],$Start_date[2])) or
		$DateValues[begin_min] != date ("i", mktime(0,$DateValues[begin_min],0,$Start_date[1],$Start_date[0],$Start_date[2]))) 
	{
		 $Valid[] = $DP_language[ERROR_STARTTIME];
	}
	if(	$End_date and ($End_date[0] != date ("d", mktime(0,0,0,$End_date[1],$End_date[0],$End_date[2])) or
		$End_date[1] != date ("m", mktime(0,0,0,$End_date[1],$End_date[0],$End_date[2])) or 
		$End_date[2] != date ("Y", mktime(0,0,0,$End_date[1],$End_date[0],$End_date[2])))) 
	{
		 $Valid[] = $DP_language[ERROR_ENDDATE];
	}
	if(	$DateValues[end_h] != date ("H", mktime($DateValues[end_h],0,0,$Start_date[1],$Start_date[0],$Start_date[2])) or
		$DateValues[end_min] != date ("i", mktime(0,$DateValues[end_min],0,$Start_date[1],$Start_date[0],$Start_date[2]))) 
	{
		 $Valid[] = $DP_language[ERROR_ENDTIME];
	}
	if(	$End_rotation and ($End_rotation[0] != date ("d", mktime(0,0,0,$End_rotation[1],$End_rotation[0],$End_rotation[2])) or
		$End_rotation[1] != date ("m", mktime(0,0,0,$End_rotation[1],$End_rotation[0],$End_rotation[2])) or 
		$End_rotation[2] != date ("Y", mktime(0,0,0,$End_rotation[1],$End_rotation[0],$End_rotation[2])))) 
	{
		 $Valid[] = $DP_language[ERROR_ROTATIONEND];
	}
	if($DateValues[shorttext] == "") $Valid[] = $DP_language[ERROR_SHORTTEXT]; 

	$Valid[] = "TRUE";
	Return $Valid;

}// end func

/**
* 	void function  setInsertDate()
* 	
* 	The paramerter are adapted to the output-form.
*
* 	@param		string $timestamp
* 	@param		string $DateValues
* 	@param		$DB (object of th db class ) 
*	@return		string $dateContent
*/
function setInsertDate($timestamp, $DateValues, $DB) 
{

	$ttd					= new TimestampToDate;

	if(!$DateValues[referer]) {
		$ttd->ttd($timestamp);
		$DateValues[date2] 		= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
		$DateValues[date4] 		= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
		$DateValues[begin_h]	= $DateValues[end_h]	= $ttd->hour_long ;
		$DateValues[begin_min]	= $DateValues[end_min] 	= $ttd->minutes ;
	}
	
	$dateContent = setDateView ($DateValues, "i",  $DB) ;

	Return $dateContent;	

}// end func

/**
* 	void function setInsertAction( $startdate,$enddate, $endroation, $DateValues)
* 	
* 	This function is adding the date information into the DB and adepting them, e.g. date to timestamp.
*
* 	@param		string $startdate
* 	@param		string $enddate
* 	@param		string $endrotation
* 	@param		array $DateValues
* 	@param		$DB (object of th db class ) 
* 	@global		int $DP_UId
* 	@global		array $DP_language 
*	@return		string $msg
*/
function setInsertAction( $startdate,$enddate, $endrotation, $DateValues,$DB ) {
	global $DP_UId, $DP_language;

	$End_rotation		= False ;
	$End_date			= False ;
	$Start_date			= explode ("/",$startdate);
	if($DateValues[whole_day] !=1){
		$DateValues[start_ts]	= mktime($DateValues[begin_h],$DateValues[begin_min],0,(int)$Start_date[1],(int)$Start_date[0],(int)$Start_date[2]);
		$End_date				= explode ("/",$enddate);
		$DateValues[end_ts]		= mktime($DateValues[end_h],$DateValues[end_min],0,(int)$End_date[1],(int)$End_date[0],(int)$End_date[2]);
	} else {
		$DateValues[start_ts]	= mktime(0,0,0,(int)$Start_date[1],(int)$Start_date[0],(int)$Start_date[2]);
		$DateValues[end_ts]		= mktime(23,59,59,(int)$Start_date[1],(int)$Start_date[0],(int)$Start_date[2]);
		$End_date				= False ;
	}
	if ($DateValues[rotation] != 0){
		if($endrotation == "") {
			$DateValues[end_rotation_ts]		= 2147468399;
			$End_rotation						= False ;
		}else {
			$End_rotation	= explode ("/",$endrotation);
			$DateValues[end_rotation_ts]		= mktime(23,59,59,$End_rotation[1],$End_rotation[0],$End_rotation[2]);
		}
	} else {
		$DateValues[end_rotation_ts]		= 0;
		$End_rotation						= False ;
	}
	
	// error Managment 
	$Valid = parseData ($DateValues, $Start_date, $End_date, $End_rotation);

	if($Valid[0] == "TRUE") {
		$return = $DB->addDate (	$DateValues[start_ts], 
									$DateValues[end_ts], 
									$DateValues[group_id], 
									$DP_UId, 
									mktime(), 
									$DateValues[rotation], 
									$DateValues[end_rotation_ts], 
									htmlspecialchars ($DateValues[shorttext]), 
									htmlspecialchars ($DateValues[text]), 
									$DateValues[keyword_id]);

		switch ($return){
				case "0": $msg = False;
						break;
				case "1": $msg = $DP_language[ERROR_DATE_EXISTS]."<br>";
						break;
				case "2": $msg = $DP_language[ERROR_DB_CONNECT]."<br>";
						break;
		}
	}else 
	{
		for($i=0; $i<(count($Valid)-1); $i++) {
			$msg = $msg.$Valid[$i]."<br>";
		}
		$msg = $msg.'
			<br>
			';
	}

	Return $msg;
}// end func

/**
* 	void function  setUpdateDeleteDate()
* 	
* 	Adaption of the given perameters to the output form. Eg. timestamp to real time.
*
* 	@param		string $timestamp
* 	@param		string $date_id
* 	@param		array $DateArray
* 	@param		array $DateValues
* 	@param		$DB (object of th db class ) 
* 	@global		string $js
* 	@global		int $DP_UId
* 	@global		array $DP_language 
*	@return		string $dateContent
*/
function setUpdateDeleteDate($timestamp, $date_id, $DateArray, $DateValues, $DB) 
{
	global $js ,$DP_UId, $DP_language ;

	if(!$DateValues[referer]) {
	$ttd						= new TimestampToDate;
	$ttd->ttd($DateArray[begin]);
	$DateValues[date2]			= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
	$DateValues[begin_h]		= $ttd->hour_long ;
	$DateValues[begin_min]		= $ttd->minutes ;
	$DateValues[date_timestamp] = $timestamp;
	
	if((int)$DateArray[end]-(int)$DateArray[begin] == 86399 ) $DateValues[whole_day] = "1" ;

	if($DateArray[end]) {
		$ttd->ttd($DateArray[end]);
		$DateValues[date4]		= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
		$DateValues[end_h]		= $ttd->hour_long ;
		$DateValues[end_min] 	= $ttd->minutes ;
	}
	if($DateArray[end_rotation]) {
		$ttd->ttd($DateArray[end_rotation]);
		$DateValues[date6] 		= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
	}
	$DateValues[DateArray]		= $DateArray;
	$DateValues[rotation]		= $DateArray[rotation] ;
	switch($DateArray[rotation]) {
		case '0':
			$DateValues[rotation_name] = $DP_language[r_nonrecurring];
			break; 
		case '1':
			$DateValues[rotation_name] = $DP_language[r_day];
			break; 
		case '2':
			$DateValues[rotation_name] = $DP_language[r_week];
			break; 
		case '3':
			$DateValues[rotation_name] = $DP_language[r_14];
			break; 
		case '4':
			$DateValues[rotation_name] = $DP_language[r_4_weeks];
			break; 
		case '5':
			$DateValues[rotation_name] = $DP_language[r_month];
			break; 
		case '6':
			$DateValues[rotation_name] = $DP_language[r_halfyear];
			break;
		case '7':
			$DateValues[rotation_name] = $DP_language[r_year];
			break; 
	}
	$DateValues[date_id]		= $DateArray[date_id] ;
	$DateValues[Groupform]		= $DB->getGroupName($DateArray[group_id]);
	if(!$DateValues[Groupform]) $DateValues[Groupform] = "none" ;

	}
	// if u wish that more than the owner of a group can chane properties af a date , this row down is the row that has to be changed.
	if($DateArray[user_id] == $DP_UId or $DateArray[group_id] == 0) {
		$dateContent = setDateView ($DateValues, "ud_w", $DB) ;
	}else {
		$dateContent = setDateView ($DateValues, "ud_ro", $DB) ;
		$js = "ro";
	}

	Return $dateContent;	

}// end func

/**
* 	void function setUpdateAction( $startdate,$enddate, $endroation, $DateValues)
* 	
* 	This function is updating the information of a date in the database.
*
* 	@param		string $startdate
* 	@param		string $enddate
* 	@param		string $endrotation
* 	@param		array $DateValues
* 	@param		$DB (object of th db class ) 
* 	@global		int $DP_UId
* 	@global		array $DP_language 
*	@return		string $msg
*/
function setUpdateAction( $startdate,$enddate, $endrotation, $DateValues, $DB) {
	global $DP_UId, $DP_language;
	if($DateValues[writepermission] != "ro") {
		$End_rotation		= False ;
		$End_date			= False ;
		$Start_date			= explode ("/",$startdate);

		if($DateValues[whole_day] !=1){
			$DateValues[start_ts]	= mktime($DateValues[begin_h],$DateValues[begin_min],0,(int)$Start_date[1],(int)$Start_date[0],(int)$Start_date[2]);
			$End_date				= explode ("/",$enddate);
			$DateValues[end_ts]		= mktime($DateValues[end_h],$DateValues[end_min],0,(int)$End_date[1],(int)$End_date[0],(int)$End_date[2]);
		} else {
			$DateValues[start_ts]	= mktime(0,0,0,(int)$Start_date[1],(int)$Start_date[0],(int)$Start_date[2]);
			$DateValues[end_ts]		= mktime(23,59,59,(int)$Start_date[1],(int)$Start_date[0],(int)$Start_date[2]);
			$End_date				= False ;
		}
		if ($DateValues[rotation] != 0){
			if($endrotation == "") {
				$DateValues[end_rotation_ts]		= 2147468399;
				$End_rotation						= False ;
			}else {
				$End_rotation	= explode ("/",$endrotation);

				$DateValues[end_rotation_ts]		= mktime(23,59,59,$End_rotation[1],$End_rotation[0],$End_rotation[2]);
			}
		} else {
			$DateValues[end_rotation_ts]		= 0;
			$End_rotation						= False ;
		}
	
		// error Managment 
		$Valid = parseData ($DateValues, $Start_date, $End_date, $End_rotation);
	}else {
		$Valid[0] = "TRUE";
	}

	if($Valid[0] == "TRUE") {
		if($DateValues[writepermission] != "ro") {
			$return = $DB->updateDate (	$DateValues[date_id],
									$DateValues[start_ts], 
									$DateValues[end_ts], 
									$DP_UId, 
									mktime(), 
									$DateValues[rotation], 
									$DateValues[end_rotation_ts], 
									htmlspecialchars ($DateValues[shorttext]), 
									htmlspecialchars ($DateValues[text]));
		}

		$return = $DB->updateKeyword2Date (
									$DP_UId, 
									$DateValues[date_id],
									$DateValues[keyword_id]);
		if($return) {
			$msg = False;
		}else {
			$msg = $DP_language[ERROR_DB]."<br>";
		}
//			$msg = $msg.' Damit der Timo debuggen kann :
//			<br> hier rotation: '.$DateValues[rotation].'<br>
//			';
	}else {
		for($i=0; $i<(count($Valid)-1); $i++) {
			$msg = $msg.$Valid[$i]."<br>";
		}
		$msg = $msg.' 
			<br>
			';
	}
	Return $msg;
}// end func

/**
* 	void function setDeleteAction($DateValues)
* 	
* 	This function is selecting the way in which a date is going to be deleted. 
*
* 	@param		array $DateValues
* 	@param		$DB (object of th db class ) 
* 	@global		int $DP_UId
* 	@global		array $DP_language 
*	@return		string $msg or $DB->delDate ()
*/
function setDeleteAction($DateValues, $DB) {
	global $DP_UId, $DP_language;
	$Gui				= new Gui();

	if($DateValues[rotation_id] != 0) {
		$DateValues[rotation_id] = 0 ;
		eval ("\$msg = \"".$Gui->getTemplate("date_msgdelrotation")."\";");
		Return $msg;
	}
	switch($DateValues[rotation_ack]) {
		case 'one':
			$return = $DB->delDate (	$DateValues[date_id],
										$DP_UId, 
										$DateValues[date_timestamp]);
			break; 
		case 'all':
			$return = $DB->delDate (	$DateValues[date_id],
										$DP_UId, 
										"0");
			break; 
		default :
			$return = $DB->delDate (	$DateValues[date_id],
										$DP_UId, 
										"0");
	}

	if($return) {
			$msg = false;
	}else {
			$msg = $DP_language[ERROR_DB]."";
	}	
		//$msg = $msg.' Damit der Timo debuggen kann :<br>Rückgabewert : '.$return.' <br> ';

	return $msg;
}// end func
?>