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

namespace ILIAS\Export\ExportHandler\Consumer\ExportWriter;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\HandlerInterface as ilExportHandlerConsumerExportWriterInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\HandlerInterface as ilExportHandlerRepositoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\FactoryInterface as ilExportHandlerRepositoryKeyFactoryInterface;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Collection\ResourceCollection;

class Handler implements ilExportHandlerConsumerExportWriterInterface
{
    protected ilExportHandlerRepositoryInterface $repository;
    protected ilExportHandlerRepositoryKeyFactoryInterface $key_factory;
    protected ObjectId $object_id;
    protected string $resource_id_serialized;

    public function __construct(
        ilExportHandlerRepositoryInterface $repository,
        ilExportHandlerRepositoryKeyFactoryInterface $key_factory
    ) {
        $this->repository = $repository;
        $this->key_factory = $key_factory;
    }

    public function withObjectId(
        objectId $objectId
    ): ilExportHandlerConsumerExportWriterInterface {
        $clone = clone $this;
        $clone->object_id = $objectId;
        return $clone;
    }

    public function withResourceIdSerialized(
        string $resource_id_serialized
    ): ilExportHandlerConsumerExportWriterInterface {
        $clone = clone $this;
        $clone->resource_id_serialized = $resource_id_serialized;
        return $clone;
    }

    public function getObjectId(): ObjectId
    {
        return $this->object_id;
    }

    public function getResourceIdSerialized(): string
    {
        return $this->resource_id_serialized;
    }

    public function writeFileByStream(
        FileStream $stream,
        string $path_in_container
    ): void {
        $element = $this->getElement();
        if (is_null($element)) {
            return;
        }
        $element->getIRSS()->write($stream, $path_in_container);
    }

    public function writeFileByFilePath(
        string $path_to_file,
        string $path_in_container
    ): void {
        if ($path_to_file === "") {
            return;
        }
        $this->writeFileByStream(
            Streams::ofResource(fopen($path_to_file, 'r')),
            $path_in_container
        );
    }

    public function writeDirectory(
        string $dir_path,
        string $dir_path_in_container
    ): void {
        if ($dir_path === "") {
            return;
        }
        $files = scandir($dir_path);
        if (!$files) {
            return;
        }
        foreach ($files as $file) {
            $file_path = $dir_path . DIRECTORY_SEPARATOR . $file;
            if (in_array($file, ['.', '..']) || is_dir($file_path)) {
                continue;
            }
            $file_path_in_container = $dir_path_in_container . DIRECTORY_SEPARATOR . $file;
            $this->writeFileByFilePath($file_path, $file_path_in_container);
        }
    }

    public function writeDirectoryRecursive(
        string $dir_path,
        string $dir_path_in_container
    ): void {
        if ($dir_path === "") {
            return;
        }
        $dirs = [[$dir_path, $dir_path_in_container]];
        while (count($dirs) > 0) {
            $cur_path = $dirs[0][0];
            $cur_path_in_container = $dirs[0][1];
            array_shift($dirs);
            $this->writeDirectory($cur_path, $cur_path_in_container);
            foreach (scandir($cur_path) as $file) {
                $path = $cur_path . DIRECTORY_SEPARATOR . $file;
                $path_in_container = $cur_path_in_container . DIRECTORY_SEPARATOR . $file;
                if (in_array($file, ['.', '..']) || !is_dir($path)) {
                    continue;
                }
                $dirs[] = [$path, $path_in_container];
            }
        }
    }

    public function writeFilesByResourceCollectionId(
        string $resource_id_serialized,
        string $path_in_container
    ): void {
        $element = $this->getElement();
        if (is_null($element)) {
            return;
        }
        $this->getElement()->getIRSS()->addResourceCollectionToContainerById($resource_id_serialized, $path_in_container);
    }

    public function writeFilesByResourceCollection(
        ResourceCollection $collection,
        string $path_in_container
    ): void {
        $this->getElement()->getIRSS()->addResourceCollectionToContaierByCollection($collection, $path_in_container);
    }

    public function writeFilesByResourceId(
        string $resource_id_serialized,
        string $path_in_container
    ): void {
        $element = $this->getElement();
        if (is_null($element)) {
            return;
        }
        $this->getElement()->getIRSS()->addResourceToContainer($resource_id_serialized, $path_in_container);
    }

    protected function getElement(): ilExportHandlerRepositoryElementInterface|null
    {
        if (!isset($this->object_id) or !isset($this->resource_id_serialized)) {
            return null;
        }
        $key = $this->key_factory->handler()
            ->withObjectId($this->object_id)
            ->withResourceIdSerialized($this->resource_id_serialized);
        $key_collection = $this->key_factory->collection()
            ->withElement($key);
        $elements = $this->repository->getElements($key_collection);
        $elements->rewind();
        return $elements->count() === 1 ? $elements->current() : null;
    }
}
