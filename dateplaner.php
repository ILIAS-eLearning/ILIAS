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
require_once	('./include/inc.header.php');

//
// main
//

// catch hack attempts
if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
{
	$ilias->raiseError($lng->txt("msg_not_available_for_anon"),$ilias->error_obj->MESSAGE);
}

/*static variables */
define("DATEPLANER_ROOT_DIR", "/modules/dateplaner"); /* relative path to the dateplaner directory */

/*dynamic variables */
require_once	('.'.DATEPLANER_ROOT_DIR.'/classes/class.interface.php');
	$Interface		= new Interface($ilias);

/* if the gui used without frames */
if(!$uptext) {
	$uptext			= $Interface->getFrameDec();
}

/* other dynamic variables used in the dateplaner */

	$DP_UId			= $Interface->getUId();
	$DP_Lang		= $Interface->getLang();
	$DP_Skin		= $Interface->getSkin();
	$DP_Style		= $Interface->getStyle();
	$DP_StyleFname	= $Interface->getStyleFname();
	$DP_GroupIds	= $Interface->getGroupIds();
	$DP_dlI			= $Interface->getDpDBHandler ();
//echo "<BR><B>AUSGABE (dateplaner.php:57):</B> <BR>";
//print_r($DP_GroupIds);
//echo "<BR><hr>";
// include DP Header 
require	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.dp.header.php');
// include DP Output functions 
require	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.output.php');

/* ----------------  session initialisation -----------------------*/
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.session.php');
// uncoment for ilias 2.3.8 Session Handler 
//db_session_write(session_id(),session_encode());
/* --------------  end session initialisation ---------------------*/

$app		= $_REQUEST["app"];

/*dateplaner functions*/
switch($_REQUEST["app"]) {
	case False :
	case 'inbox':
		$PAGETITLE	= $DP_language[app_.$_REQUEST["app"]];
		include	('.'.DATEPLANER_ROOT_DIR.'/inbox.php');
		break;
	case 'date':
		if ($_REQUEST["date_id"]){
			$DateArray		= $DB->getDate ($_REQUEST["date_id"], $DP_UId);
			$PAGETITLE		= $DP_language[app_.$_REQUEST["app"]]." : ".$DateArray[8];			// Page Titel setzten
		} else {
			$PAGETITLE		= $DP_language[app_.$_REQUEST["app"]]." : ".@$DateValues[shorttext];	// Page Titel setzten
		}
		include	('.'.DATEPLANER_ROOT_DIR.'/date.php');
		
		break;
	default :
		$PAGETITLE	= $DP_language[app_.$_REQUEST["app"]];
		include	('.'.DATEPLANER_ROOT_DIR.'/'.$_REQUEST["app"].'.php');
}
?>
