<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

// special handling for direct navigation request
require_once "./Services/Navigation/classes/class.ilNavigationHistoryGUI.php";
$nav_hist = new ilNavigationHistoryGUI();
$nav_hist->handleNavigationRequest();

// store original parameter before plugin slot may influence it
$orig_target = $_GET['target'];

// user interface plugin slot hook
if (is_object($ilPluginAdmin)) {
    // get user interface plugins
    $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");

    // search
    foreach ($pl_names as $pl) {
        $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
        $gui_class = $ui_plugin->getUIClassInstance();
        $gui_class->gotoHook();
    }
}

$r_pos = strpos($_GET["target"], "_");
$rest = substr($_GET["target"], $r_pos+1);
$target_arr = explode("_", $_GET["target"]);
$target_type = $target_arr[0];
$target_id = $target_arr[1];
$additional = $target_arr[2];		// optional for pages

// imprint has no ref id...
if ($target_type == "impr") {
    ilUtil::redirect('ilias.php?baseClass=ilImprintGUI');
}

// goto is not granted?
include_once("Services/Init/classes/class.ilStartUpGUI.php");
if (!ilStartUpGUI::_checkGoto($_GET["target"])) {
    // if anonymous: go to login page
    if (!$ilUser->getId() || $ilUser->isAnonymous()) {
        ilUtil::redirect("login.php?target=" . $orig_target . "&cmd=force_login&lang=" . $ilUser->getCurrentLanguage());
    } else {
        // message if target given but not accessible
        $tarr = explode("_", $_GET["target"]);
        if ($tarr[0] != "pg" && $tarr[0] != "st" && $tarr[1] > 0) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($tarr[1]))
            ), true);
        } else {
            ilUtil::sendFailure($lng->txt('permission_denied'), true);
        }
        ilUtil::redirect(ilUserUtil::getStartingPointAsUrl());
    }
}


// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//
//               FOR NEW OBJECT TYPES:
//       PLEASE USE DEFAULT IMPLEMENTATION ONLY
//
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

switch ($target_type) {
    // exception, must be kept for now
    case "pg":
        ilLMPageObjectGUI::_goto($rest);
        break;

    // exception, must be kept for now
    case "st":
        ilStructureObjectGUI::_goto($target_id, $additional);
        break;

    // exception, must be kept for now
    case "git":
        $target_ref_id = $target_arr[2];
        ilGlossaryTermGUI::_goto($target_id, $target_ref_id);
        break;

    // please migrate to default branch implementation
    case "glo":
        ilObjGlossaryGUI::_goto($target_id);
        break;
                
    // please migrate to default branch implementation
    case "lm":
        ilObjContentObjectGUI::_goto($target_id);
        break;

    // please migrate to default branch implementation
    case "htlm":
        ilObjFileBasedLMGUI::_goto($target_id);
        break;
        
    // please migrate to default branch implementation
    case "frm":
        require_once("./Modules/Forum/classes/class.ilObjForumGUI.php");
        $target_thread = $target_arr[2];
        $target_posting = $target_arr[3];
        ilObjForumGUI::_goto($target_id, $target_thread, $target_posting);
        break;
        
    // please migrate to default branch implementation
    case "exc":
        ilObjExerciseGUI::_goto($target_id, $rest);
        break;
        
    // please migrate to default branch implementation
    case "tst":
        require_once("./Modules/Test/classes/class.ilObjTestGUI.php");
        ilObjTestGUI::_goto($target_id);
        break;
    
    // please migrate to default branch implementation
    case "qpl":
        require_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPoolGUI.php");
        ilObjQuestionPoolGUI::_goto($target_id);
        break;

    // please migrate to default branch implementation
    case "spl":
        require_once("./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPoolGUI.php");
        ilObjSurveyQuestionPoolGUI::_goto($target_id);
        break;

    // please migrate to default branch implementation
    case "svy":
        require_once("./Modules/Survey/classes/class.ilObjSurveyGUI.php");
        if (array_key_exists("accesscode", $_GET)) {
            ilObjSurveyGUI::_goto($target_id, $_GET["accesscode"]);
        } else {
            ilObjSurveyGUI::_goto($target_id);
        }
        break;

    // please migrate to default branch implementation
    case "webr":
        require_once("./Modules/WebResource/classes/class.ilObjLinkResourceGUI.php");
        ilObjLinkResourceGUI::_goto($target_id, $rest);
        break;

    // please migrate to default branch implementation
    case "sahs":
        require_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleGUI.php");
        ilObjSAHSLearningModuleGUI::_goto($target_id);
        break;

    // please migrate to default branch implementation
    case "cat":
        require_once("./Modules/Category/classes/class.ilObjCategoryGUI.php");
        ilObjCategoryGUI::_goto($target_id);
        break;

    // please migrate to default branch implementation
    case "crs":
        require_once("Modules/Course/classes/class.ilObjCourseGUI.php");
        ilObjCourseGUI::_goto($target_id, $additional);
        break;

    // please migrate to default branch implementation
    case "grp":
        require_once("./Modules/Group/classes/class.ilObjGroupGUI.php");
        ilObjGroupGUI::_goto($target_id, $additional);
        break;
        
    // please migrate to default branch implementation
    case 'fold':
        require_once("./Modules/Folder/classes/class.ilObjFolderGUI.php");
        ilObjFolderGUI::_goto($target_id);
        break;
    
    // please migrate to default branch implementation
    case "file":
        require_once("./Modules/File/classes/class.ilObjFileGUI.php");
        ilObjFileGUI::_goto($target_id, $rest);
        break;

    // please migrate to default branch implementation
    case "mcst":
        ilObjMediaCastGUI::_goto($target_id);
        break;

    // please migrate to default branch implementation
    case 'root':
        require_once('./Modules/RootFolder/classes/class.ilObjRootFolderGUI.php');
        ilObjRootFolderGUI::_goto($target_id);
        break;
        
    // please migrate to default branch implementation
    case 'cert':
        require_once('./Services/Certificate/classes/class.ilCertificate.php');
        ilCertificate::_goto($target_id);
        break;

    // links to the documentation of the kitchen sink in the administration
    case 'stys':
        require_once('./Services/Style/System/classes/class.ilSystemStyleMainGUI.php');
        ilSystemStyleMainGUI::_goto($target_id, $target_arr);
        break;

    //
    // default implementation (should be used by all new object types)
    //
    default:
        if (!$objDefinition->isPlugin($target_type)) {
            $class_name = "ilObj" . $objDefinition->getClassName($target_type) . "GUI";
            $location = $objDefinition->getLocation($target_type);
            if (is_file($location . "/class." . $class_name . ".php")) {
                include_once($location . "/class." . $class_name . ".php");
                call_user_func(array($class_name, "_goto"), $rest);
            }
        } else {
            $class_name = "ilObj" . $objDefinition->getClassName($target_type) . "GUI";
            $location = $objDefinition->getLocation($target_type);
            if (is_file($location . "/class." . $class_name . ".php")) {
                include_once($location . "/class." . $class_name . ".php");
                call_user_func(array($class_name, "_goto"), array($rest, $class_name));
            }
        }
        break;
}
