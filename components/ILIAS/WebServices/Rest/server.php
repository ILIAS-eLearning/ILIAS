<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../..');

ilContext::init(ilContext::CONTEXT_REST);

$_COOKIE['client_id'] = $_GET['client_id'] = $_REQUEST['client_id'];

ilInitialisation::initILIAS();

$server = new ilRestServer(
    [
        'settings' => [
            'displayErrorDetails' => true
        ]
    ]
);
$server->init();
$server->run();
