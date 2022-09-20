<?php

declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\ResourceStorage\StorageHandler;

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;

/**
 * Class Migrator
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class Migrator
{
    private \ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory $handler_factory;
    private \ilDBInterface $database;
    protected string $filesystem_base_path;

    protected bool $clean_up = true;
    protected \ILIAS\ResourceStorage\Resource\ResourceBuilder $resource_builder;

    /**
     * Migrator constructor.
     */
    public function __construct(
        StorageHandlerFactory $handler_factory,
        ResourceBuilder $resource_builder,
        \ilDBInterface $database,
        string $filesystem_base_path
    ) {
        $this->handler_factory = $handler_factory;
        $this->resource_builder = $resource_builder;
        $this->database = $database;
        $this->filesystem_base_path = $filesystem_base_path;
    }

    public function migrate(StorableResource $resource, string $to_handler_id): bool
    {
        $existing_handler = $this->handler_factory->getHandlerForResource($resource);
        $existing_path = $this->filesystem_base_path . '/' . $existing_handler->getFullContainerPath($resource->getIdentification());

        $new_handler = $this->handler_factory->getHandlerForStorageId($to_handler_id);
        $destination_path = $this->filesystem_base_path . '/' . $new_handler->getFullContainerPath($resource->getIdentification());

        if (!file_exists($existing_path)) {
            // File is not existing, we MUST delete the resource
            $this->resource_builder->remove($resource);
            return false;
        }

        if (!is_dir(dirname($destination_path)) && !mkdir(dirname($destination_path), 0777, true)) {
            return false;
        }
        if (rename($existing_path, $destination_path)) {
            $this->database->manipulateF(
                "UPDATE il_resource SET storage_id = %s WHERE identification = %s LIMIT 1",
                ['text', 'text'],
                [$to_handler_id, $resource->getIdentification()->serialize()]
            );

            // remove old
            if ($this->clean_up) {
                $existing_handler->cleanUpContainer($resource);
            }

            return true;
        }
        return false;
    }

    public function removeEmptySubFolders(string $path): bool
    {
        $empty = true;
        foreach (glob($path . DIRECTORY_SEPARATOR . "*") as $file) {
            $empty &= is_dir($file) && $this->removeEmptySubFolders($file);
        }
        return $empty && rmdir($path);
    }
}
