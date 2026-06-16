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

class ilLearningModuleExportOptionHTML extends \ILIAS\Export\HTML\ExportOptionBase
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected LOMServices $lom_services;

    public function init(Container $DIC): void
    {
        parent::init($DIC);
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
        return $this->lng->txt("exp_format_dropdown-html");
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
        return array_merge($default_dirs, array_map(function ($la) {
            return "_html_" . $la;
        }, $langs));
    }
}
