<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage;

use ILIAS\ResourceStorage\Consumer\ConsumerFactory;
use ILIAS\ResourceStorage\Consumer\Consumers;
use ILIAS\ResourceStorage\Information\Repository\InformationRepository;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Revision\Repository\RevisionRepository;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\FileNamePolicyStack;

/**
 * Class Services
 * @public
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services
{

    /**
     * @var Manager
     */
    protected $manager;
    /**
     * @var Consumers
     */
    protected $consumers;

    /**
     * Services constructor.
     * @param StorageHandler        $storage_handler
     * @param RevisionRepository    $revision_repository
     * @param ResourceRepository    $resource_repository
     * @param InformationRepository $information_repository
     * @param StakeholderRepository $stakeholder_repository
     * @param LockHandler           $lock_handler
     * @param FileNamePolicy        $file_name_policy
     */
    public function __construct(
        StorageHandler $storage_handler,
        RevisionRepository $revision_repository,
        ResourceRepository $resource_repository,
        InformationRepository $information_repository,
        StakeholderRepository $stakeholder_repository,
        LockHandler $lock_handler,
        FileNamePolicy $file_name_policy
    ) {
        $file_name_policy_stack = new FileNamePolicyStack();
        $file_name_policy_stack->addPolicy($file_name_policy);

        $b = new ResourceBuilder(
            $storage_handler,
            $revision_repository,
            $resource_repository,
            $information_repository,
            $stakeholder_repository,
            $lock_handler,
            $file_name_policy_stack
        );
        $this->manager = new Manager($b);
        $this->consumers = new Consumers(
            new ConsumerFactory(
                new StorageHandlerFactory([$storage_handler]),
                $file_name_policy_stack
            ),
            $b
        );
    }

    public function manage() : Manager
    {
        return $this->manager;
    }

    public function consume() : Consumers
    {
        return $this->consumers;
    }

}
