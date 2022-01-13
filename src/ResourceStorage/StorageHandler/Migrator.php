<?php declare(strict_types=1);

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
    /**
     * @var StorageHandlerFactory
     */
    private $handler_factory;
    /**
     * @var \ilDBInterface
     */
    private $database;
    /**
     * @var string
     */
    protected $filesystem_base_path;

    protected $clean_up = true;
    /**
     * @var ResourceBuilder
     */
    protected $resource_builder;

    /**
     * Migrator constructor.
     * @param StorageHandlerFactory $handler_factory
     * @param \ilDBInterface        $database
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

    public function migrate(StorableResource $resource, string $to_handler_id) : bool
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

        if (!is_dir(dirname($destination_path))) {
            if (!mkdir(dirname($destination_path), 0777, true)) {
                return false;
            }
        }
        if (rename($existing_path, $destination_path)) {
            $r = $this->database->manipulateF(
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

    public function removeEmptySubFolders($path) : bool
    {
        $empty = true;
        foreach (glob($path . DIRECTORY_SEPARATOR . "*") as $file) {
            $empty &= is_dir($file) && $this->removeEmptySubFolders($file);
        }
        return $empty && rmdir($path);
    }
}
