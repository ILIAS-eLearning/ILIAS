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

namespace ILIAS\MetaData\OERExposer\OAIPMH;

use ILIAS\Data\URI;

class Handler
{
    protected InitiatorInterface $initiator;
    protected \ilLogger $logger;

    protected readonly URI $base_url;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->meta();
        $this->initiator = new Initiator($DIC);
        $this->base_url = new URI(rtrim(ILIAS_HTTP_PATH, '/') . '/oai.php');
    }

    public function sendResponseToRequest(): void
    {
        if (!$this->initiator->settings()->isOAIPMHActive()) {
            $this->initiator->httpWrapper()->sendResponseAndClose(404);
            return;
        }

        set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): never {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try {
            $response = $this->initiator->requestProcessor()->getResponseToRequest(
                $this->initiator->requestParser()->parseFromHTTP($this->base_url)
            );
        } catch (\Throwable $e) {
            $this->logError($e->getMessage());
            $this->initiator->httpWrapper()->sendResponseAndClose(500, $e->getMessage());
            return;
        } finally {
            restore_error_handler();
        }

        $this->initiator->httpWrapper()->sendResponseAndClose(200, '', $response);
    }

    protected function logError(string $message): void
    {
        $this->logger->error($message);
    }
}
