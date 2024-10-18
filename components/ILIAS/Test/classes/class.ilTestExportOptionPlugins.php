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

class ilTestExportOptionPlugins extends ilBasicLegacyExportOption
{
    protected ilLanguage $lng;
    protected ILIAS\DI\UIServices $ui;
    protected ilComponentFactory $component_factory;

    public function init(Container $DIC): void
    {
        $this->lng = $DIC->language();
        $this->ui = $DIC->ui();
        $this->component_factory = $DIC['component.factory'];
        parent::init($DIC);
    }

    public function getExportType(): string
    {
        return "Plugin";
    }

    public function getExportOptionId(): string
    {
        return "tst_exp_option_plugin";
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ['tst'];
    }

    public function getLabel(): string
    {
        return $this->lng->txt("obj_cmps");
    }

    public function isObjectSupported(ObjectId $object_id): bool
    {
        $plugin_count = 0;
        foreach ($this->component_factory->getActivePluginsInSlot('texp') as $plugin) {
            $plugin_count++;
        }
        return $plugin_count > 0;
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        $export_dir = $this->getExportDirectory(
            $context->exportObject()->getId(),
            $context->exportObject()->getType()
        );
        foreach ($file_identifiers as $file_identifier) {
            $file = explode(":", $file_identifier->getIdentifier());
            $file[1] = basename($file[1]);
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
        $export_dir = $this->getExportDirectory(
            $context->exportObject()->getId(),
            $context->exportObject()->getType()
        );
        foreach ($file_identifiers as $file_identifier) {
            $file = explode(":", trim($file_identifier->getIdentifier()));
            $file[1] = basename($file[1]);
            if (!file_exists($export_dir . "/" . $file[1])) {
                continue;
            }
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
        $export_dir = $this->getExportDirectory(
            $object_id,
            $type
        );
        $file[1] = basename($file[1]);
        if (!file_exists($export_dir . "/" . $file[1])) {
            return;
        }
        ilFileDelivery::deliverFileLegacy(
            $export_dir . "/" . $file[1],
            $file[1]
        );
    }

    public function getFiles(
        ilExportHandlerConsumerContextInterface $context
    ): ilExportHandlerFileInfoCollectionInterface {
        $collection_builder = $context->fileCollectionBuilder();
        $dir = $this->getExportDirectory(
            $context->exportObject()->getId(),
            $context->exportObject()->getType()
        );
        foreach ($this->component_factory->getActivePluginsInSlot('texp') as $plugin) {
            /** @var ilTestExportPlugin $plugin */
            $file_infos = $this->getFileInfoForPlugin(
                $plugin,
                $dir
            );
            $object_id = new ObjectId($context->exportObject()->getId());
            foreach ($file_infos as $file_name => $file_info) {
                $collection_builder = $collection_builder->withSPLFileInfo(
                    new SplFileInfo($dir . DIRECTORY_SEPARATOR . $file_info["file"]),
                    $object_id,
                    $this
                );
            }
        }
        return $collection_builder->collection();
    }

    public function onExportOptionSelected(
        ilExportHandlerConsumerContextInterface $context
    ): void {
        $this->ctrl->redirectByClass(ilTestExportGUI::class, "showExportPluginMenu");
    }

    protected function getExportDirectory(
        int $object_id,
        string $object_type
    ): string {
        $dir = ilExport::_getExportDirectory(
            $object_id,
            "",
            $object_type
        );
        $dir = substr($dir, 0, strlen($dir) - 1);
        return $dir;
    }

    protected function getFileInfoForPlugin(
        ilTestExportPlugin $plugin,
        string $export_dir_path,
    ): array {
        $file_infos = [];
        if (!is_dir($export_dir_path)) {
            return $file_infos;
        }
        foreach (scandir($export_dir_path) as $file) {
            $file_path = $export_dir_path . DIRECTORY_SEPARATOR . $file;
            if (
                in_array($file, ['.', '..']) or
                is_dir($file_path) or
                !str_contains($file, $plugin->getPluginName())
            ) {
                continue;
            }
            $file_infos[$file_path] = [
                "file" => $file,
            ];
        }
        return $file_infos;
    }
}
