<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../..');

include_once 'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_REST);

include_once './include/inc.header.php';


include_once './Services/WebServices/Rest/classes/class.ilRestServer.php';
$server = new ilRestServer();
$server->config('debug', true);
$server->init();
$server->run();



?>
