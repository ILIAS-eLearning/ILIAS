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

namespace ILIAS\WebDAV\Log;

use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Exception\Locked;
use Sabre\DAV\Exception\ConflictingLock;
use Sabre\DAV\ServerPlugin;
use Sabre\DAV\Server;
use Override;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Log extends ServerPlugin
{
    public function __construct(
        private bool $enable_debugging,
    ) {
    }

    private ?\ilLogger $logger = null;
    private const string EVENT_TYPE_EXCEPTION = 'exception';
    private array $ignore_exceptions = [
        NotFound::class,
        NotAuthenticated::class,
        Locked::class,
        ConflictingLock::class,
    ];

    #[Override]
    public function initialize(Server $server): void
    {
        $server->on(self::EVENT_TYPE_EXCEPTION, fn() => $this->handleException(func_get_arg(0)));

        // TODO: remove service locator usage
        global $DIC;
        $this->logger ??= $DIC->logger()->webdav();

        if ($this->enable_debugging) {
            $this->ignore_exceptions = [];
        }
    }

    private function handleException(\Throwable $e): void
    {
        foreach ($this->ignore_exceptions as $ignore_exception) {
            if ($e instanceof $ignore_exception) {
                return;
            }
        }

        $called_by = $e->getTrace()[0] ?? null;
        if ($called_by && isset($called_by['class'], $called_by['function'])) {
            $this->logger->write(
                'WEBDAV: Exception in ' . $called_by['class'] . '::' . $called_by['function'] . ' - ' . $e->getMessage(
                ),
            );
        } else {
            $this->logger->write(
                'WEBDAV: Uncaught exception - ' . $e->getMessage(),
            );
        }
    }

}
