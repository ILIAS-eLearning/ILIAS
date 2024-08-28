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

namespace ILIAS\Export\ExportHandler\Info\File;

use DateTimeImmutable;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\HandlerInterface as ilExportHandlerConsumerExportOptionInterface;
use ILIAS\Export\ExportHandler\I\Info\File\HandlerInterface as ilExportHandlerFileInfoInterface;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;
use SplFileInfo;

class Handler implements ilExportHandlerFileInfoInterface
{
    protected ResourcesStorageService $irss;
    protected StorableResource $resource;
    protected SplFileInfo $splFileInfo;
    protected ilExportHandlerConsumerExportOptionInterface $export_option;
    protected bool $public_access_enabled;
    protected bool $public_access_possible;
    protected string $type;

    public function __construct(
        ResourcesStorageService $irss
    ) {
        $this->irss = $irss;
    }

    public function withPublicAccessPossible(bool $enabled): ilExportHandlerFileInfoInterface
    {
        $clone = clone $this;
        $clone->public_access_possible = $enabled;
        return $clone;
    }

    public function withPublicAccessEnabled(bool $enabled): ilExportHandlerFileInfoInterface
    {
        $clone = clone $this;
        $clone->public_access_enabled = $enabled;
        return $clone;
    }

    public function withType(string $type): ilExportHandlerFileInfoInterface
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function withResourceId(ResourceIdentification $resource_id): ilExportHandlerFileInfoInterface
    {
        $clone = clone $this;
        $clone->resource = $this->irss->manage()->getResource($resource_id);
        return $clone;
    }

    public function withSplFileInfo(SplFileInfo $splFileInfo): ilExportHandlerFileInfoInterface
    {
        $clone = clone $this;
        $clone->splFileInfo = $splFileInfo;
        return $clone;
    }

    public function withExportOption(
        ilExportHandlerConsumerExportOptionInterface $export_option
    ): ilExportHandlerFileInfoInterface {
        $clone = clone $this;
        $clone->export_option = $export_option;
        return $clone;
    }

    public function getExportOption(): ilExportHandlerConsumerExportOptionInterface
    {
        return $this->export_option;
    }

    public function getPublicAccessPossible(): bool
    {
        return $this->public_access_possible;
    }

    public function getPublicAccessEnabled(): bool
    {
        return $this->public_access_enabled;
    }

    public function getFileSize(): int
    {
        $file_size = 0;
        if (isset($this->splFileInfo)) {
            $file_size = $this->splFileInfo->getSize();
        }
        if (isset($this->resource)) {
            $file_size = $this->resource->getCurrentRevision()->getInformation()->getSize();
        }
        return $file_size;
    }

    public function getFileName(): string
    {
        $file_name = "";
        if (isset($this->splFileInfo)) {
            $file_name = $this->splFileInfo->getFilename();
        }
        if (isset($this->resource)) {
            $file_name = $this->resource->getCurrentRevision()->getInformation()->getTitle();
        }
        return $file_name;
    }

    public function getFileType(): string
    {
        $file_name = "";
        if (isset($this->type)) {
            $file_name = $this->type;
        }
        return $file_name;
    }

    public function getDownloadInfo(): string
    {
        $download_info = "";
        if (isset($this->splFileInfo)) {
            $download_info = $this->splFileInfo->getPath();
        }
        if (isset($this->resource)) {
            $download_info = $this->resource->getIdentification()->serialize();
        }
        return $download_info;
    }

    public function getLastChangedTimestamp(): int
    {
        $timestamp = 0;
        if (isset($this->splFileInfo)) {
            $timestamp = $this->splFileInfo->getCTime();
        }
        if (isset($this->resource)) {
            $timestamp = $this->resource->getCurrentRevision()->getInformation()->getCreationDate()->getTimestamp();
        }
        return $timestamp;
    }

    public function getLastChanged(): DateTimeImmutable
    {
        $timestamp = new DateTimeImmutable();
        if (isset($this->splFileInfo)) {
            $timestamp = (new DateTimeImmutable())->setTimestamp($this->splFileInfo->getCTime());
        }
        if (isset($this->resource)) {
            $timestamp = $this->resource->getCurrentRevision()->getInformation()->getCreationDate();
        }
        return $timestamp;
    }

    public function getFileIdentifier(): string
    {
        $file_identifier = "";
        if (isset($this->type) and isset($this->splFileInfo)) {
            $file_identifier = $this->type . ":" . $this->getFileName();
        }
        if (isset($this->resource)) {
            $file_identifier = $this->resource->getIdentification()->serialize();
        }
        return $file_identifier;
    }
}
