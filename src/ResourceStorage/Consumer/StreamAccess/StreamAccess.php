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

namespace ILIAS\ResourceStorage\Consumer\StreamAccess;

use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class StreamAccess
{
    public const PHP_MEMORY = 'php://memory';
    /**
     * @readonly
     */
    private TokenFactory $factory;
    private StorageHandlerFactory $storage_handler_factory;

    public function __construct(
        string $storage_base_path,
        StorageHandlerFactory $storage_handler_factory
    ) {
        $this->storage_handler_factory = $storage_handler_factory;
        $this->factory = new TokenFactory($storage_base_path);
    }

    public function populateRevision(Revision $revision): Revision
    {
        $stream = $this->storage_handler_factory->getHandlerForRevision($revision)->getStream($revision);
        $token = $this->factory->lease($stream);

        return $revision->withToken($token);
    }
}
