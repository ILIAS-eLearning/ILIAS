<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_STRICT);
header("Content-Type: text/plain, charset=utf-8");
include_once("gev_utils.php");

function import_ilias() {
    // ILIAS core requires an authenticated user to use its API, unless the
    // called script name is index.php (see ilInitialisation::authenticate()).
    // When our script is being executed, we don't have a user and thus
    // cannot authenticate them - which means we need to fiddle with the
    // environment variables to work around this behaviour.
    $php_self = $_SERVER['PHP_SELF'];
    $_SERVER['PHP_SELF'] = str_replace(basename(__file__), 'index.php', $php_self);
    include("./include/inc.header.php");
    $_SERVER['PHP_SELF'] = $php_self;
}

import_ilias();
$import = get_gev_import();
$import->update_imported_shadow_users();



?>
