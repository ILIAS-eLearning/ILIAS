<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir("../..");
require_once "./include/inc.header.php";
include_once "./webservice/soap/include/inc.soap_functions.php";
$results = array();
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
