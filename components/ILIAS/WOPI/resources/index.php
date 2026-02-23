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

use ILIAS\WOPI\Handler\RequestHandler;

// this is the base file for WOPI requests. It responds to the following requests:
// - CheckFileInfo
// - GetFile
// - PutFile
// - Lock
// - Unlock
// INIT ILIAS
require_once("../../vendor/composer/vendor/autoload.php");
require_once("../../artifacts/bootstrap_default.php");

entry_point("ILIAS Legacy Initialisation Adapter");

// handle all requests behind /wopi/index.php/
$handler = new RequestHandler();
$handler->handleRequest();
