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
* Include file Session
*
* this file shoud manage session intitialisations 
*
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version      $Id$                                    
* @module       inc.session.php                            
* @modulegroup  dateplaner                    
* @package		dateplaner-functions
*/


session_start();	

if(!$HTTP_SESSION_VARS[modul_name]) {
	$modul_name = "DP_ILIAS" ;
	session_register("modul_name");
}

if(!session_is_registered ("DP_UId"))			session_register("DP_UId");
if(!session_is_registered ("DP_Lang"))			session_register("DP_Lang");
if(!session_is_registered ("DP_Skin"))			session_register("DP_Skin");
if(!session_is_registered ("DP_Style"))			session_register("DP_Style");
if(!session_is_registered ("DP_StyleFname"))	session_register("DP_StyleFname");
if(!session_is_registered ("DP_GroupIds"))		session_register("DP_GroupIds");
if(!session_is_registered ("DP_Starttime") or !session_is_registered ("DP_Endtime")) {
	getStartEndTime($DP_UId , $DB);
	session_register("DP_Starttime");
	session_register("DP_Endtime");
}
if(!session_is_registered ("DP_Keywords")) {
	$DP_Keywords = "*";
	session_register("DP_Keywords");
}

/*for some views required keywords settings (e.g. list-view)*/
if ($_REQUEST[S_Keywords]) 
{
	$_SESSION[DP_Keywords] = $_REQUEST[S_Keywords];
}

/**
*	void function getStartEndTime()
*	@description : get the start an end time for Week view and Day view
* 	@param string DP_UId     ( actual User ID )
* 	@param DP_dlI  ( Dateplaner DB handler)
* 	@global string DP_Starttime( include Start Time of during on day in week view )
* 	@global string DP_Endtime  ( include End Time of during on day in week view )
*/
function getStartEndTime($DP_UId, $DB)
{
	global $DP_Starttime,$DP_Endtime;
	$Result		= $DB->getStartEnd( $DP_UId );

	if ($Result != False) 
	{
		$DP_Starttime		= $Result[1];
		$DP_Endtime			= $Result[2];
	}
	else 
	{
		$DP_Starttime		= "08:00:00";
		$DP_Endtime			= "18:00:00";
	}
} // end func
?>