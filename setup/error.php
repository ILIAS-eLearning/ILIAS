<?php
try {
    chdir("..");
    define('IL_INITIAL_WD', getcwd());
    if (is_dir("./pear")) {
        ini_set("include_path", "./pear:" . ini_get("include_path"));
    }

    require_once "./setup/include/inc.setup_header.php";

    $tpl = new ilTemplate("tpl.error_page.html", true, true, "setup");

    if ($_SESSION["ClientId"] != "") {
        $tpl->setCurrentBlock("client_setup");
        $tpl->setVariable("CLIENT_ID", $_SESSION["ClientId"]);
        $tpl->parseCurrentBlock();
    }

    $tpl->setVariable("PAGETITLE", "Setup");
    //$this->tpl->setVariable("LOCATION_STYLESHEET","./templates/blueshadow.css");
    $tpl->setVariable("LOCATION_STYLESHEET", "../templates/default/delos.css");
    $tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "./css/setup.css");
    $tpl->setVariable("TXT_SETUP", "ILIAS Setup");

    $tpl->setVariable("CONTENT", '<div class="alert alert-danger" role="alert">' . $_SESSION["failure"] . '</div>');
    $tpl->show();
} catch (Exception $e) {
    if (defined('DEVMODE') && DEVMODE) {
        throw $e;
    }

    die($e->getMessage());
}
