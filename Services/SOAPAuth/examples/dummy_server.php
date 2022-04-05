<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

exit;

/**
 * dummy soap authentication server
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
chdir('../..');

global $HTTP_RAW_POST_DATA;

ini_set("display_errors", "1");
error_reporting(E_ALL & ~E_NOTICE);
include_once './Services/SOAPAuth/examples/class.ilSoapDummyAuthServer.php';
$server = new ilSoapDummyAuthServer();
$server->start();
