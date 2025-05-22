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

chdir("../../..");
ilInitialisation::initILIAS();
include_once "./components/ILIAS/soap/include/inc.soap_functions.php";
$results = [];
foreach ($_POST as $key => $value) {
    if (preg_match("/value_(\d+)_1/", $key, $matches)) {
        array_push($results, $_POST["value_" . $matches[1] . "_1"]);
        array_push($results, $_POST["value_" . $matches[1] . "_2"]);
        array_push($results, $_POST["points_" . $matches[1]]);
    }
}
$res = ilSoapFunctions::saveQuestion($_POST["session_id"] . "::" . $_POST["client"], $_POST["active_id"], $_POST["question_id"], $_POST["pass"], $results);
if ($res === true) {
    global $DIC;
    $lng = $DIC['lng'];
    $lng->loadLanguageModule("assessment");
    echo $lng->txt("result_successful_saved");
} else {
    global $DIC;
    $lng = $DIC['lng'];
    $lng->loadLanguageModule("assessment");
    echo $lng->txt("result_unsuccessful_saved");
}
