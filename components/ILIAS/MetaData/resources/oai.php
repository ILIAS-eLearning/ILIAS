<?php

use ILIAS\MetaData\OERExposer\OAIPMH\Handler;

/*
 * Handles OAI-PMH request according to https://www.openarchives.org/OAI/openarchivesprotocol.html
 */

require_once '../vendor/composer/vendor/autoload.php';

ilContext::init(ilContext::CONTEXT_ICAL);
ilInitialisation::initILIAS();

$handler = new Handler();
$handler->sendResponseToRequest();
