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
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Init\ErrorHandling\Http\ErrorPageResponder;
use ILIAS\Init\ErrorHandling\Http\PlainTextFallbackResponder;

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
    if (defined('DEVMODE') && DEVMODE) {
        throw $e;
    }

    $DIC->logger()->root()->error($e->getMessage());
    $DIC->logger()->root()->error($e->getTraceAsString());

    $DIC->language()->loadLanguageModule('error');
    $df = new DataFactory();
    $back_target = $df->link(
        $DIC->language()->txt('error_back_to_repository'),
        $df->uri(ILIAS_HTTP_PATH . '/ilias.php?baseClass=ilRepositoryGUI')
    );

    try {
        new ErrorPageResponder(
            $DIC->globalScreen(),
            $DIC->language(),
            $DIC->ui(),
            $DIC->http()
        )->respond(
            $DIC->language()->txt('http_404_not_found'),
            StatusCode::HTTP_NOT_FOUND,
            $back_target
        );
    } catch (Throwable) {
        new PlainTextFallbackResponder()->respond(
            $e,
            StatusCode::HTTP_NOT_FOUND,
            $DIC->language()->txt('http_404_not_found')
        );
    }
}

$DIC['ilBench']->save();
$DIC['http']?->close();
