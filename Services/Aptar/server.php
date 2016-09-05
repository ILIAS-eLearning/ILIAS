<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
chdir('../..');

require_once  'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SOAP);

require_once 'Services/Aptar/classes/class.ilAptarSoapServer.php';
$server = ilAptarSoapServer::getInstance();
$server->handleRequest();
