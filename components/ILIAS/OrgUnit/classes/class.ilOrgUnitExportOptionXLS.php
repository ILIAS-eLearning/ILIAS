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
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicHandler as ilBasicExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\DI\Container;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;

class ilOrgUnitExportOptionXLS extends ilBasicExportOption
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
        return "simple xls";
    }

    public function getExportOptionId(): string
    {
        return "orgu_exp_option_simple_xls";
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ["orgu"];
    }

    public function getLabel(): string
    {
        return $this->lng->txt('simple_xls');
    }

    public function isObjectSupported(ObjectId $object_id): bool
    {
        //Simple XML and Simple XLS Export should only be available in the root orgunit folder as it always exports the whole tree
        return $object_id->toInt() === ilObjOrgUnit::getRootOrgId();
    }

    public function onExportOptionSelected(ilExportHandlerConsumerContextInterface $context): void
    {
        $this->ctrl->redirect($context->exportGUIObject(), "simpleExportExcel");
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        # Direct download on export creation, no local files
    }

    public function onDownloadFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        # Direct download on export creation, no local files
    }

    public function onDownloadWithLink(
        ReferenceId $reference_id,
        ilExportHandlerConsumerFileIdentifierInterface $file_identifier
    ): void {
        # Direct download on export creation, no local files
    }

    public function getFiles(
        ilExportHandlerConsumerContextInterface $context
    ): ilExportHandlerFileInfoCollectionInterface {
        # Direct download on export creation, no local files
        return $context->fileCollectionBuilder()->collection();
    }

    public function getFileSelection(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): ilExportHandlerFileInfoCollectionInterface {
        # Direct download on export creation, no local files
        return $context->fileCollectionBuilder()->collection();
    }
}
