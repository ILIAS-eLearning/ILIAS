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

namespace ILIAS\MetaData\OERHarvester;

use ILIAS\MetaData\OERHarvester\Results\WrapperInterface as Result;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\HandlerInterface as ObjectHandler;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface as StatusRepository;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RepositoryInterface as ExposedRecordRepository;
use ILIAS\MetaData\Copyright\Search\FactoryInterface as CopyrightSearchFactory;
use ILIAS\MetaData\Repository\RepositoryInterface as LOMRepository;
use ILIAS\MetaData\OERHarvester\XML\WriterInterface as SimpleDCXMLWriter;
use ILIAS\MetaData\OERHarvester\Export\HandlerInterface as ExportHandler;

class Harvester
{
    protected SettingsInterface $settings;
    protected ObjectHandler $object_handler;
    protected ExportHandler $export_handler;
    protected StatusRepository $status_repository;
    protected ExposedRecordRepository $exposed_record_repository;
    protected CopyrightSearchFactory $copyright_search_factory;
    protected LOMRepository $lom_repository;
    protected SimpleDCXMLWriter $xml_writer;
    protected \ilLogger $logger;

    public function __construct(
        SettingsInterface $settings,
        ObjectHandler $object_handler,
        ExportHandler $export_handler,
        StatusRepository $status_repository,
        ExposedRecordRepository $exposed_record_repository,
        CopyrightSearchFactory $copyright_search_factory,
        LOMRepository $lom_repository,
        SimpleDCXMLWriter $xml_writer,
        \ilLogger $logger
    ) {
        $this->settings = $settings;
        $this->object_handler = $object_handler;
        $this->export_handler = $export_handler;
        $this->status_repository = $status_repository;
        $this->exposed_record_repository = $exposed_record_repository;
        $this->copyright_search_factory = $copyright_search_factory;
        $this->lom_repository = $lom_repository;
        $this->xml_writer = $xml_writer;
        $this->logger = $logger;
    }

    public function run(Result $result): Result
    {
        try {
            $messages = [];

            $harvestable_obj_ids = $this->findHarvestableObjectIDs();
            $currently_harvested_obj_ids = iterator_to_array($this->status_repository->getAllHarvestedObjIDs());

            $deletion_count = $this->deleteDeprecatedReferences(
                $harvestable_obj_ids,
                $currently_harvested_obj_ids
            );
            $messages[] = 'Deleted ' . $deletion_count . ' deprecated references.';
            $harvest_count = $this->harvestObjects(
                $harvestable_obj_ids,
                $currently_harvested_obj_ids
            );
            $messages[] = 'Created ' . $harvest_count . ' new references.';
            $exposure_count = $this->updateExposedRecords(
                $harvestable_obj_ids
            );
            $messages[] = 'Created, updated, or deleted ' . $exposure_count . ' exposed records.';

            if ($deletion_count !== 0 || $harvest_count !== 0 || $exposure_count !== 0) {
                $result = $result->withStatus(\ILIAS\Cron\Job\JobResult::STATUS_OK);
            } else {
                $result = $result->withStatus(\ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION);
            }
            return $result->withMessage(implode('<br>', $messages));
        } catch (\Exception $e) {
            return $result->withStatus(\ILIAS\Cron\Job\JobResult::STATUS_FAIL)
                          ->withMessage($e->getMessage());
        }
    }

    protected function findHarvestableObjectIDs(): array
    {
        $searcher = $this->copyright_search_factory->get()
                                                   ->withRestrictionToRepositoryObjects(true);
        foreach ($this->settings->getObjectTypesSelectedForHarvesting() as $type) {
            $searcher = $searcher->withAdditionalTypeFilter($type);
        }
        $search_results = [];
        foreach ($searcher->search(
            $this->lom_repository,
            ...$this->settings->getCopyrightEntryIDsSelectedForHarvesting()
        ) as $ressource_id) {
            $search_results[] = $ressource_id->objID();
        }

        $unblocked = $this->status_repository->filterOutBlockedObjects(...$search_results);
        $results = [];
        foreach ($unblocked as $obj_id) {
            if ($this->object_handler->isObjectDeleted($obj_id)) {
                continue;
            }
            $results[] = $obj_id;
        }
        return $results;
    }

