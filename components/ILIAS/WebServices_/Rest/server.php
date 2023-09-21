<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../../..');

include_once 'components/ILIAS/Context_/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_REST);

$_COOKIE['client_id'] = $_GET['client_id'] = $_REQUEST['client_id'];

include_once './cli/inc.header.php';


include_once './components/ILIAS/WebServices_/Rest/classes/class.ilRestServer.php';
$server = new ilRestServer(
    [
        'settings' => [
            'displayErrorDetails' => true
        ]
    ]
);
$server->init();
$server->run();
