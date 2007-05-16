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

require_once "./include/inc.header.php";

// special handling for direct navigation request
require_once "./Services/Navigation/classes/class.ilNavigationHistoryGUI.php";
$nav_hist = new ilNavigationHistoryGUI();
$nav_hist->handleNavigationRequest();

$target_arr = explode("_", $_GET["target"]);
$target_type = $target_arr[0];
$target_id = $target_arr[1];
$target_ref_id = $target_arr[2];		// optional for pages

// if anonymous and goto is not granted: go to login page
include_once("Services/Init/classes/class.ilStartUpGUI.php");
if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID && !ilStartUpGUI::_checkGoto($_GET["target"]))
{
	ilUtil::redirect("login.php?target=".$_GET["target"]."&cmd=force_login&lang=".$ilUser->getCurrentLanguage());
}

switch($target_type)
{
	// learning module pages
	case "pg":
		require_once("./Modules/LearningModule/classes/class.ilLMPageObjectGUI.php");
		ilLMPageObjectGUI::_goto($target_id, $target_ref_id);
		break;

	// learning module chapters
	case "st":
		require_once("./Modules/LearningModule/classes/class.ilStructureObjectGUI.php");
		ilStructureObjectGUI::_goto($target_id, $target_ref_id);
		break;

	// new implementation: ok
	case "git":
		require_once("./Modules/Glossary/classes/class.ilGlossaryTermGUI.php");
		$target_ref_id = $target_arr[2];
		ilGlossaryTermGUI::_goto($target_id, $target_ref_id);
		break;

	// new implementation: ok
	case "glo":
		require_once("./Modules/Glossary/classes/class.ilObjGlossaryGUI.php");
		ilObjGlossaryGUI::_goto($target_id);
		break;
				
	// new implementation: ok
	case "lm":
	case "dbk":
		require_once("./Modules/LearningModule/classes/class.ilObjContentObjectGUI.php");
		ilObjContentObjectGUI::_goto($target_id);
		break;

	// new implementation: ok
	case "htlm":
		require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMGUI.php");
		ilObjFileBasedLMGUI::_goto($target_id);
		break;
		
	// new implementation: ok
	case "frm":
		require_once("./Modules/Forum/classes/class.ilObjForumGUI.php");
		$target_thread = $target_arr[2];
		$target_posting = $target_arr[3];
		ilObjForumGUI::_goto($target_id, $target_thread, $target_posting);
		break;
		
	// new implementation: ok
	case "exc":
		require_once("./Modules/Exercise/classes/class.ilObjExerciseGUI.php");
		ilObjExerciseGUI::_goto($target_id);
		break;
		
	// new implementation: ok
	case "tst":
		require_once("./Modules/Test/classes/class.ilObjTestGUI.php");
		ilObjTestGUI::_goto($target_id);
		break;

	// new implementation: ok
	case "svy":
		require_once("./Modules/Survey/classes/class.ilObjSurveyGUI.php");
		if (array_key_exists("accesscode", $_GET))
		{
			ilObjSurveyGUI::_goto($target_id, $_GET["accesscode"]);
		}
		else
		{
			ilObjSurveyGUI::_goto($target_id);
		}
		break;

	// new implementation: ok
	case "webr":
		require_once("./Modules/WebResource/classes/class.ilObjLinkResourceGUI.php");
		ilObjLinkResourceGUI::_goto($target_id);
		break;

	// new implementation: ok
	case "chat":
		require_once("./Modules/Chat/classes/class.ilObjChatGUI.php");
		ilObjChatGUI::_goto($target_id);
		break;

	// new implementation: ok
	case "sahs":
		require_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleGUI.php");
		ilObjSAHSLearningModuleGUI::_goto($target_id);
		break;

	// new implementation: ok
	case "cat":
		require_once("./Modules/Category/classes/class.ilObjCategoryGUI.php");
		ilObjCategoryGUI::_goto($target_id);
		break;

	// new implementation: ok
	case "crs":
		require_once("Modules/Course/classes/class.ilObjCourseGUI.php");
		ilObjCourseGUI::_goto($target_id);
		break;

	// new implementation: ok
	case "grp":
		require_once("classes/class.ilObjGroupGUI.php");
		ilObjGroupGUI::_goto($target_id);
		break;
		
	// new implementation: ok (smeyer) 
	case 'fold':
		require_once("classes/class.ilObjFolderGUI.php");
		ilObjFolderGUI::_goto($target_id);
		break;
	
	// new implementation: ok
	case "file":
		require_once("./Modules/File/classes/class.ilObjFileGUI.php");
		ilObjFileGUI::_goto($target_id);
		break;

	// new implementation: ok
	case "mcst":
		require_once("./Modules/MediaCast/classes/class.ilObjMediaCastGUI.php");
		ilObjMediaCastGUI::_goto($target_id);
		break;
		
	case "icrs":
		require_once("ilinc/classes/class.ilObjiLincCourse.php");
		ilObjiLincCourse::_goto($target_id);
		include("repository.php");
		break;
	
	// new implementation: ok
	case 'root':
		require_once('classes/class.ilObjRootFolderGUI.php');
		ilObjRootFolderGUI::_goto($target_id);
		break;
}
?>
