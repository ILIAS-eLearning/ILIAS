<?php
/**
* Include file Session
*
* this file shoud manage all session intitialisation 
* 
* @author Frank Grümmert 
* 
* @version $Id: inc.session.php,v 0.9 2003/06/11 
* @package application
* @access public
*
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

/*for som views required keywords settings (e.g. list-view)*/
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