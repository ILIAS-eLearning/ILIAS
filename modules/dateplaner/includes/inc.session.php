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
	$modul_name = "CSCW_ILIAS" ;
	session_register("modul_name");
}


session_register("CSCW_UId");
session_register("CSCW_Lang");
session_register("CSCW_Skin");
session_register("CSCW_Style");
session_register("CSCW_StyleFname");
session_register("CSCW_GroupIds");
getStartEndTime();
session_register("CSCW_Starttime");
session_register("CSCW_Endtime");
if(!session_is_registered ("CSCW_Keywords")) {
	$CSCW_Keywords = "*";
	session_register("CSCW_Keywords");
}
/* Bug in Php 4.3.3
if(!session_register("CSCW_ScreenWith")) {
	session_register("CSCW_ScreenWith");
}
if(!session_register("CSCW_ScreenHeight")) {
	session_register("CSCW_ScreenHeight");
}
*/

/**
*	void function getStartEndTime()
*	@description : get the start an end time for Week view and Day view
* 	@global string CSCW_UId     ( actual User ID )
* 	@global string CSCW_Starttime( include Start Time of during on day in week view )
* 	@global string CSCW_Endtime  ( include End Time of during on day in week view )
*/
function getStartEndTime()
{
	global 	$CSCW_UId,$CSCW_Starttime,$CSCW_Endtime;
	$DB		= new Database();
	$Result = $DB->getStartEnd( $CSCW_UId );
	if ($Result != False) 
	{
		$CSCW_Starttime		= $Result[1];
		$CSCW_Endtime		= $Result[2];
	}
	else 
	{
		$CSCW_Starttime		= "08:00:00";
		$CSCW_Endtime		= "18:00:00";
	}
} // end func
?> 
