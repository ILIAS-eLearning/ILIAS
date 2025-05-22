<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\MetaData\OERExposer\OAIPMH\Handler;

/*
 * Handles OAI-PMH request according to https://www.openarchives.org/OAI/openarchivesprotocol.html
 */

require_once '../vendor/composer/vendor/autoload.php';

ilContext::init(ilContext::CONTEXT_ICAL);
ilInitialisation::initILIAS();

$handler = new Handler();
$handler->sendResponseToRequest();