    /**
     * Returns number of deletions.
     * @param int[] $harvestable_obj_ids
     * @param int[] $currently_harvested_obj_ids
     */
    protected function deleteDeprecatedReferences(
        array $harvestable_obj_ids,
        array $currently_harvested_obj_ids
    ): int {
        $count = 0;
        foreach ($currently_harvested_obj_ids as $obj_id) {
            if (in_array($obj_id, $harvestable_obj_ids)) {
                continue;
            }

            $ref_id = $this->status_repository->getHarvestRefID($obj_id);
            $this->logDebug('Deleting deprecated object with ref_id: ' . $ref_id);
            try {
                $this->object_handler->deleteReference($ref_id);
            } catch (\Exception $e) {
                $this->logError(
                    'Error when deleting harvested reference with ref_id ' .
                    $ref_id . ': ' . $e->getMessage()
                );
                continue;
            }
            $this->status_repository->deleteHarvestRefID($obj_id);
            $count++;
        }
        return $count;
    }

    /**
     * Returns number of harvested objects.
     * @param int[] $harvestable_obj_ids
     * @param int[] $currently_harvested_obj_ids
     */
    protected function harvestObjects(
        array $harvestable_obj_ids,
        array $currently_harvested_obj_ids
    ): int {
        $count = 0;

        $target_ref_id = $this->settings->getContainerRefIDForHarvesting();
        if (!$target_ref_id) {
            return 0;
        }

        foreach ($harvestable_obj_ids as $obj_id) {
            if (in_array($obj_id, $currently_harvested_obj_ids)) {
                continue;
            }

            $this->logDebug('Creating new reference for object with obj_id: ' . $obj_id);
            try {
                $new_ref_id = $this->object_handler->referenceObjectInTargetContainer(
                    $obj_id,
                    $target_ref_id
                );
            } catch (\Exception $e) {
                $this->logError(
                    'Error when creating reference for object with obj_id ' .
                    $obj_id . ': ' . $e->getMessage()
                );
                continue;
            }
            $this->status_repository->setHarvestRefID($obj_id, $new_ref_id);

            try {
                if (!$this->export_handler->hasPublicAccessExport($obj_id)) {
                    $this->export_handler->createPublicAccessExport($obj_id);
                }
            } catch (\Exception $e) {
                $this->logError(
                    'Error when creating export for object with obj_id ' .
                    $obj_id . ': ' . $e->getMessage()
                );
            }

            $count++;
        }
        return $count;
    }

    /**
     * Returns number of changed exposed records.
     * @param int[] $harvestable_obj_ids
     */
    protected function updateExposedRecords(
        array $harvestable_obj_ids,
    ): int {
        $count = 0;

        $source_ref_id = $this->settings->getContainerRefIDForExposing();
        if (!$source_ref_id) {
            return 0;
        }

        $already_exposed = [];
        foreach ($this->exposed_record_repository->getRecords() as $record) {
            $obj_id = $record->infos()->objID();
            $already_exposed[] = $obj_id;

            $ref_id = $this->object_handler->getObjectReferenceIDInContainer($obj_id, $source_ref_id);

            if (!in_array($obj_id, $harvestable_obj_ids) || is_null($ref_id)) {
                $this->logDebug('Deleting exposed record for object with obj_id: ' . $obj_id);
                $this->exposed_record_repository->deleteRecord($obj_id);
                $count++;
                continue;
            }

            $simple_dc_xml = $this->xml_writer->writeSimpleDCMetaData(
                $obj_id,
                $ref_id,
                $this->object_handler->getTypeOfReferencedObject($ref_id)
            );

            if ($simple_dc_xml->saveXML() !== $record->metadata()->saveXML()) {
                $this->logDebug('Updating exposed record for object with obj_id: ' . $obj_id);
                $this->exposed_record_repository->updateRecord($obj_id, $simple_dc_xml);
                $count++;
            }
        }

        foreach ($harvestable_obj_ids as $obj_id) {
            if (in_array($obj_id, $already_exposed)) {
                continue;
            }

            $ref_id = $this->object_handler->getObjectReferenceIDInContainer($obj_id, $source_ref_id);
            if (is_null($ref_id)) {
                continue;
            }

            $type = $this->object_handler->getTypeOfReferencedObject($ref_id);

            $simple_dc_xml = $this->xml_writer->writeSimpleDCMetaData(
                $obj_id,
                $ref_id,
                $type
            );

            $this->logDebug('Creating exposed record for object with obj_id: ' . $obj_id);
            $this->exposed_record_repository->createRecord(
                $obj_id,
                $this->buildIdentifier($obj_id, $type),
                $simple_dc_xml
            );
            $count++;
        }

        return $count;
    }

    protected function logDebug(string $message): void
    {
        $this->logger->debug($message);
    }

    protected function logError(string $message): void
    {
        $this->logger->error($message);
    }

    protected function buildIdentifier(int $obj_id, string $type): string
    {
        return 'il__' . $type . '_' . $obj_id;
    }
}
