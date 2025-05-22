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

chdir("./../../../");
$ini = new ilIniFile("ilias.ini.php");
$ini->read();

$http = $ini->readVariable("server", "http_path");
$http = preg_replace("/^(https:\/\/)|(http:\/\/)+/", "", $http);

$_SERVER['HTTP_HOST'] = $http;
$_SERVER['REQUEST_URI'] = "";

ilInitialisation::initILIAS();

global $DIC;

$ilErr = $DIC['ilErr'];

$ilErr->raiseError("This is your error message", $ilErr->FATAL);
