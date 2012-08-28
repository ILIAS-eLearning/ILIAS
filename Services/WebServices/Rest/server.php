<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../..');

include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_SOAP);

include_once 'Services/Init/classes/class.ilInitialisation.php';
$ilInit = new ilInitialisation();
$GLOBALS['ilInit'] = $ilInit;
$ilInit->initILIAS('webdav');


include_once './Services/WebServices/Rest/classes/class.ilRestServer.php';
$server = new ilRestServer();
$server->config('debug', true);
$server->init();
$server->run();



?>
