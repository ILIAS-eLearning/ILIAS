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
* dateplaner
* includes personal an group specific date management  
* developt for ilias3 and adapted also to ilias2
*
* @author		Frank Gruemmert <gruemmert@feuerwelt.de>
* @organisation University of Applied Sciences Bremen
* @version		$Id$
*/ 


// remove tags from all post items
if (is_array($_POST))
{
	foreach($_POST as $k => $v)
	{
//echo "<br>-$_POST[$k]-".strip_tags($_POST[$k])."-";
		$_POST[$k] = strip_tags($_POST[$k]);
	}
}

/** ------------------------------------------------------------------------------+
* Modul porperties/settings for the dateplaner
*
*	please take care that changes to be needed. 
*	
*/

$_REQUEST["app"] = str_replace(array(".", "/", "\\", "%"), "", $_REQUEST["app"]);

	/**
	* ilias module directory (up to the ilias root dir)
	* @ var string
	* @ access private
	*/

	$modulDir = "/calendar";

/** 
* End modulsettings
* u can find mor setting into the dateppaner modul dir and subdir "/conf"
* ------------------------------------------------------------------------------+
*/

// get ilias connectivity 
require_once	('./include/inc.header.php');

// catch hack attempts
if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
{
	$ilias->raiseError($lng->txt("msg_not_available_for_anon"),$ilias->error_obj->MESSAGE);
}

//
// main
//

/*static variables */
define("DATEPLANER_ROOT_DIR", $modulDir ); /* relative path to the dateplaner directory */
	
/*dynamic variables and interface connection to ilias*/
require_once	('.'.DATEPLANER_ROOT_DIR.'/classes/class.ilCalInterface.php');
	$Interface		= new ilCalInterface($ilias);

	/* if the gui used without frames */
	if(!$uptext) {
		$uptext			= $Interface->getFrameDec();
	}
	

	/* load language strings into private array for the tateplaner*/
	$lng->loadLanguageModule("dateplaner");	
	$DP_language	= $lng->text;

	/* other dynamic variables used in the dateplaner */
	$DP_UId			= $Interface->getUId();				// UserID
	$DP_Lang		= $Interface->getLang();			// language, selected by the user
	$DP_Skin		= $Interface->getSkin();			// style(-sheet)-name, selected by the user 
	$DP_Style		= $Interface->getStyle();			// skin-name, selected by the user 
	$DP_StyleFname	= $Interface->getStyleFname();		// style(-sheet)-name including path, selected by the user 
	$DP_GroupIds	= $Interface->getGroupIds();		// GroupIDs of the current UserID (stub)
	$DP_dlI			= $Interface->getDpDBHandler ();	// dateplaner database handler
	$app			= $_REQUEST["app"];					// dateplaner application
	
		/*

		$tpl->addBlockFile("LOCATOR","locator","tpl.locator.html");
		$tpl->setCurrentBlock("locator_item");
		$tpl->setVariable("LINK_ITEM","./search.php");
		$tpl->setVariable("LINK_TARGET","bottom");
		$tpl->setVariable("ITEM",$lng->txt("mail_search_word"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("locator");
		$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
		$tpl->parseCurrentBlock();*/

header('Content-type: text/html; charset=UTF-8');

// include DP Header 
require	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.dp.header.php');
// include DP Output functions 
require	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.output.php');

/* ----------------  session initialisation -----------------------*/
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.session.php');
// uncoment for ilias 2.3.8 Session Handler 
//db_session_write(session_id(),session_encode());
/* --------------  end session initialisation ---------------------*/

/*dateplaner functions*/

include ('.'.DATEPLANER_ROOT_DIR.'/classes/class.ilMiniCal.php');


if (!$_GET["month"])
{
	$month = date(m);
	$year = date(Y);
}
else
{
	$month = $_GET["month"];	
	$year = $_GET["year"];
}
$MiniCal = new ilMiniCal();

$CALENDAR = $MiniCal->show($month, $year, $MiniCal);


switch($_REQUEST["app"]) {
	case False :
	case 'inbox':
			
		$PAGETITLE	= $DP_language[app_.$_REQUEST["app"]];	
										// set page titel
		$locator		= $Interface->showLocator($tpl, $lng,$app); // Locate for ilias3
		include	('.'.DATEPLANER_ROOT_DIR.'/inbox.php');												// include specific datplaner function
				
		
		break;
	case 'date':
		
		if ($_REQUEST["date_id"]){
			$DateArray		= $DB->getDate ($_REQUEST["date_id"], $DP_UId);
			$PAGETITLE		= $DP_language[app_.$_REQUEST["app"]]." : ".$DateArray[8];		// set page titel
		} else {
			$PAGETITLE		= $DP_language[app_.$_REQUEST["app"]]." : ".@$DateValues[shorttext];	// set page titel
		}

		include	('.'.DATEPLANER_ROOT_DIR.'/date.php');												// include specific datplaner function
		
		break;
	default :
	
		$PAGETITLE	= $DP_language[app_.$_REQUEST["app"]];											// set page titel
		$locator		= $Interface->showLocator($tpl, $lng,$app); // Locate for ilias3
		include	('.'.DATEPLANER_ROOT_DIR.'/'.$_REQUEST["app"].'.php');	
		
							// include specific datplaner function
}

?>

