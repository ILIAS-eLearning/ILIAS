<?php 
/**
* Include file cscw.header
*
* this file should manage the include and require sequence
* 
* @author Frank Grümmert 
* 
* @version $Id: inc.cscw.header.php,v 0.9 2003/06/11 
* @package application
* @access public
*
*/

if(!$actualIliasDir) {
	require_once	('./classes/class.interface.php');
	$interface	= new Interface(nb);
	$actualIliasDir = $interface->getactualIliasDir();
}

if(!$uptext) {
	require_once	('./classes/class.interface.php');
	$interface		= new Interface(nb);
	$uptext			= $interface->getFrameDec();
}

/* ----------------------------------   Include Datei ----------------------------------------*/
//include classes and functions
require_once	('./classes/class.Database.php');
require_once	('./classes/class.Gui.php');
require_once	('./classes/class.TimestampToDate.php');

//include language_file
require_once	('./lang/DP_'.$DP_Lang.'.lang.php');

//include keyword functions
require_once	('./includes/inc.keywords.php');

//include keyword functions
require_once	('./includes/inc.sortdates.php');


//include keyword functions
require_once	('./includes/inc.minicalendar.php');

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
			location = "inbox.php?ScreenHeight=" + height + "&ScreenWith=" + width + "&" + SidName + "=" + sid + "&JSscript=" + JSscript;   
		}
	</script>
		 ';
	$DP_JSscript		= $HTTP_GET_VARS[JSscript] ;
	if($HTTP_GET_VARS[ScreenWith]) {
		$DP_ScreenWith	= $HTTP_GET_VARS[ScreenWith] ;
		session_register("DP_ScreenWith");
	}else {
		$DP_JSscript	= 0 ;
	}
	if($HTTP_GET_VARS[ScreenHeight]) {
		$DP_ScreenHeight	= $HTTP_GET_VARS[ScreenHeight] ;
		session_register("DP_ScreenHeight");
	}else {
		$DP_JSscript	= 0 ;
	}
	session_register("DP_JSscript");
}
?>
