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
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicLegacyHandler as ilLegacyExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\DI\Container;

class ilHTLMExportOptionHTML extends ilLegacyExportOption
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;

    public function init(Container $DIC): void
    {
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        parent::init($DIC);
    }

    public function getExportType(): string
    {
        return "html";
    }

    public function getExportOptionId(): string
    {
        return "htlm_exp_option_html";
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ["htlm"];
    }

    public function getLabel(): string
    {
        $this->lng->loadLanguageModule('exp');
        return $this->lng->txt("exp_html");
    }

    public function onExportOptionSelected(
        ilExportHandlerConsumerContextInterface $context
    ): void {
        $object = $context->exportObject();

        ilExport::_createExportDirectory(
            $object->getId(),
            "html",
            $object->getType()
        );
        $export_dir = ilExport::_getExportDirectory(
            $object->getId(),
            "html",
            $object->getType()
        );

        $subdir = $object->getType() . "_" . $object->getId();

        $target_dir = $export_dir . "/" . $subdir;

        ilFileUtils::delDir($target_dir);
        ilFileUtils::makeDir($target_dir);

        $source_dir = $object->getDataDirectory();

        ilFileUtils::rCopy($source_dir, $target_dir);

        // zip it all
        $date = time();
        $zip_file = $export_dir . "/" . $date . "__" . IL_INST_ID . "__" .
            $object->getType() . "_" . $object->getId() . ".zip";
        ilFileUtils::zip($target_dir, $zip_file);

        ilFileUtils::delDir($target_dir);

        $this->ctrl->redirect($context->exportGUIObject(), $context->exportGUIObject()::CMD_LIST_EXPORT_FILES);
    }
}
