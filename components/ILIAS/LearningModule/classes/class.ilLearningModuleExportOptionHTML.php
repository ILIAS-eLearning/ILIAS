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
use ILIAS\DI\Container;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicHandler as ilBasicExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\MetaData\Services\ServicesInterface as LOMServices;

class ilLearningModuleExportOptionHTML extends ilBasicExportOption
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected LOMServices $lom_services;

    public function init(Container $DIC): void
    {
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->lom_services = $DIC->learningObjectMetadata();
    }

    public function isPublicAccessPossible(): bool
    {
        return true;
    }

    public function getExportType(): string
    {
        return "html";
    }

    public function getExportOptionId(): string
    {
        return "lm_exp_option_html";
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ["lm"];
    }

    public function getLabel(): string
    {
        $this->lng->loadLanguageModule('exp');
        return $this->lng->txt("exp_html");
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        $this->lng->loadLanguageModule("meta");
        foreach ($file_identifiers as $file_identifier) {
            $file = explode(":", $file_identifier->getIdentifier());
            $file[1] = basename($file[1]);
            $export_dir = ilExport::_getExportDirectory(
                $context->exportObject()->getId(),
                "",
                $context->exportObject()->getType()
            );
            $export_dir = substr($export_dir, 0, strlen($export_dir) - 1);
            foreach ($this->getSubDirs($context->exportObject()->getId()) as $sub_dir) {
                $target_dir = $export_dir . $sub_dir;
                $exp_file = $target_dir . DIRECTORY_SEPARATOR . str_replace("..", "", $file[1]);
                if (is_file($exp_file)) {
                    unlink($exp_file);
                }
                if (is_dir($target_dir) and count(scandir($target_dir)) === 2) {
                    ilFileUtils::delDir($target_dir);
                }
            }
            $lm_dir = substr($export_dir, 0, strlen($export_dir) - strlen("/export_html"));
            if (is_dir($lm_dir) and count(scandir($lm_dir)) === 2) {
                ilFileUtils::delDir($lm_dir);
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
                "",
                $context->exportObject()->getType()
            );
            $export_dir = substr($export_dir, 0, strlen($export_dir) - 1);
            foreach ($this->getSubDirs($context->exportObject()->getId()) as $sub_dir) {
                $target_dir = $export_dir . $sub_dir;
                $file_path = $target_dir . "/" . basename($file[1]);
                if (!is_file($file_path)) {
                    continue;
                }
                ilFileDelivery::deliverFileLegacy(
                    $file_path,
                    $file[1]
                );
                break;
            }
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
            "",
            $type
        );
        $export_dir = substr($export_dir, 0, strlen($export_dir) - 1);
        foreach ($this->getSubDirs($object_id) as $sub_dir) {
            $target_dir = $export_dir . $sub_dir;
            $file_path = $target_dir . "/" . basename($file[1]);
            if (!is_file($file_path)) {
                continue;
            }
            ilFileDelivery::deliverFileLegacy(
                $file_path,
                $file[1]
            );
            break;
        }
    }

    public function getFiles(
        ilExportHandlerConsumerContextInterface $context
    ): \ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface {
        $collection_builder = $context->fileCollectionBuilder();
        $export_dir = ilExport::_getExportDirectory(
            $context->exportObject()->getId(),
            "",
            $context->exportObject()->getType()
        );
        $export_dir = substr($export_dir, 0, strlen($export_dir) - 1);
        $files = [];
        foreach ($this->getSubDirs($context->exportObject()->getId()) as $sub_dir) {
            $target_dir = $export_dir . $sub_dir;
            if (!is_dir($target_dir)) {
                continue;
            }
            $new_files = array_filter(scandir($target_dir), function ($file) { return str_ends_with($file, ".zip"); });
            $files[$target_dir] = $new_files;
        }
        $object_id = new ObjectId($context->exportObject()->getId());
        foreach ($files as $lang_dir => $file_names) {
            foreach ($file_names as $file_name) {
                $collection_builder = $collection_builder->withSPLFileInfo(
                    new SplFileInfo($lang_dir . DIRECTORY_SEPARATOR . $file_name),
                    $object_id,
                    $this
                );
            }
        }
        return $collection_builder->collection();
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

    public function onExportOptionSelected(
        ilExportHandlerConsumerContextInterface $context
    ): void {
        $this->ctrl->redirectByClass(ilObjContentObjectGUI::class, "showExportOptionsHTML");
    }

    protected function getSubDirs(int $object_id): array
    {
        $langs = [];
        foreach ($this->lom_services->dataHelper()->getAllLanguages() as $language) {
            $langs[] = $language->value();
        }
        $default_dirs = ["_html"];
        $default_dirs = array_merge($default_dirs, ["_html_all"]);
        return array_merge($default_dirs, array_map(function ($la) { return "_html_" . $la; }, $langs));
    }
}
