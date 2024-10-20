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

use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicHandler as ilBasicExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\DI\Container;
use ILIAS\Data\ObjectId;

class ilForumExportOptionHTML extends ilBasicExportOption
{
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;

    public function init(Container $DIC): void
    {
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }

    public function getExportType(): string
    {
        return 'html';
    }

    public function getExportOptionId(): string
    {
        return 'frm_exp_option_html';
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ['frm'];
    }

    public function getLabel(): string
    {
        $this->lng->loadLanguageModule('exp');
        return $this->lng->txt('exp_html');
    }

    public function onExportOptionSelected(ilExportHandlerConsumerContextInterface $context): void
    {
        $fex_gui = new ilForumExportGUI();
        $fex_gui->exportHTML();
        $this->ctrl->redirectByClass(ilObjForumGUI::class, 'export');
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        \ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface $file_identifiers
    ): void {
        # Direct download on export creation, no local files
    }

    public function onDownloadFiles(
        ilExportHandlerConsumerContextInterface $context,
        \ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface $file_identifiers
    ): void {
        # Direct download on export creation, no local files
    }

    public function onDownloadWithLink(
        \ILIAS\Data\ReferenceId $reference_id,
        \ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface $file_identifier
    ): void {
        # Direct download on export creation, no local files
    }

    public function getFiles(
        ilExportHandlerConsumerContextInterface $context
    ): \ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface {
        # Direct download on export creation, no local files
        return $context->fileCollectionBuilder()->collection();
    }

    public function getFileSelection(
        ilExportHandlerConsumerContextInterface $context,
        \ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface $file_identifiers
    ): \ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface {
        # Direct download on export creation, no local files
        return $context->fileCollectionBuilder()->collection();
    }
}
