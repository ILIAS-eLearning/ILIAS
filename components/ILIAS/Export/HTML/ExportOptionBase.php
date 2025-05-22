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

namespace ILIAS\Export\HTML;

use ilCtrl;
use ilExport;
use ilFileDelivery;
use ilFileUtils;
use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\Data\ReferenceId;
use ILIAS\DI\Container;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicHandler as ilExportHandlerConsumerBasicExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ilObject;
use SplFileInfo;

abstract class ExportOptionBase extends ilExportHandlerConsumerBasicExportOption
{
    protected \ilExportHTMLStakeholder $stakeholder;
    protected ExportFileDBRepository $repo;
    protected ilCtrl $ctrl;
    protected ilDataFactory $data_factory;

    public function init(Container $DIC): void
    {
        $this->ctrl = $DIC->ctrl();
        $this->data_factory = new ilDataFactory();
        $this->repo = $DIC->export()->internal()->repo()->html()->exportFile();
        $this->stakeholder = new \ilExportHTMLStakeholder();
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        foreach ($file_identifiers as $file_identifier) {
            $rid = $file_identifier->getIdentifier();
            $this->repo->delete(
                $context->exportObject()->getId(),
                $rid
            );
        }
    }

    public function onDownloadFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        foreach ($file_identifiers as $file_identifier) {
            $rid = $file_identifier->getIdentifier();
            $this->repo->deliverFile($rid);
        }
    }

    public function onDownloadWithLink(
        ReferenceId $reference_id,
        ilExportHandlerConsumerFileIdentifierInterface $file_identifier
    ): void {
        $object_id = $reference_id->toObjectId()->toInt();
        $type = ilObject::_lookupType($object_id);
        $file = explode(":", trim($file_identifier->getIdentifier()));
        var_dump($file_identifier);
        exit;
        // todo: send based on rid
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
        $object_id = $this->data_factory->objId($context->exportObject()->getId());
        foreach ($this->repo->getAllOfObjectId($context->exportObject()->getId()) as $file) {
            $collection_builder = $collection_builder->withResourceIdentifier(
                $this->repo->getResourceIdForIdString($file->getRid()),
                $object_id,
                $this
            );
        }
        return $collection_builder->collection();
    }
}
