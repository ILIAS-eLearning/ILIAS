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

use ILIAS\Test\TestDIC;
use ILIAS\Test\ExportImport\DBRepository;
use ILIAS\Test\ExportImport\ResultsExportStakeholder;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ObjectId;
use ILIAS\Data\ReferenceId;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicHandler as ilBasicExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\DI\Container;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;

class ilTestExportOptionXMLRES extends ilBasicExportOption
{
    public const OPTIONS_ID = 'test_exp_option_xmlres';
    private ilLanguage $lng;
    private ResourceStorage $irss;
    private DataFactory $data_factory;
    private DBRepository $repository;

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC['lng'];
        $this->irss = $DIC['resource_storage'];
        $this->data_factory = new DataFactory();
        $this->repository = TestDIC::dic()['exportimport.repository'];
    }

    public function getExportType(): string
    {
        return 'ZIP Results';
    }

    public function getExportOptionId(): string
    {
        return self::OPTIONS_ID;
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ['tst'];
    }

    public function getLabel(): string
    {
        $this->lng->loadLanguageModule('exp');
        $this->lng->loadLanguageModule('assessment');
        return  $this->lng->txt("exp_format_dropdown-xml") . " (" . $this->lng->txt('ass_create_export_file_with_results') . ")";
    }

    public function onDeleteFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        $object_id = new ObjectId($context->exportObject()->getId());
        foreach ($file_identifiers as $file_identifier) {
            $rid = $this->irss->manage()->find($file_identifier->getIdentifier());
            $this->repository->delete($rid);
            $this->irss->manage()->remove($rid, new ResultsExportStakeholder());
        }
    }

    public function onDownloadFiles(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): void {
        $object_id = new ObjectId($context->exportObject()->getId());
        foreach ($file_identifiers as $file_identifier) {
            $this->irss->consume()->download(
                $this->irss->manage()->find($file_identifier->getIdentifier())
            )->run();
        }
    }

    public function onDownloadWithLink(
        ReferenceId $reference_id,
        ilExportHandlerConsumerFileIdentifierInterface $file_identifier
    ): void {
        $this->irss->consume()->download($reference_id)->run();
    }

    public function getFiles(
        ilExportHandlerConsumerContextInterface $context
    ): ilExportHandlerFileInfoCollectionInterface {
        return $this->buildElements(
            $context,
            $this->data_factory->objId($context->exportObject()->getId())
        );
    }

    public function getFileSelection(
        ilExportHandlerConsumerContextInterface $context,
        ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
    ): ilExportHandlerFileInfoCollectionInterface {
        return $this->buildElements(
            $context,
            $this->data_factory->objId($context->exportObject()->getId()),
            $file_identifiers->toStringArray()
        );
    }

    public function onExportOptionSelected(
        ilExportHandlerConsumerContextInterface $context
    ): void {
        $context->exportGUIObject()->createTestExportWithResults();
    }

    protected function buildElements(
        ilExportHandlerConsumerContextInterface $context,
        ObjectId $object_id,
        ?array $file_identifiers = null
    ): ilExportHandlerFileInfoCollectionInterface {
        if ($file_identifiers === null) {
            $file_identifiers = array_map(
                static fn(array $v): string => $v['rid'],
                $this->repository->getFor($object_id->toInt())
            );
        }
        $collection_builder = $context->fileCollectionBuilder();
        foreach ($file_identifiers as $file_identifier) {
            $collection_builder = $collection_builder->withResourceIdentifier(
                $this->irss->manage()->find($file_identifier),
                $object_id,
                $this
            );
        }
        return $collection_builder->collection();
    }
}
