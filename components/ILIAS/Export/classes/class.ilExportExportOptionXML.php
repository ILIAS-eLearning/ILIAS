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
use ILIAS\Export\ExportHandler\I\Table\RowId\HandlerInterface as ilExportHandlerTableRowIdInterface;
use ILIAS\StaticURL\Context as ilStaticURLContext;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicHandler as ilBasicExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\Factory as ilExportHandlerFactory;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Table\RowId\CollectionInterface as ilExportHandlerTableRowIdCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;

class ilExportExportOptionXML extends ilBasicExportOption
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;

    public function init(Container $DIC): void
    {
        $this->export_handler = new ilExportHandlerFactory();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
    }

    public function getExportType(): string
    {
        return "xml";
    }

    public function getExportOptionId(): string
    {
        return "expxml";
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        # The standard xml export is a special case.
        # It is by default always enabled, independend of the object types returned.
        # Therefore no object types are returned.
        # The only exception are the special cases: Test, TestQuestionPool
        return [];
    }

    public function isPublicAccessPossible(): bool
    {
        return true;
    }

    public function getLabel(): string
    {
        return $this->lng->txt("exp_create_file") . " (xml)";
    }

    public function onExportOptionSelected(
        ilExportHandlerConsumerContextInterface $context
    ): void {
        $this->ctrl->redirect($context->exportGUIObject(), $context->exportGUIObject()::CMD_EXPORT_XML);
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        $object_id = new ObjectId($context->exportObject()->getId());
        $keys = $this->export_handler->repository()->key()->collection();
        foreach ($file_identifiers as $file_identifier) {
            $keys = $keys->withElement($this->export_handler->repository()->key()->handler()
                ->withObjectId($object_id)
                ->withResourceIdSerialized($file_identifier->getIdentifier()));
        }
        $this->export_handler->repository()->handler()->deleteElements(
            $keys,
            $this->export_handler->repository()->stakeholder()->handler()->withOwnerId($this->user->getId())
        );
    }

    public function onDownloadFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        $object_id = new ObjectId($context->exportObject()->getId());
        $keys = $this->export_handler->repository()->key()->collection();
        foreach ($file_identifiers as $file_identifier) {
            $keys = $keys->withElement($this->export_handler->repository()->key()->handler()
                ->withObjectId($object_id)
                ->withResourceIdSerialized($file_identifier->getIdentifier()));
        }
        $elements = $this->export_handler->repository()->handler()->getElements($keys);
        foreach ($elements as $element) {
            $element->getIRSS()->download();
        }
    }

    public function onDownloadWithLink(
        ReferenceId $reference_id,
        ilExportHandlerConsumerFileIdentifierInterface $file_identifier
    ): void {
        $object_id = $reference_id->toObjectId();
        $keys = $this->export_handler->repository()->key()->collection()
            ->withElement($this->export_handler->repository()->key()->handler()
                ->withObjectId($object_id)
                ->withResourceIdSerialized($file_identifier->getIdentifier()));
        $elements = $this->export_handler->repository()->handler()->getElements($keys);
        foreach ($elements as $element) {
            $element->getIRSS()->download();
        }
    }

    public function getFiles(
        ilExportHandlerConsumerContextInterface $context
    ): ilExportHandlerFileInfoCollectionInterface {
        $object_id = new ObjectId($context->exportObject()->getId());
        return $this->buildElements($context, $object_id, [], true);
    }

    public function getFileSelection(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): ilExportHandlerFileInfoCollectionInterface {
        $object_id = new ObjectId($context->exportObject()->getId());
        return $this->buildElements($context, $object_id, $file_identifiers->toStringArray());
    }

    protected function buildElements(
        ilExportHandlerConsumerContextInterface $context,
        ObjectId $object_id,
        array $file_identifiers,
        bool $all_elements = false
    ): ilExportHandlerFileInfoCollectionInterface {
        $keys = $this->export_handler->repository()->key()->collection();
        if ($all_elements) {
            $keys = $keys->withElement($this->export_handler->repository()->key()->handler()->withObjectId($object_id));
        }
        if (!$all_elements) {
            foreach ($file_identifiers as $id) {
                $keys = $keys->withElement($this->export_handler->repository()->key()->handler()
                    ->withObjectId($object_id)
                    ->withResourceIdSerialized($id));
            }
        }
        $elements = $this->export_handler->repository()->handler()->getElements($keys);
        $object_id = new ObjectId($context->exportObject()->getId());
        $collection_builder = $context->fileCollectionBuilder();
        foreach ($elements as $element) {
            $collection_builder = $collection_builder->withResourceIdentifier(
                $element->getIRSSInfo()->getResourceId(),
                $object_id,
                $this
            );
        }
        return $collection_builder->collection();
    }
}
