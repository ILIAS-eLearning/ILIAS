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
use ILIAS\ResourceStorage\Preloader\RepositoryPreloader;
use ILIAS\ResourceStorage\Preloader\StandardRepositoryPreloader;

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
     * @var RepositoryPreloader
     */
    protected $preloader;


    /**
     * Services constructor.
     * @param StorageHandler        $storage_handler_factory
     * @param RevisionRepository    $revision_repository
     * @param ResourceRepository    $resource_repository
     * @param InformationRepository $information_repository
     * @param StakeholderRepository $stakeholder_repository
     * @param LockHandler           $lock_handler
     * @param FileNamePolicy        $file_name_policy
     */
    public function __construct(
        StorageHandlerFactory $storage_handler_factory,
        RevisionRepository $revision_repository,
        ResourceRepository $resource_repository,
        InformationRepository $information_repository,
        StakeholderRepository $stakeholder_repository,
        LockHandler $lock_handler,
        FileNamePolicy $file_name_policy,
        RepositoryPreloader $preloader = null
    ) {
        $file_name_policy_stack = new FileNamePolicyStack();
        $file_name_policy_stack->addPolicy($file_name_policy);

        $b = new ResourceBuilder(
            $storage_handler_factory,
            $revision_repository,
            $resource_repository,
            $information_repository,
            $stakeholder_repository,
            $lock_handler,
            $file_name_policy_stack
        );
        $this->preloader = $preloader ?? new StandardRepositoryPreloader(
                $resource_repository,
                $revision_repository,
                $information_repository,
                $stakeholder_repository
            );

        $this->manager = new Manager($b, $this->preloader);
        $this->consumers = new Consumers(
            new ConsumerFactory(
                $storage_handler_factory,
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

    public function preload(array $identification_strings) : void
    {
        $this->preloader->preload($identification_strings);
    }

}
