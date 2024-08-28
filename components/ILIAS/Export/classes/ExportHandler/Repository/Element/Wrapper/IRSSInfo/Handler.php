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

namespace ILIAS\Export\ExportHandler\Repository\Element\Wrapper\IRSSInfo;

use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSSInfo\HandlerInterface as ilExportHandlerRepositoryElementIRSSInfoWrapperInterface;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;

class Handler implements ilExportHandlerRepositoryElementIRSSInfoWrapperInterface
{
    protected ResourcesStorageService $irss;
    protected string $resource_identification_serialized;

    public function __construct(
        ResourcesStorageService $irss
    ) {
        $this->irss = $irss;
    }

    public function withResourceIdSerialized(string $resource_identification_serialized): ilExportHandlerRepositoryElementIRSSInfoWrapperInterface
    {
        $clone = clone $this;
        $clone->resource_identification_serialized = $resource_identification_serialized;
        return $clone;
    }

    public function getResourceIdSerialized(): string
    {
        return $this->resource_identification_serialized;
    }

    public function getStream(): FileStream
    {
        return $this->irss->consume()->stream($this->getResourceId())
            ->getStream();
    }

    public function getResourceId(): null|ResourceIdentification
    {
        return $this->irss->manageContainer()->find($this->resource_identification_serialized);
    }

    public function getFileName(): string
    {
        return $this->irss->manageContainer()->getResource($this->getResourceId())
            ->getCurrentRevision()->getInformation()->getTitle();
    }

    public function getFileSize(): int
    {
        return $this->irss->manageContainer()->getResource($this->getResourceId())
            ->getCurrentRevision()->getInformation()->getSize();
    }

}
