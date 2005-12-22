<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* redirection script
* todo: (a better solution should control the processing
* via a xml file)
*
* $_GET["target"]  should be of format <type>_<id>
*
* @author Alex Killing <alex.killing@gmx.de>
* @package ilias-core
* @version $Id$
*/

//var_dump ($_SESSION);
//var_dump ($_COOKIE);

// this should bring us all session data of the desired
// client
if (isset($_GET["client_id"]))
{
	setcookie("ilClientId",$_GET["client_id"]);
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}
//echo "1";
require_once "./include/inc.header.php";
//echo "2";
$target_arr = explode("_", $_GET["target"]);
$target_type = $target_arr[0];
$target_id = $target_arr[1];
$target_ref_id = $target_arr[2];		// optional for pages

switch($target_type)
{
	// learning module pages
	case "pg":
		require_once("content/classes/class.ilLMPageObject.php");
		ilLMPageObject::_goto($target_id, $target_ref_id);
		include("ilias.php");
		break;

	// learning module chapters
	case "st":
		require_once("content/classes/class.ilStructureObject.php");
		ilStructureObject::_goto($target_id);
		include("ilias.php");
		break;
		
// glossar entries
	case "glo":
	case "git":
		require_once("content/classes/class.ilGlossaryTerm.php");
		ilGlossaryTerm::_goto($target_id,$target_type);
		break;
		
	case "lm":
		require_once("./content/classes/class.ilObjContentObject.php");
		ilObjContentObject::_goto($target_id);
		include("ilias.php");
		break;
		
	case "frm":
		require_once("./classes/class.ilObjForum.php");
		$target_thread = $target_arr[2];
		ilObjForum::_goto($target_id, $target_thread);
		break;
		
	case "exc":
		require_once("./classes/class.ilObjExercise.php");
		ilObjExercise::_goto($target_id);
		break;
		
	case "tst":
		require_once("./assessment/classes/class.ilObjTest.php");
		ilObjTest::_goto($target_id);
		break;

	case "svy":
		require_once("./survey/classes/class.ilObjSurvey.php");
		if (array_key_exists("accesscode", $_GET))
		{
			ilObjSurvey::_goto($target_id, $_GET["accesscode"]);
		}
		else
		{
			ilObjSurvey::_goto($target_id);
		}
		break;

	case "webr":
		require_once("./link/classes/class.ilObjLinkResource.php");
		ilObjLinkResource::_goto($target_id);
		break;

}

?>
