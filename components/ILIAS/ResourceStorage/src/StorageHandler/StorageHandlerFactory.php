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
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\Data\Meta\Html\OpenGraph\Resource;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class StorageHandlerFactory
 * @author Fabian Schmid <fabian@sr.solutions.ch>
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

    public function getHandlerForRevision(Revision $revision): StorageHandler
    {
        return $this->getHandlerForStorageId($revision->getStorageID());
    }

    public function getHandlerForStorageId(string $storage_id): StorageHandler
    {
        if (isset($this->handlers[$storage_id])) {
            return $this->handlers[$storage_id];
        }

        throw new \LogicException("no StorageHandler for '$storage_id' available");
    }

    public function getPrimary(): ?\ILIAS\ResourceStorage\StorageHandler\StorageHandler
    {
        return $this->primary;
    }

    public function getRidForURI(string $uri): ?ResourceIdentification
    {
        $internal_path = str_replace($this->getBaseDir(), "", $uri);
        $internal_path = ltrim(str_replace(self::BASE_DIRECTORY, "", $internal_path), "/");

        $fs_identifier = explode("/", $internal_path)[0];

        $internal_path = str_replace($fs_identifier . "/", "", $internal_path);

        $handler = $this->getHandlerForStorageId($fs_identifier);
        $path_generator = $handler->getPathGenerator();
        try {
            $rid = $path_generator->getIdentificationFor($internal_path);
        } catch (\Throwable) {
            return null;
        }
        return $rid;
    }
}
