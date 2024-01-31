<?php

// this is the base file for WOPI requests. It responds to the following requests:
// - CheckFileInfo
// - GetFile
// - PutFile
// - Lock
// - Unlock

// INIT ILIAS
use ILIAS\components\WOPI\Handler\RequestHandler;

chdir("../../");
require_once __DIR__ . "../../vendor/composer/vendor/autoload.php";
ilInitialisation::initILIAS();

// handle all requests behind /public/wopi/index.php/
$handler = new RequestHandler();
$handler->handleRequest();