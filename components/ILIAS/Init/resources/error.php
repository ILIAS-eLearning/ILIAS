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

try {
    require_once '../vendor/composer/vendor/autoload.php';

    require_once __DIR__ . '/../artifacts/bootstrap_default.php';
    entry_point('ILIAS Legacy Initialisation Adapter');

    $DIC->globalScreen()->tool()->context()->claim()->external();

    $lng->loadLanguageModule('error');
    $txt = $lng->txt('error_back_to_repository');

    $local_tpl = new \ilGlobalTemplate('tpl.error.html', true, true);
    $local_tpl->setCurrentBlock('ErrorLink');
    $local_tpl->setVariable('TXT_LINK', $txt);
    $local_tpl->setVariable('LINK', \ilUtil::secureUrl(ILIAS_HTTP_PATH . '/ilias.php?baseClass=ilRepositoryGUI'));
    $local_tpl->parseCurrentBlock();

    \ilSession::clear('referer');
    \ilSession::clear('message');

    $DIC->http()->saveResponse(
        $DIC->http()
            ->response()
            ->withStatus(500)
            ->withHeader(\ILIAS\HTTP\Response\ResponseHeader::CONTENT_TYPE, 'text/html')
    );

    $tpl->setContent($local_tpl->get());
    $tpl->printToStdout();

    $DIC->http()->close();
} catch (\Throwable $e) {
    if (\defined('DEVMODE') && DEVMODE) {
        throw $e;
    }

    /*
     * Since we are already in the `error.php` and an unexpected error occurred, we should not rely on the $DIC or any
     * other components here and use "Vanilla PHP" instead to handle the error.
     */
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
    }

    $incident_id = session_id() . '_' . (new \Random\Randomizer())->getInt(1, 9999);
    $timestamp = (new \DateTimeImmutable())->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');

    echo "Internal Server Error\n";
    echo "Incident: $incident_id\n";
    echo "Timestamp: $timestamp\n";
    if ($e instanceof \PDOException) {
        echo "Message: A database error occurred. Please contact the system administrator with the incident id.\n";
    } else {
        echo "Message: {$e->getMessage()}\n";
    }

    error_log(\sprintf(
        "[%s] INCIDENT %s — Uncaught %s: %s in %s:%d\nStack trace:\n%s\n",
        $timestamp,
        $incident_id,
        \get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));

    exit(1);
}
