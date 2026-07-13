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

namespace ILIAS\MetaData\OERHarvester\Publisher;

use ilLogger;
use ilAccess;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface as ResourceStatusRepository;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\HandlerInterface as RepoObjectHandler;
use ILIAS\MetaData\OERHarvester\Export\HandlerInterface as ExportHandler;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface as PublishingSettings;
use ILIAS\MetaData\OERHarvester\XML\WriterInterface as SimpleDCXMLWriter;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RepositoryInterface as ExposedRecordsRepository;

class Publisher implements PublisherInterface
{
    public function __construct(
        protected ExposedRecordsRepository $exposed_repo,
        protected ResourceStatusRepository $status_repo,
        protected RepoObjectHandler $repo_object_handler,
        protected ExportHandler $export_handler,
        protected PublishingSettings $publishing_settings,
        protected SimpleDCXMLWriter $xml_writer,
        protected ilAccess $access
    ) {
    }

    public function block(int $obj_id): void
    {
        $this->status_repo->setHarvestingBlocked($obj_id, true);
    }

    public function checkPermissionsForBlock(int $ref_id, string $type, int $obj_id): bool
    {
        return $this->access->checkAccess('write', '', $ref_id, $type, $obj_id);
    }

    public function unblock(int $obj_id): void
    {
        $this->status_repo->setHarvestingBlocked($obj_id, false);
    }

    public function checkPermissionsForUnblock(int $ref_id, string $type, int $obj_id): bool
    {
        return $this->access->checkAccess('write', '', $ref_id, $type, $obj_id);
    }

    public function publish(int $obj_id, string $type): void
    {
        $target_ref_id = $this->publishing_settings->getContainerRefIDForPublishing();
        $new_ref_id = $this->harvestObject($obj_id, $target_ref_id);
        $this->publishObjectToOAIPMH($obj_id, $type, $new_ref_id);
    }

    public function checkPermissionsForPublish(int $ref_id, string $type, int $obj_id): bool
    {
        $target_ref_id = $this->publishing_settings->getContainerRefIDForPublishing();
        return $this->access->checkAccess('write', '', $ref_id, $type, $obj_id) &&
            $this->access->checkAccess('create_' . $type, '', $target_ref_id);
    }

    public function withdraw(int $obj_id): void
    {
        $this->exposed_repo->updateRecord($obj_id, true, null);

        $ref_id = $this->status_repo->getHarvestRefID($obj_id);
        $this->deleteReferenceIfOthersExist($ref_id);
        $this->status_repo->deleteHarvestRefID($obj_id);

        if ($this->publishing_settings->isAutomaticPublishingEnabled()) {
            $this->block($obj_id);
        }
    }

    public function checkPermissionsForWithdraw(int $ref_id, string $type, int $obj_id): bool
    {
        $harvested_ref_id = $this->status_repo->getHarvestRefID($obj_id);
        return $this->access->checkAccess('write', '', $ref_id, $type, $obj_id) &&
            $this->access->checkAccess('delete', '', $harvested_ref_id, $type, $obj_id);
    }

    public function submit(int $obj_id): void
    {
        $target_ref_id = $this->publishing_settings->getContainerRefIDForEditorialStep();
        $this->harvestObject($obj_id, $target_ref_id);
    }

    public function checkPermissionsForSubmit(int $ref_id, string $type, int $obj_id): bool
    {
        $target_ref_id = $this->publishing_settings->getContainerRefIDForEditorialStep();
        return $this->access->checkAccess('write', '', $ref_id, $type, $obj_id) &&
            $this->access->checkAccess('create_' . $type, '', $target_ref_id);
    }

    public function accept(int $obj_id, string $type): void
    {
        $harvested_ref_id = $this->status_repo->getHarvestRefID($obj_id);

        $publishing_ref_id = $this->publishing_settings->getContainerRefIDForPublishing();
        $ref_id_in_publishing = $this->repo_object_handler->referenceObjectInTargetContainer($obj_id, $publishing_ref_id);
        $this->status_repo->setHarvestRefID($obj_id, $ref_id_in_publishing);

        $this->deleteReferenceIfOthersExist($harvested_ref_id);

        $this->publishObjectToOAIPMH($obj_id, $type, $ref_id_in_publishing);
    }

    public function checkPermissionsForAccept(int $ref_id, string $type, int $obj_id): bool
    {
        $publishing_ref_id = $this->publishing_settings->getContainerRefIDForPublishing();
        $harvested_ref_id = $this->status_repo->getHarvestRefID($obj_id);
        return $this->access->checkAccess('write', '', $ref_id, $type, $obj_id) &&
            $this->access->checkAccess('delete', '', $harvested_ref_id, $type, $obj_id) &&
            $this->access->checkAccess('create_' . $type, '', $publishing_ref_id);
    }

    public function reject(int $obj_id): void
    {
        $ref_id = $this->status_repo->getHarvestRefID($obj_id);
        $this->deleteReferenceIfOthersExist($ref_id);
        $this->status_repo->deleteHarvestRefID($obj_id);

        if ($this->publishing_settings->isAutomaticPublishingEnabled()) {
            $this->block($obj_id);
        }
    }

    public function checkPermissionsForReject(int $ref_id, string $type, int $obj_id): bool
    {
        $harvested_ref_id = $this->status_repo->getHarvestRefID($obj_id);
        return $this->access->checkAccess('write', '', $ref_id, $type, $obj_id) &&
            $this->access->checkAccess('delete', '', $harvested_ref_id, $type, $obj_id);
    }

    protected function harvestObject(int $obj_id, int $target_ref_id): int
    {
        $new_ref_id = $this->repo_object_handler->referenceObjectInTargetContainer(
            $obj_id,
            $target_ref_id
        );
        $this->status_repo->setHarvestRefID($obj_id, $new_ref_id);

        if (!$this->export_handler->hasPublicAccessExport($obj_id)) {
            $this->export_handler->createPublicAccessExport($obj_id);
        }
        return $new_ref_id;
    }

    protected function publishObjectToOAIPMH(int $obj_id, string $type, int $ref_id): void
    {
        $simple_dc_xml = $this->xml_writer->writeSimpleDCMetaData(
            $obj_id,
            $ref_id,
            $type
        );
        if ($this->exposed_repo->doesRecordExistForObjID($obj_id)) {
            $this->exposed_repo->updateRecord(
                $obj_id,
                false,
                $simple_dc_xml
            );
        } else {
            $this->exposed_repo->createRecord(
                $obj_id,
                $this->buildIdentifier($obj_id, $type),
                $simple_dc_xml
            );
        }
    }

    protected function deleteReferenceIfOthersExist(int $ref_id): void
    {
        if ($this->repo_object_handler->isOnlyReference($ref_id)) {
            return;
        }
        $this->repo_object_handler->deleteReference($ref_id);
    }

    protected function buildIdentifier(int $obj_id, string $type): string
    {
        return 'il__' . $type . '_' . $obj_id;
    }
}
