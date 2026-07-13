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

namespace ILIAS\Init;

use Throwable;
use ILIAS\HTTP\StatusCode;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Init\ErrorHandling\Http\ErrorPageResponder;
use ILIAS\Init\ErrorHandling\Http\PlainTextFallbackResponder;

try {
    require_once '../vendor/composer/vendor/autoload.php';

    require_once __DIR__ . '/../artifacts/bootstrap_default.php';
    entry_point('ILIAS Legacy Initialisation Adapter');

    /** @var \ILIAS\DI\Container $DIC */
    global $DIC;

    \ilSession::clear('referer');
    \ilSession::clear('message');

    $DIC->language()->loadLanguageModule('error');

    $message = \ilSession::get('failure') ?? $DIC->language()->txt('http_500_internal_server_error');
    \ilSession::clear('failure');

    $df = new DataFactory();
    $back_target = $df->link(
        $DIC->language()->txt('error_back_to_repository'),
        $df->uri(ILIAS_HTTP_PATH . '/ilias.php?baseClass=ilRepositoryGUI')
    );

    new ErrorPageResponder(
        $DIC->globalScreen(),
        $DIC->language(),
        $DIC->ui(),
        $DIC->http()
    )->respond(
        $message,
        StatusCode::HTTP_INTERNAL_SERVER_ERROR,
        $back_target
    );
} catch (Throwable $e) {
    new PlainTextFallbackResponder()->respond($e);
}
