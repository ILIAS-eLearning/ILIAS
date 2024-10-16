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

namespace ILIAS\Export\ExportHandler\Consumer\ExportOption;

use ilCtrl;
use ilExport;
use ilExportFileInfo;
use ilFileDelivery;
use ilFileUtils;
use ILIAS\Data\ObjectId;
use ILIAS\Data\ReferenceId;
use ILIAS\DI\Container;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicHandler as ilExportHandlerConsumerBasicExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ilObject;
use SplFileInfo;

abstract class BasicLegacyHandler extends ilExportHandlerConsumerBasicExportOption
{
    protected ilCtrl $ctrl;

    public function init(Container $DIC): void
    {
        $this->ctrl = $DIC->ctrl();
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        foreach ($file_identifiers as $file_identifier) {
            $file = explode(":", $file_identifier->getIdentifier());
            $file[1] = basename($file[1]);
            $export_dir = ilExport::_getExportDirectory(
                $context->exportObject()->getId(),
                str_replace("..", "", $file[0]),
                $context->exportObject()->getType()
            );
            $exp_file = $export_dir . "/" . str_replace("..", "", $file[1]);
            $exp_dir = $export_dir . "/" . substr($file[1], 0, strlen($file[1]) - 4);
            if (is_file($exp_file)) {
                unlink($exp_file);
            }
            if (
                is_dir($exp_dir) and
                count(scandir($exp_dir)) === 2
            ) {
                ilFileUtils::delDir($exp_dir);
            }
        }
    }

    public function onDownloadFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        foreach ($file_identifiers as $file_identifier) {
            $file = explode(":", trim($file_identifier->getIdentifier()));
            $export_dir = ilExport::_getExportDirectory(
                $context->exportObject()->getId(),
                str_replace("..", "", $file[0]),
                $context->exportObject()->getType()
            );
            $file[1] = basename($file[1]);
            ilFileDelivery::deliverFileLegacy(
                $export_dir . "/" . $file[1],
                $file[1]
            );
        }
    }

    public function onDownloadWithLink(
        ReferenceId $reference_id,
        ilExportHandlerConsumerFileIdentifierInterface $file_identifier
    ): void {
        $object_id = $reference_id->toObjectId()->toInt();
        $type = ilObject::_lookupType($object_id);
        $file = explode(":", trim($file_identifier->getIdentifier()));
        $export_dir = ilExport::_getExportDirectory(
            $object_id,
            str_replace("..", "", $file[0]),
            $type
        );
        $file[1] = basename($file[1]);
        ilFileDelivery::deliverFileLegacy(
            $export_dir . "/" . $file[1],
            $file[1]
        );
    }

    public function getFileSelection(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): ilExportHandlerFileInfoCollectionInterface {
        $collection_builder = $context->fileCollectionBuilder();
        $file_identifiers_array = $file_identifiers->toStringArray();
        foreach ($this->getFiles($context) as $file) {
            if (in_array($file->getFileIdentifier(), $file_identifiers_array)) {
                $collection_builder = $collection_builder->withFileInfo($file);
            }
        }
        return $collection_builder->collection();
    }

    public function getFiles(
        ilExportHandlerConsumerContextInterface $context
    ): ilExportHandlerFileInfoCollectionInterface {
        $collection_builder = $context->fileCollectionBuilder();
        $dir = ilExport::_getExportDirectory(
            $context->exportObject()->getId(),
            $this->getExportType(),
            $context->exportObject()->getType()
        );
        $file_infos = ilExport::_getExportFiles(
            $context->exportObject()->getId(),
            [$this->getExportType()],
            $context->exportObject()->getType()
        );
        $object_id = new ObjectId($context->exportObject()->getId());
        foreach ($file_infos as $file_name => $file_info) {
            $collection_builder = $collection_builder->withSPLFileInfo(
                new SplFileInfo($dir . DIRECTORY_SEPARATOR . $file_info["file"]),
                $object_id,
                $this
            );
        }
        return $collection_builder->collection();
    }
}
