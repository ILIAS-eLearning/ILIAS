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

/* ----------------------------------   Include Datei ----------------------------------------*/
//include classes and functions
require_once	('.'.DATEPLANER_ROOT_DIR.'/classes/class.Database.php');

// Objects
$DB			= new database($DP_dlI);

require_once	('.'.DATEPLANER_ROOT_DIR.'/classes/class.Gui.php');

// Objects
$Gui		= new Gui();

require_once	('.'.DATEPLANER_ROOT_DIR.'/classes/class.TimestampToDate.php');

//include language_file
require_once	('.'.DATEPLANER_ROOT_DIR.'/lang/dp_'.$DP_Lang.'.lang.php');

//include keyword functions
require_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.keywords.php');

//include keyword functions
require_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.sortdates.php');


//include keyword functions
require_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.minicalendar.php');

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
