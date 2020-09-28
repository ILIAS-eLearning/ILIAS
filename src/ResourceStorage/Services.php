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
     */
    public function __construct(
        StorageHandler $storage_handler,
        RevisionRepository $revision_repository,
        ResourceRepository $resource_repository,
        InformationRepository $information_repository
    ) {
        $b               = new ResourceBuilder(
            $storage_handler,
            $revision_repository,
            $resource_repository,
            $information_repository
        );
        $this->manager   = new Manager($b);
        $this->consumers = new Consumers(
            new ConsumerFactory(new StorageHandlerFactory([$storage_handler]))
            , $b
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
