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

namespace ILIAS\Export\ExportHandler\Consumer\File;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\HandlerInterface as ilExportHandlerConsumerExportOptionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\CollectionBuilderInterface as ilExportHandlerConsumerFileCollectionBuilderInterface;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\File\FactoryInterface as ilExportHandlerFileInfoFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\File\HandlerInterface as ilExportHandlerFileInfoInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\HandlerInterface as ilExportHandlerPublicAccessInterface;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use SplFileInfo;

class CollectionBuilder implements ilExportHandlerConsumerFileCollectionBuilderInterface
{
    protected ilExportHandlerFileInfoFactoryInterface $file_info_factory;
    protected ilExportHandlerPublicAccessInterface $public_access;
    protected ilExportHandlerFileInfoCollectionInterface $collection;

    public function __construct(
        ilExportHandlerFileInfoFactoryInterface $file_info_factory,
        ilExportHandlerPublicAccessInterface $public_access
    ) {
        $this->file_info_factory = $file_info_factory;
        $this->public_access = $public_access;
        $this->collection = $file_info_factory->collection();
    }

    public function withSPLFileInfo(
        SplFileInfo $spl_file_info,
        ObjectId $object_id,
        ilExportHandlerConsumerExportOptionInterface $export_option
    ): ilExportHandlerConsumerFileCollectionBuilderInterface {
        $file_info = $this->file_info_factory->handler()
            ->withSplFileInfo($spl_file_info)
            ->withType($export_option->getExportType())
            ->withPublicAccessPossible($export_option->isPublicAccessPossible());
        $file_info = $file_info->withPublicAccessEnabled(
            $export_option->isPublicAccessPossible() and
            $this->public_access->hasPublicAccessFile($object_id) and
            $this->public_access->getPublicAccessFileExportOptionId($object_id) === $export_option->getExportOptionId() and
            $this->public_access->getPublicAccessFileIdentifier($object_id) === $file_info->getFileIdentifier()
        );
        $clone = clone $this;
        $clone->collection = $clone->collection->withFileInfo($file_info);
        return $clone;
    }

    public function withResourceIdentifier(
        ResourceIdentification $resource_id,
        ObjectId $object_id,
        ilExportHandlerConsumerExportOptionInterface $export_option
    ): ilExportHandlerConsumerFileCollectionBuilderInterface {
        $file_info = $this->file_info_factory->handler()
            ->withResourceId($resource_id)
            ->withType($export_option->getExportType())
            ->withPublicAccessPossible($export_option->isPublicAccessPossible());
        $file_info = $file_info->withPublicAccessEnabled(
            $export_option->isPublicAccessPossible() and
            $this->public_access->hasPublicAccessFile($object_id) and
            $this->public_access->getPublicAccessFileExportOptionId($object_id) === $export_option->getExportOptionId() and
            $this->public_access->getPublicAccessFileIdentifier($object_id) === $file_info->getFileIdentifier()
        );
        $clone = clone $this;
        $clone->collection = $clone->collection->withFileInfo($file_info);
        return $clone;
    }

    public function withFileInfo(
        ilExportHandlerFileInfoInterface $file_info
    ): ilExportHandlerConsumerFileCollectionBuilderInterface {
        $clone = clone $this;
        $clone->collection = $clone->collection->withFileInfo($file_info);
        return $clone;
    }

    public function collection(): ilExportHandlerFileInfoCollectionInterface
    {
        return $this->collection;
    }
}
