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

namespace ILIAS\MetaData\OERHarvester\ControlCenter\State;

use ilAccess;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RepositoryInterface as ExposedRecordsRepository;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface as ResourceStatusRepository;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface as PublishingSettings;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\HandlerInterface as RepoObjectHandler;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as CopyrightIdentifierHandler;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\OERHarvester\Publisher\PublisherInterface;
use ILIAS\MetaData\Repository\RepositoryInterface;

class StateInfoFetcher implements StateInfoFetcherInterface
{
    public function __construct(
        protected ilAccess $access,
        protected ExposedRecordsRepository $exposed_repo,
        protected ResourceStatusRepository $status_repo,
        protected PublishingSettings $publishing_settings,
        protected RepoObjectHandler $repo_object_handler,
        protected CopyrightIdentifierHandler $copyright_identifier_handler,
        protected PublisherInterface $state_changer,
        protected RepositoryInterface $repository,
        protected NavigatorFactoryInterface $navigator_factory,
        protected PathFactory $path_factory
    ) {
    }

    public function getStateInfoForObjectReference(
        int $ref_id,
        int $obj_id,
        string $type
    ): StateInfoInterface {
        $is_publishing_relevant = $this->isPublishingRelevantForObject($ref_id, $type, $obj_id);
        if (!$is_publishing_relevant) {
            return new StateInfo(false, Status::UNPUBLISHED, [], [], [], [], false);
        }
        $current_status = $this->getStatusForObject($obj_id);
        $relevant_actions = $this->getRelevantActions($current_status, $ref_id);
        $eligible_copyright_entry_ids = $this->getEligibleCopyrightEntryIDs();
        $is_copyright_eligible = $this->isCopyrightEligibleForPublishing($obj_id, $type, $eligible_copyright_entry_ids);
        return new StateInfo(
            true,
            $current_status,
            $this->getAllPossibleStatuses(),
            $relevant_actions,
            $this->getUnavailableActions($ref_id, $type, $obj_id, $relevant_actions, $is_copyright_eligible),
            $eligible_copyright_entry_ids,
            $is_copyright_eligible
        );
    }

    public function isPublishingRelevantForObject(int $ref_id, string $type, int $obj_id): bool
    {
        return $this->access->checkAccess('write', '', $ref_id, $type, $obj_id) &&
            (
                $this->publishing_settings->isManualPublishingEnabled() ||
                $this->publishing_settings->isAutomaticPublishingEnabled()
            ) &&
            in_array($type, $this->publishing_settings->getObjectTypesEligibleForPublishing());
    }

    public function getStatusForObject(int $obj_id): Status
    {
        if ($this->exposed_repo->doesUndeletedRecordExistForObjID($obj_id)) {
            return Status::PUBLISHED;
        }
        if ($this->status_repo->isAlreadyHarvested($obj_id)) {
            return Status::UNDER_REVIEW;
        }
        if ($this->status_repo->isHarvestingBlocked($obj_id)) {
            return Status::BLOCKED;
        }
        return Status::UNPUBLISHED;
    }

    /**
     * @return Status[]
     */
    protected function getAllPossibleStatuses(): array
    {
        $statuses = [Status::UNPUBLISHED];
        if ($this->publishing_settings->isAutomaticPublishingEnabled()) {
            $statuses[] = Status::BLOCKED;
        }
        if ($this->publishing_settings->isEditorialStepEnabled()) {
            $statuses[] = Status::UNDER_REVIEW;
        }
        $statuses[] = Status::PUBLISHED;
        return $statuses;
    }


    /**
     * @return Action[]
     */
    protected function getRelevantActions(Status $status, int $ref_id): array
    {
        $actions = [];
        switch ($status) {
            case Status::UNPUBLISHED:
                if ($this->publishing_settings->isAutomaticPublishingEnabled()) {
                    $actions[] = Action::BLOCK;
                }
                if (!$this->publishing_settings->isManualPublishingEnabled()) {
                    break;
                }
                if ($this->publishing_settings->isEditorialStepEnabled()) {
                    $actions[] = Action::SUBMIT;
                } else {
                    $actions[] = Action::PUBLISH;
                }
                break;

            case Status::BLOCKED:
                $actions[] = Action::UNBLOCK;
                break;

            case Status::UNDER_REVIEW:
                if ($this->isReferenceInEditorialCategory($ref_id)) {
                    $actions[] = Action::ACCEPT;
                    $actions[] = Action::REJECT;
                } else {
                    $actions[] = Action::WITHDRAW;
                }
                break;

            case Status::PUBLISHED:
                $actions[] = Action::WITHDRAW;
        }
        return $actions;
    }

    protected function isReferenceInEditorialCategory(int $ref_id): bool
    {
        if (!$this->publishing_settings->isEditorialStepEnabled()) {
            return false;
        }
        return $this->repo_object_handler->isReferenceInContainer(
            $ref_id,
            $this->publishing_settings->getContainerRefIDForEditorialStep()
        );
    }

    /**
     * @return string[]
     */
    protected function getEligibleCopyrightEntryIDs(): array
    {
        return $this->publishing_settings->getCopyrightEntryIDsSelectedForPublishing();
    }

    /**
     * @param int[]    $eligible_copyright_entry_ids
     * @param Action[] $relevant_actions
     * @return Action[]
     */
    protected function getUnavailableActions(
        int $ref_id,
        string $type,
        int $obj_id,
        array $relevant_actions,
        bool $is_copyright_eligible
    ): array {
        $unavailable_actions = [];
        foreach ($relevant_actions as $action) {
            $available = match ($action) {
                Action::BLOCK => $this->state_changer->checkPermissionsForBlock($ref_id, $type, $obj_id),
                Action::UNBLOCK => $this->state_changer->checkPermissionsForUnblock($ref_id, $type, $obj_id),
                Action::PUBLISH =>
                    $this->state_changer->checkPermissionsForPublish($ref_id, $type, $obj_id) &&
                    $is_copyright_eligible,
                Action::WITHDRAW => $this->state_changer->checkPermissionsForWithdraw($ref_id, $type, $obj_id),
                Action::SUBMIT =>
                    $this->state_changer->checkPermissionsForSubmit($ref_id, $type, $obj_id) &&
                    $is_copyright_eligible,
                Action::ACCEPT => $this->state_changer->checkPermissionsForAccept($ref_id, $type, $obj_id),
                Action::REJECT => $this->state_changer->checkPermissionsForReject($ref_id, $type, $obj_id),
            };
            if (!$available) {
                $unavailable_actions[] = $action;
            }
        }
        return $unavailable_actions;
    }

    protected function isCopyrightEligibleForPublishing(
        int $obj_id,
        string $type,
        array $eligible_copyright_entry_ids
    ): bool {
        $copyright_path = $this->path_factory
            ->custom()
            ->withNextStep('rights')
            ->withNextStep('description')
            ->withNextStep('string')
            ->get();
        $set = $this->repository->getMD($obj_id, $obj_id, $type);
        $copyright_string = $this->navigator_factory->navigator($copyright_path, $set->getRoot())
                                                     ->lastElementAtFinalStep()
                                                     ?->getData()
                                                     ?->value() ?? '';
        if (!$this->copyright_identifier_handler->isIdentifierValid($copyright_string)) {
            return false;
        }
        $entry_id = $this->copyright_identifier_handler->parseEntryIDFromIdentifier($copyright_string);
        if (!in_array($entry_id, $eligible_copyright_entry_ids)) {
            return false;
        }
        return true;
    }
}
