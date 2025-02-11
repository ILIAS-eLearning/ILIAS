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
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicLegacyHandler as ilBasicLegacyExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;

/**
 * @ilCtrl_Calls ilSurveyQuestionPoolExportOptionQuestions: ilObjSurveyQuestionPoolGUI
 */
class ilSurveyQuestionPoolExportOptionQuestions extends ilBasicLegacyExportOption
{
    protected ilLanguage $lng;

    public function init(\ILIAS\DI\Container $DIC): void
    {
        parent::init($DIC);
        $this->lng = $DIC->language();
    }

    public function getExportType(): string
    {
        return $this->lng->txt("survey_question_pool_export_legacy_export_type");
    }

    public function getExportOptionId(): string
    {
        return "spl_legacy_xml";
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ["spl"];
    }

    public function getLabel(): string
    {
        return $this->lng->txt("survey_question_pool_export_legacy_export_label");
    }

    public function onExportOptionSelected(ilExportHandlerConsumerContextInterface $context): void
    {
        /** @var ilObjSurveyQuestionPool $qpl */
        $qpl = $context->exportObject();
        $array_str = implode(',', $qpl->getQuestions());
        $this->ctrl->setParameterByClass(ilObjSurveyQuestionPoolGUI::class, "qid", urlencode($array_str));
        $this->ctrl->redirectByClass(ilObjSurveyQuestionPoolGUI::class, "exportQuestionExportTab");
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        foreach ($file_identifiers as $file_identifier) {
            $file = explode(":", $file_identifier->getIdentifier());
            $file[1] = basename($file[1]);
            $export_dir = $this->getDirectory(
                $context->exportObject()->getId(),
                $context->exportObject()->getType()
            );
            $exp_file = $export_dir . "/" . str_replace("..", "", $file[1]);
            if (is_file($exp_file)) {
                unlink($exp_file);
            }
            if (
                is_dir($export_dir) and
                count(scandir($export_dir)) === 2
            ) {
                ilFileUtils::delDir($export_dir);
            }
        }
    }

    public function onDownloadFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        foreach ($file_identifiers as $file_identifier) {
            $file = explode(":", trim($file_identifier->getIdentifier()));
            $export_dir = $this->getDirectory(
                $context->exportObject()->getId(),
                $context->exportObject()->getType()
            );
            $file[1] = basename($file[1]);
            ilFileDelivery::deliverFileLegacy(
                $export_dir . "/" . $file[1],
                $file[1]
            );
        }
    }

    public function getFiles(
        ilExportHandlerConsumerContextInterface $context
    ): ilExportHandlerFileInfoCollectionInterface {
        $collection_builder = $context->fileCollectionBuilder();
        $export_dir = $this->getDirectory(
            $context->exportObject()->getId(),
            $context->exportObject()->getType()
        );
        $file_infos = $this->getExportFiles($export_dir);
        $object_id = $this->data_factory->objId($context->exportObject()->getId());
        foreach ($file_infos as $file_name => $file_info) {
            $collection_builder = $collection_builder->withSPLFileInfo(
                new SplFileInfo($export_dir . DIRECTORY_SEPARATOR . $file_info["file"]),
                $object_id,
                $this
            );
        }
        return $collection_builder->collection();
    }

    protected function getExportFiles(
        string $directory
    ): array {
        $files = [];
        try {
            foreach (scandir($directory) as $file) {
                if (
                    in_array($file, ['.', '..']) ||
                    !str_ends_with($file, ".zip")
                ) {
                    continue;
                }
                $files[$file] = ["file" => $file];
            }
        } catch (Exception $e) {

        }
        return $files;
    }

    protected function getDirectory(
        int $object_id,
        string $export_object_type
    ): string {
        $dir = ilExport::_getExportDirectory(
            $object_id,
            "",
            $export_object_type
        );
        $dir = substr($dir, 0, strlen($dir) - 1);
        return $dir;
    }
}
