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

use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Class Migrator
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 * @internal
 */
class Migrator
{
    protected bool $clean_up = true;
    private StorageHandlerFactory $handler_factory;
    protected ResourceBuilder $resource_builder;
    private \ilDBInterface $database;
    protected string $filesystem_base_path;

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
        $existing_path = $this->filesystem_base_path . '/' . $existing_handler->getFullContainerPath(
            $resource->getIdentification()
        );

        $new_handler = $this->handler_factory->getHandlerForStorageId($to_handler_id);
        $destination_path = $this->filesystem_base_path . '/' . $new_handler->getFullContainerPath(
            $resource->getIdentification()
        );

        if (!file_exists($existing_path)) {
            // File is not existing, we MUST delete the resource
            $this->resource_builder->remove($resource);
            return false;
        }

        if (!is_dir(dirname($destination_path)) && !mkdir(dirname($destination_path), 0777, true)) {
            return false;
        }
        if (file_exists($destination_path)) {
            // target exists, we have to merge the folders.
            $result = $this->mergeDirectories($existing_path, $destination_path);
        } else {
            $result = rename($existing_path, $destination_path);
        }

        if ($result) {
            $this->database->manipulateF(
                "UPDATE il_resource SET storage_id = %s WHERE rid = %s LIMIT 1",
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

    private function mergeDirectories(string $path_to_source_dir, string $path_to_destination_dir): bool
    {
        $dir = opendir($path_to_source_dir);

        while (($name = readdir($dir)) !== false) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            $src_path = $path_to_source_dir . '/' . $name;
            $dest_path = $path_to_destination_dir . '/' . $name;

            if (is_dir($src_path)) {
                // If it's a directory, create destination and then recurse
                if (!file_exists($dest_path)) {
                    mkdir($dest_path, 0777, true);
                }
                $this->mergeDirectories($src_path, $dest_path);
            } elseif (file_exists($dest_path)) {
                unlink($dest_path);
                rename($src_path, $dest_path);
            } elseif (!file_exists($dest_path)) {
                rename($src_path, $dest_path);
            }
        }
        closedir($dir);

        return true;
    }
}
