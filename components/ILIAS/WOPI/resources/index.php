<?php

// this is the base file for WOPI requests. It responds to the following requests:
// - CheckFileInfo
// - GetFile
// - PutFile
// - Lock
// - Unlock

// INIT ILIAS
require_once __DIR__ . "/../../vendor/composer/vendor/autoload.php";
ilInitialisation::initILIAS();

// handle all requests behind /wopi/index.php/
$handler = new ILIAS\components\WOPI\Handler\RequestHandler();
$handler->handleRequest();
