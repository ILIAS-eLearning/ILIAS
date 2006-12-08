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
* Include file dateplaner header
*
* this file should manage the include and require sequence
*
* @author		Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$                                    
*/

/* ----------------------------------   include files ----------------------------------------*/
/* include classes and functions */

//classes
	require_once	('.'.DATEPLANER_ROOT_DIR.'/classes/class.Database.php');
		// Objects
		$DB			= new database($DP_dlI);
	require_once	('.'.DATEPLANER_ROOT_DIR.'/classes/class.Gui.php');
		// Objects
		$Gui		= new Gui();
	require_once	('.'.DATEPLANER_ROOT_DIR.'/classes/class.TimestampToDate.php');

//language
	//$DP_language = ($Gui->getLangArray($DP_Lang));

//dateplaner functions
	//include keyword functions
	require_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.keywords.php');
	//include keyword functions
	require_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.sortdates.php');
	//include minicalendar
	require_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.minicalendar.php');

/* --------------------------------- end include files --------------------------------------*/
/* include classes and functions */



/** 
* function to detect the resolution of users desktop to calculate the views
*/
if (!session_is_registered("DP_ScreenHeight") or !session_is_registered("DP_ScreenWith")) 
{
    session_start ();
	$SidName	= session_name();
    $sid		= session_id();
	echo '
	<script language="JavaScript" >
		var width = screen.width;
		var height = screen.height;
		var SidName		= \''.$SidName.'\';
		var sid			= \''.$sid.'\';
		var JSscript	= \'1\';
		if(location.search.indexOf("ScreenHeight") == -1) {
			location = "." + "/dateplaner.php?app=inbox&ScreenHeight=" + height + "&ScreenWith=" + width + "&" + SidName + "=" + sid + "&JSscript=" + JSscript;   
		}
	</script>
		 ';
	
	session_register("DP_JSscript");

	$DP_JSscript				= $_GET[JSscript] ;
	if($_GET[ScreenWith]) {
		session_register("DP_ScreenWith");
		$_SESSION["DP_ScreenWith"]	= $_GET[ScreenWith] ;
		$_SESSION["DP_ScreenHeight"]= $_GET[ScreenHeight] ;
		$_SESSION["DP_JSscript"]	= $_GET[JSscript] ;
	}else {
		$_SESSION["DP_JSscript"]	= 0 ;
	}
}
?>