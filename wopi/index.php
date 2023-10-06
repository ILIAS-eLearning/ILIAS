<?php

// this is the base file for WOPI requests. It responds to the following requests:
// - CheckFileInfo
// - GetFile
// - PutFile
// - Lock
// - Unlock

// INIT ILIAS
use ILIAS\Services\WOPI\Handler\RequestHandler;

chdir("../");
require_once __DIR__ . "/../libs/composer/vendor/autoload.php";
ilInitialisation::initILIAS();

// handle all requests behind /wopi/index.php/
$handler = new RequestHandler();
$handler->handleRequest();
