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

namespace ILIAS\ResourceStorage\StorageHandler;

use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Class StorageHandlerFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StorageHandlerFactory
{
    public const BASE_DIRECTORY = "storage";
    /**
     * @var StorageHandler[]
     */
    protected array $handlers = [];
    protected ?\ILIAS\ResourceStorage\StorageHandler\StorageHandler $primary = null;
    private string $base_dir;

    /**
     * StorageHandlerFactory constructor.
     * @param StorageHandler[] $handlers
     */
    public function __construct(array $handlers, string $base_dir)
    {
        $this->base_dir = $base_dir;
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getID()] = $handler;
            if ($handler->isPrimary()) {
                if ($this->primary !== null) {
                    throw new \LogicException("Only one primary StorageHandler can exist");
                }
                $this->primary = $handler;
            }
        }
        if ($this->primary === null) {
            throw new \LogicException("One primary StorageHandler must exist");
        }
    }

    public function getBaseDir(): string
    {
        return $this->base_dir;
    }

    public function getHandlerForResource(StorableResource $resource): StorageHandler
    {
        return $this->getHandlerForStorageId($resource->getStorageID());
    }

    public function getHandlerForStorageId(string $storage_id): StorageHandler
    {
        if (isset($this->handlers[$storage_id])) {
            return $this->handlers[$storage_id];
        }

        throw new \LogicException("no other StorageHandler possible at the moment");
    }

    public function getPrimary(): ?\ILIAS\ResourceStorage\StorageHandler\StorageHandler
    {
        return $this->primary;
    }
}
