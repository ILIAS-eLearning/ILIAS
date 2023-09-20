<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../../..');

include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/Context_/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_REST);

$_COOKIE['client_id'] = $_GET['client_id'] = $_REQUEST['client_id'];

include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/cli/inc.header.php';


include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/WebServices_/Rest/classes/class.ilRestServer.php';
$server = new ilRestServer(
    [
        'settings' => [
            'displayErrorDetails' => true
        ]
    ]
);
$server->init();
$server->run();
