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

use ILIAS\Data\ObjectId;
use ILIAS\Data\ReferenceId;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicLegacyHandler as ilBasicLegacyExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\DI\Container;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;

class ilTestExportOptionCSV extends ilBasicLegacyExportOption
{
    protected ilLanguage $lng;

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
        parent::init($DIC);
    }

    public function getExportType(): string
    {
        return 'Results';
    }

    public function getExportOptionId(): string
    {
        return 'test_exp_option_csv';
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ['tst'];
    }

    public function getLabel(): string
    {
        return $this->lng->txt('ass_create_export_test_results');
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        $object_id = new ObjectId($context->exportObject()->getId());
        foreach ($file_identifiers as $file_identifier) {
            $file = explode(":", $file_identifier->getIdentifier());
            $file[1] = basename($file[1]);
            $export_dir = $this->getDirectory($object_id, $context->exportObject()->getType());
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
        $object_id = new ObjectId($context->exportObject()->getId());
        foreach ($file_identifiers as $file_identifier) {
            $file = explode(":", trim($file_identifier->getIdentifier()));
            $export_dir = $this->getDirectory($object_id, $context->exportObject()->getType());
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
        $object_id = $reference_id->toObjectId();
        $type = ilObject::_lookupType($object_id->toInt());
        $file = explode(":", trim($file_identifier->getIdentifier()));
        $export_dir = $this->getDirectory($object_id, $type);
        $file[1] = basename($file[1]);
        ilFileDelivery::deliverFileLegacy(
            $export_dir . "/" . $file[1],
            $file[1]
        );
    }

    public function getFiles(
        ilExportHandlerConsumerContextInterface $context
    ): ilExportHandlerFileInfoCollectionInterface {
        $collection_builder = $context->fileCollectionBuilder();
        $object_id = new ObjectId($context->exportObject()->getId());
        $dir = $this->getDirectory($object_id, $context->exportObject()->getType());
        $file_infos = $this->getExportFiles($dir);
        foreach ($file_infos as $file_name => $file_info) {
            $collection_builder = $collection_builder->withSPLFileInfo(
                new SplFileInfo($dir . DIRECTORY_SEPARATOR . $file_info["file"]),
                $object_id,
                $this
            );
        }
        return $collection_builder->collection();
    }

    public function onExportOptionSelected(
        ilExportHandlerConsumerContextInterface $context
    ): void {
        $context->exportGUIObject()->createTestResultsExport();
    }

    protected function getExportFiles(
        string $directory
    ): array {
        $file = [];
        try {
            $h_dir = dir($directory);
            while ($entry = $h_dir->read()) {
                if (
                    $entry !== "." &&
                    $entry !== ".." &&
                    (
                        substr($entry, -4) === ".csv" or
                        substr($entry, -5) === ".xlsx"
                    )
                ) {
                    $ts = substr($entry, 0, strpos($entry, "__"));
                    $file[$entry . $this->getExportType()] = [
                        "type" => $this->getExportType(),
                        "file" => $entry,
                        "size" => (int) filesize($directory . "/" . $entry),
                        "timestamp" => (int) $ts
                    ];
                }
            }
        } catch (Exception $e) {

        }
        return $file;
    }

    protected function getDirectory(
        ObjectId $object_id,
        string $export_object_type
    ): string {
        $dir = ilExport::_getExportDirectory(
            $object_id->toInt(),
            "",
            $export_object_type
        );
        $dir = substr($dir, 0, strlen($dir) - 1);
        return $dir;
    }
}
