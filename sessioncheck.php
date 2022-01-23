<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;

if (!file_exists(getcwd() . '/ilias.ini.php')) {
    exit();
}

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SESSION_REMINDER);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

/** @var \ILIAS\DI\Container $DIC */
$DIC->http()->saveResponse(
    $DIC->http()->response()
        ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
        ->withBody(Streams::ofString(
            (new ilSessionReminderCheck())->getJsonResponse(
                ilUtil::stripSlashes(
                    $DIC->http()->wrapper()->post()->retrieve('hash', $DIC->refinery()->kindlyTo()->string())
                )
            )
        ))
);
$DIC->http()->sendResponse();
$DIC->http()->close();
