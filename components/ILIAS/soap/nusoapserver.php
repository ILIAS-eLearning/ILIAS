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

declare(strict_types=1);

if (!defined('ILIAS_MODULE') || ILIAS_MODULE !== 'components/ILIAS/soap') {
    //direct call to this endpoint
    chdir('../..');
    define('ILIAS_MODULE', 'components/ILIAS/soap');
    define('IL_SOAPMODE_NUSOAP', 0);
    define('IL_SOAPMODE_INTERNAL', 1);
    define('IL_SOAPMODE', IL_SOAPMODE_NUSOAP);
}

if (!isset($ilIliasIniFile)) {
    $ilIliasIniFile = new ilIniFile(__DIR__ . "/../../../ilias.ini.php");
    $ilIliasIniFile->read();
}

$server = new ilNusoapUserAdministrationAdapter(true, $ilIliasIniFile);
$server->start();
