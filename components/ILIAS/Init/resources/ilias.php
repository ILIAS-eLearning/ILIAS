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

declare(strict_types=1);

use ILIAS\HTTP\StatusCode;
use ILIAS\Init\ErrorHandling\Http\ErrorPageResponder;
use ILIAS\Init\ErrorHandling\Http\PlainTextFallbackResponder;
use ILIAS\Init\ErrorHandling\Http\UnableToRenderErrorPageResponderException;

if (!file_exists('../ilias.ini.php')) {
    die('The ILIAS setup is not completed. Please run the setup routine.');
}

require_once '../vendor/composer/vendor/autoload.php';

/** @var \ILIAS\DI\Container $DIC */
global $DIC;

try {
    require_once __DIR__ . '/../artifacts/bootstrap_default.php';
    entry_point('ILIAS Legacy Initialisation Adapter');

    $DIC->ctrl()->callBaseClass();
} catch (ilCtrlException $e) {
    global $DIC;

    if (defined('DEVMODE') && DEVMODE) {
        throw $e;
    }

    if ($DIC->offsetExists('ilLoggerFactory')) {
        $DIC->logger()->root()->error($e->getMessage());
        $DIC->logger()->root()->error($e->getTraceAsString());
    }

    try {
        new ErrorPageResponder($DIC)->respond(
            '',
            StatusCode::HTTP_NOT_FOUND,
            null,
            null,
            ErrorPageResponder::ROUTING_FAILURE_MESSAGE_LANG_KEY
        );
    } catch (UnableToRenderErrorPageResponderException) {
        new PlainTextFallbackResponder()->respond(
            $e,
            StatusCode::HTTP_NOT_FOUND,
            ErrorPageResponder::translatedUserMessageOrNull(
                $DIC,
                ErrorPageResponder::ROUTING_FAILURE_MESSAGE_LANG_KEY
            )
        );
    } catch (Throwable $t) {
        new PlainTextFallbackResponder()->respond(
            $t,
            StatusCode::HTTP_NOT_FOUND,
            ErrorPageResponder::translatedUserMessageOrNull(
                $DIC,
                ErrorPageResponder::ROUTING_FAILURE_MESSAGE_LANG_KEY
            )
        );
    }
}

/** @var \ILIAS\DI\Container $DIC */
global $DIC;

$DIC['ilBench']->save();
$DIC['http']?->close();
