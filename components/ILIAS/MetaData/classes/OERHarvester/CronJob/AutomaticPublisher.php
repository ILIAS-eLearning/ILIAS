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

namespace ILIAS\MetaData\OERHarvester\CronJob;

use ilLogger;
use ILIAS\MetaData\OERHarvester\CronJob\Results\WrapperInterface as Result;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\HandlerInterface as ObjectHandler;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface as StatusRepository;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RepositoryInterface as ExposedRecordRepository;
use ILIAS\MetaData\Copyright\Search\FactoryInterface as CopyrightSearchFactory;
use ILIAS\MetaData\Repository\RepositoryInterface as LOMRepository;
use ILIAS\MetaData\OERHarvester\XML\WriterInterface as SimpleDCXMLWriter;
use ILIAS\Cron\Job\JobResult;
use ILIAS\MetaData\OERHarvester\Publisher\PublisherInterface;

class AutomaticPublisher
{
    public function __construct(
        protected PublisherInterface $publisher,
        protected SettingsInterface $settings,
        protected ObjectHandler $object_handler,
        protected StatusRepository $status_repository,
        protected ExposedRecordRepository $exposed_record_repository,
        protected CopyrightSearchFactory $copyright_search_factory,
        protected LOMRepository $lom_repository,
        protected SimpleDCXMLWriter $xml_writer,
        protected ilLogger $logger
    ) {
    }

    public function run(Result $result): Result
    {
        try {
            $messages = [];

            $harvestable_obj_ids = $this->findHarvestableObjectIDs();
            $currently_harvested_obj_ids = iterator_to_array($this->status_repository->getAllHarvestedObjIDs());

            $deletion_count = $this->withdrawDeprecatedObjects(
                $harvestable_obj_ids,
                $currently_harvested_obj_ids
            );
            $messages[] = 'Withdrew ' . $deletion_count . ' deprecated objects.';

            $exposure_count = $this->updatePublishedObjects();
            $messages[] = 'Updated ' . $exposure_count . ' published objects.';

            $harvest_count = 0;
            if ($this->settings->isAutomaticPublishingEnabled()) {
                $harvest_count = $this->publishObjects(
                    $harvestable_obj_ids,
                    $currently_harvested_obj_ids
                );
                $messages[] = 'Published or submitted for review ' . $harvest_count . ' new objects.';
            }

            if ($deletion_count !== 0 || $harvest_count !== 0 || $exposure_count !== 0) {
                $result = $result->withStatus(JobResult::STATUS_OK);
            } else {
                $result = $result->withStatus(JobResult::STATUS_NO_ACTION);
            }
            return $result->withMessage(implode('<br>', $messages));
        } catch (\Exception $e) {
            return $result->withStatus(JobResult::STATUS_FAIL)
                          ->withMessage($e->getMessage());
        }
    }

    protected function findHarvestableObjectIDs(): array
    {
        $eligible_types = $this->settings->getObjectTypesSelectedForPublishing();
        $eligible_copyright_entries = $this->settings->getCopyrightEntryIDsSelectedForPublishing();

        if ($eligible_types === [] || $eligible_copyright_entries === []) {
            return [];
        }

        $searcher = $this->copyright_search_factory->get()
                                                   ->withRestrictionToRepositoryObjects(true);
        foreach ($eligible_types as $type) {
            $searcher = $searcher->withAdditionalTypeFilter($type);
        }
        $search_results = [];
        foreach ($searcher->search(
            $this->lom_repository,
            ...$eligible_copyright_entries
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
    protected function withdrawDeprecatedObjects(
        array $harvestable_obj_ids,
        array $currently_harvested_obj_ids
    ): int {
        $count = 0;
        foreach ($currently_harvested_obj_ids as $obj_id) {
            if (
                in_array($obj_id, $harvestable_obj_ids) &&
                $this->object_handler->doesReferenceExist($this->status_repository->getHarvestRefID($obj_id))
            ) {
                continue;
            }

            $this->logDebug('Withdrawing deprecated object with obj: ' . $obj_id);
            try {
                $this->publisher->withdraw($obj_id);
            } catch (\Exception $e) {
                $this->logError(
                    'Error when withdrawing from publishing object with obj_id ' .
                    $obj_id . ': ' . $e->getMessage()
                );
                continue;
            }
            $count++;
        }
        return $count;
    }

    /**
     * Returns number of published/submitted objects.
     * @param int[] $harvestable_obj_ids
     * @param int[] $currently_harvested_obj_ids
     */
    protected function publishObjects(
        array $harvestable_obj_ids,
        array $currently_harvested_obj_ids
    ): int {
        $count = 0;

        foreach ($harvestable_obj_ids as $obj_id) {
            if (in_array($obj_id, $currently_harvested_obj_ids)) {
                continue;
            }

            $this->logDebug('Publishing object with obj_id: ' . $obj_id);
            try {
                if ($this->settings->isEditorialStepEnabled()) {
                    $this->publisher->submit($obj_id);
                } else {
                    $type = $this->object_handler->getTypeOfObject($obj_id);
                    $this->publisher->publish($obj_id, $type);
                }
            } catch (\Exception $e) {
                $this->logError(
                    'Error when publishing object with obj_id ' .
                    $obj_id . ': ' . $e->getMessage()
                );
                continue;
            }

            $count++;
        }
        return $count;
    }

    /**
     * Returns number of changed published records.
     */
    protected function updatePublishedObjects(): int
    {
        $count = 0;

        foreach ($this->exposed_record_repository->getRecords() as $record) {
            $obj_id = $record->infos()->objID();
            $ref_id = $this->status_repository->getHarvestRefID($obj_id);

            if (!$ref_id && $record->infos()->isDeleted()) {
                continue;
            }

            $simple_dc_xml = $this->xml_writer->writeSimpleDCMetaData(
                $obj_id,
                $ref_id,
                $this->object_handler->getTypeOfObject($obj_id)
            );

            if (
                $record->infos()->isDeleted() ||
                $simple_dc_xml->saveXML() !== $record->metadata()->saveXML()
            ) {
                // TODO should also be done by the publisher
                $this->logDebug('Updating exposed record for object with obj_id: ' . $obj_id);
                $this->exposed_record_repository->updateRecord($obj_id, false, $simple_dc_xml);
                $count++;
            }
        }

        $this->cleanUpDeletedRecords();

        return $count;
    }

    protected function cleanUpDeletedRecords(): void
    {
        $this->exposed_record_repository->deleteRecordsMarkedAsDeletedOlderThan(
            new \DateInterval('P30D')
        );
    }

    protected function logDebug(string $message): void
    {
        $this->logger->debug($message);
    }

    protected function logError(string $message): void
    {
        $this->logger->error($message);
    }
}
