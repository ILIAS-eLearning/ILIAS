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

use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicLegacyHandler as ilBasicLegacyExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\DI\Container;
use ILIAS\Data\ObjectId;

class ilDataCollectionExportOptionsXLSX extends ilBasicLegacyExportOption
{
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    public function init(Container $DIC): void
    {
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        parent::init($DIC);
    }

    public function getExportType(): string
    {
        return 'xlsx';
    }

    public function getExportOptionId(): string
    {
        return 'dcl_exp_option_xlsx';
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ['dcl'];
    }

    public function getLabel(): string
    {
        return $this->lng->txt('dcl_xls_async_export');
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
        $file_infos = $this->getExportFiles($dir);
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

    public function onExportOptionSelected(
        ilExportHandlerConsumerContextInterface $context
    ): void {
        if (!$this->checkForExportableFields($context)) {
            return;
        }
        $this->ctrl->redirectByClass(ilObjDataCollectionGUI::class, "handleExportAsync");
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
            $exp_dir = $export_dir . "/" . substr($file[1], 0, strlen($file[1]) - 5);
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
                    substr($entry, -5) === "." . $this->getExportType()
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

    protected function checkForExportableFields(
        ilExportHandlerConsumerContextInterface $context
    ): bool {
        $obj = $context->exportObject();

        foreach ($obj->getTables() as $tbl) {
            /** @var $tbl ilDclTable */
            foreach ($tbl->getFields() as $field) {
                if ($field->getExportable()) {
                    return true;
                }
            }
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('dcl_no_export_data_available'), true);
        $this->ctrl->redirect($context->exportGUIObject(), "listExportFiles");

        return false;
    }
}
