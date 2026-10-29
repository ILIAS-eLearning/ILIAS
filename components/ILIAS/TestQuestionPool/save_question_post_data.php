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

/** @var ILIAS\DI\Container $DIC */
global $DIC;

$http = $DIC->http();
$post = $http->wrapper()->post();
$refinery = $DIC['refinery'];
$string = $refinery->kindlyTo()->string();
$int = $refinery->kindlyTo()->int();

$results = [];
foreach ($post->keys() as $key) {
    if (preg_match("/value_(\d+)_1/", $key, $matches)) {
        $id = $matches[1];
        $results[] = (string) $post->retrieve("value_{$id}_1", $string);
        $results[] = (string) $post->retrieve("value_{$id}_2", $string);
        $results[] = (string) $post->retrieve("points_{$id}", $string);
    }
}

$session_client = "{$post->retrieve('session_id', $string)}::{$post->retrieve('client', $string)}";
$active_id = (int) $post->retrieve('active_id', $int);
$question_id = (int) $post->retrieve('question_id', $int);
$pass = (int) $post->retrieve('pass', $int);

$res = ilSoapFunctions::saveQuestion($session_client, $active_id, $question_id, $pass, $results);
if ($res === true) {
    $lng = $DIC['lng'];
    $lng->loadLanguageModule("assessment");
    echo $lng->txt("result_successful_saved");
} else {
    $lng = $DIC['lng'];
    $lng->loadLanguageModule("assessment");
    echo $lng->txt("result_unsuccessful_saved");
}
