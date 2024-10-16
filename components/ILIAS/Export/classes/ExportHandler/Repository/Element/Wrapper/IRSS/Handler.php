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

namespace ILIAS\Export\ExportHandler\Repository\Element\Wrapper\IRSS;

use ILIAS\components\ResourceStorage\Container\Wrapper\ZipReader;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSS\HandlerInterface as ilExportHandlerRepositoryElementIRSSWrapperInterface;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;

class Handler implements ilExportHandlerRepositoryElementIRSSWrapperInterface
{
    protected ResourcesStorageService $irss;
    protected string $resource_identification_serialized;

    public function __construct(
        ResourcesStorageService $irss
    ) {
        $this->irss = $irss;
    }

    public function withResourceIdSerialized(string $resource_identification_serialized): ilExportHandlerRepositoryElementIRSSWrapperInterface
    {
        $clone = clone $this;
        $clone->resource_identification_serialized = $resource_identification_serialized;
        return $clone;
    }

    public function getResourceIdSerialized(): string
    {
        return $this->resource_identification_serialized;
    }

    public function write(
        FileStream $stream,
        string $path_in_container
    ): bool {
        if (!isset($this->resource_identification_serialized)) {
            return false;
        }
        $success = $this->addStreamToContainer($stream, $path_in_container);
        if ($success) {
            $this->removeTmpFile();
        }
        return $success;
    }

    public function addResourceToContainerByResourceId(
        ResourceIdentification $resource_identification,
        string $path_in_container
    ): void {
        $container_rid = $this->getResourceId();
        if (is_null($container_rid)) {
            return;
        }
        $file_stream = $this->irss->consume()->stream($resource_identification)->getStream();
        $this->irss->manageContainer()->addStreamToContainer(
            $container_rid,
            $file_stream,
            $path_in_container
        );
    }

    public function addResourceToContainer(
        string $resource_id_serialized,
        string $path_in_container
    ): void {
        $resource_rid = $this->irss->manage()->find($resource_id_serialized);
        if (is_null($resource_rid)) {
            return;
        }
        $this->addResourceToContainerByResourceId(
            $resource_rid,
            $path_in_container
        );
    }

    public function addResourceCollectionToContainerById(
        string $resource_id_serialized,
        string $dir_path_in_container
    ): void {
        $collection_rid = $this->irss->collection()->id($resource_id_serialized);
        if (is_null($collection_rid)) {
            return;
        }
        $collection = $this->irss->collection()->get($collection_rid);
        $this->addResourceCollectionToContaierByCollection($collection, $dir_path_in_container);
    }

    public function addResourceCollectionToContaierByCollection(
        ResourceCollection $collection,
        string $dir_path_in_container
    ) {
        foreach ($collection->getResourceIdentifications() as $resource_identification) {
            $resource = $this->irss->manage()->getResource($resource_identification);
            $file_title = $resource->getCurrentRevision()->getInformation()->getTitle();
            $path_in_container = $dir_path_in_container . DIRECTORY_SEPARATOR . $file_title;
            $this->addResourceToContainerByResourceId(
                $resource_identification,
                $path_in_container
            );
        }
    }

    public function writeZip(
        FileStream $zip_stream,
        string $path_in_container
    ): bool {
        $zip_reader = new ZipReader($zip_stream);
        $zip_structure = $zip_reader->getStructure();
        $success = true;
        foreach ($zip_structure as $path_inside_zip => $item) {
            if ($item['is_dir']) {
                continue;
            }
            $stream = $zip_reader->getItem($path_inside_zip, $zip_structure)[0];
            $success = $success and $this->write($stream, $path_in_container . DIRECTORY_SEPARATOR . $path_inside_zip);
        }
        return $success;
    }

    public function writeElement(
        ilExportHandlerRepositoryElementInterface $other,
        string $path_in_container
    ): bool {
        return $this->writeZip($other->getIRSSInfo()->getStream(), $path_in_container);
    }

    public function download(string $zip_file_name = ""): void
    {
        $download = $this->irss->consume()->download($this->getResourceId());
        if ($zip_file_name !== "") {
            $download = $download->overrideFileName($zip_file_name);
        }
        $download->run();
    }

    public function isContainerExport(): bool
    {
        $rid = $this->irss->manageContainer()->find($this->resource_identification_serialized);
        foreach ($this->irss->consume()->containerZIP($rid)->getZIP()->getPaths() as $path) {
            if (str_starts_with($path, "set")) {
                return true;
            }
        }
        return false;
    }

    protected function removeTmpFile(): void
    {
        $this->irss->manageContainer()->removePathInsideContainer(
            $this->getResourceId(),
            self::TMP_FILE_PATH
        );
    }

    protected function addStreamToContainer(
        FileStream $stream,
        string $path_in_container
    ): bool {
        return $this->irss->manageContainer()->addStreamToContainer(
            $this->getResourceId(),
            $stream,
            $path_in_container
        );
    }

    protected function getResourceId(): null|ResourceIdentification
    {
        return $this->irss->manageContainer()->find($this->getResourceIdSerialized());
    }
}
