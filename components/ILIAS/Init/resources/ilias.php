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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\StatusCode;
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
    global $DIC;

    if (defined('DEVMODE') && DEVMODE) {
        throw $e;
    }

    if ($DIC->offsetExists('ilLoggerFactory')) {
        $DIC->logger()->root()->error($e->getMessage());
        $DIC->logger()->root()->error($e->getTraceAsString());
    }

    $repository_href = defined('ILIAS_HTTP_PATH')
        ? ILIAS_HTTP_PATH . '/ilias.php?baseClass=ilRepositoryGUI'
        : '/ilias.php?baseClass=ilRepositoryGUI';

    $public_message = null;
    if ($DIC->offsetExists('lng')) {
        $DIC->language()->loadLanguageModule('error');
        $public_message = $DIC->language()->txt('http_404_not_found');
    }

    $can_html_error_page = $DIC->offsetExists('lng')
        && $DIC->offsetExists('http')
        && $DIC->offsetExists('tpl')
        && $DIC->offsetExists('ui.factory')
        && $DIC->offsetExists('ui.renderer');

    $can_plain_html_error_page = $DIC->offsetExists('lng')
        && $DIC->offsetExists('http')
        && $DIC->offsetExists('tpl')
        && (
            !$DIC->offsetExists('ui.factory')
            || !$DIC->offsetExists('ui.renderer')
        );

    try {
        if ($can_html_error_page || $can_plain_html_error_page) {
            $df = $DIC->offsetExists(\ILIAS\Data\Factory::class)
                ? $DIC[\ILIAS\Data\Factory::class]
                : new DataFactory();
            $back_target = $df->link(
                $DIC->language()->txt('error_back_to_repository'),
                $df->uri($repository_href)
            );
            $shell = $can_html_error_page
                ? $DIC->ui()
                : $DIC['tpl'];
            new ErrorPageResponder(
                $DIC->offsetExists('global_screen') ? $DIC->globalScreen() : null,
                $DIC->language(),
                $DIC->http(),
                $shell
            )->respond(
                $public_message ?? '',
                StatusCode::HTTP_NOT_FOUND,
                $back_target
            );
        }
        new PlainTextFallbackResponder()->respond($e, StatusCode::HTTP_NOT_FOUND, $public_message);
    } catch (Throwable $t) {
        new PlainTextFallbackResponder()->respond(
            $t,
            StatusCode::HTTP_NOT_FOUND,
            $public_message
        );
    }
}

/** @var \ILIAS\DI\Container $DIC */
global $DIC;

$DIC['ilBench']->save();
$DIC['http']?->close();
