<?php

chdir('..');


$cookie_path = dirname(dirname($_SERVER['PHP_SELF']));

/* if ilias is called directly within the docroot $cookie_path
is set to '/' expecting on servers running under windows..
here it is set to '\'.
in both cases a further '/' won't be appended due to the following regex
*/
$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

if (isset($_GET["client_id"])) {
    if ($cookie_path == "\\") {
        $cookie_path = '/';
    }

    setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, '');
    $_COOKIE["ilClientId"] = $_GET["client_id"];
}

define('IL_COOKIE_PATH', $cookie_path);

ilContext::init(ilContext::CONTEXT_APACHE_SSO);

ilInitialisation::initILIAS();

// @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
// $ilCtrl->setCmd('doApacheAuthentication');
$ilCtrl->callBaseClass('ilStartUpGUI');