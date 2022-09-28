<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/** @noRector */
require_once("libs/composer/vendor/autoload.php");

ilInitialisation::initILIAS();
global $DIC, $ilPluginAdmin;

$requested_target = $DIC->http()->wrapper()->query()->has("target")
    ? $DIC->http()->wrapper()->query()->retrieve(
        "target",
        $DIC->refinery()->to()->string()
    )
    : '';

// special handling for direct navigation request
$nav_hist = new ilNavigationHistoryGUI();
$nav_hist->handleNavigationRequest();

// store original parameter before plugin slot may influence it
$orig_target = $requested_target;

// user interface plugin slot hook
if (is_object($ilPluginAdmin)) {
    // search
    foreach ($DIC["component.factory"]->getActivePluginsInSlot("uihk") as $ui_plugin) {
        $gui_class = $ui_plugin->getUIClassInstance();
        $gui_class->gotoHook();
    }
}

$r_pos = strpos($requested_target, "_");
$rest = substr($requested_target, $r_pos + 1);
$target_arr = explode("_", $requested_target);
$target_type = $target_arr[0];
$target_id = $target_arr[1];
$additional = $target_arr[2] ?? '';		// optional for pages

// imprint has no ref id...
if ($target_type === "impr") {
    $DIC->ctrl()->redirectToURL('ilias.php?baseClass=ilImprintGUI');
}

// goto is not granted?
if (!ilStartUpGUI::_checkGoto($requested_target)) {
    // if anonymous: go to login page
    if ($DIC->user()->getId() === 0 || $DIC->user()->isAnonymous()) {
        $DIC->ctrl()->redirectToURL(
            "login.php?target="
            . $orig_target . "&cmd=force_login&lang="
            . $DIC->user()->getCurrentLanguage()
        );
    } else {
        // message if target given but not accessible
        $tarr = explode("_", $requested_target);
        if ($tarr[0] != "pg" && $tarr[0] != "st" && $tarr[1] > 0) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage(
                'failure',
                sprintf(
                    $DIC->language()->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($tarr[1]))
                ),
                true
            );
        } else {
            global $DIC;
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $DIC->language()->txt('permission_denied'), true);
        }
        $DIC->ctrl()->redirectToURL(ilUserUtil::getStartingPointAsUrl());
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
        ilStructureObjectGUI::_goto($target_id, (int) $additional);
        break;

        // exception, must be kept for now
    case "git":
        $target_ref_id = $target_arr[2] ?? 0;
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
        $target_thread = isset($target_arr[2]) ? $target_arr[2] : 0;
        $target_posting = isset($target_arr[3]) ? $target_arr[3] : 0;
        ilObjForumGUI::_goto($target_id, $target_thread, $target_posting);
        break;

        // please migrate to default branch implementation
    case "exc":
        ilObjExerciseGUI::_goto($target_id, $rest);
        break;

        // please migrate to default branch implementation
    case "tst":
        ilObjTestGUI::_goto($target_id);
        break;

        // please migrate to default branch implementation
    case "qpl":
        ilObjQuestionPoolGUI::_goto($target_id);
        break;

        // please migrate to default branch implementation
    case "webr":
        ilObjLinkResourceGUI::_goto($target_id, $rest);
        break;

        // please migrate to default branch implementation
    case "sahs":
        ilObjSAHSLearningModuleGUI::_goto($target_id);
        break;

        // please migrate to default branch implementation
    case "crs":
        ilObjCourseGUI::_goto($target_id, $additional);
        break;

        // please migrate to default branch implementation
    case "grp":
        ilObjGroupGUI::_goto($target_id, $additional);
        break;

        // please migrate to default branch implementation
    case "file":
        ilObjFileGUI::_goto($target_id, $rest);
        break;

        // please migrate to default branch implementation
    case 'cert':
        ilCertificate::_goto($target_id);
        break;

        // links to the documentation of the kitchen sink in the administration
    case 'stys':
        (new ilKSDocumentationGotoLink())->redirectWithGotoLink($target_id, $target_arr, $DIC->ctrl());
        break;

    //
        // default implementation (should be used by all new object types)
    //
    default:
        global $objDefinition;
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
                call_user_func(array($class_name, "_goto"), array($rest, $class_name));
            }
        }
        break;
}
