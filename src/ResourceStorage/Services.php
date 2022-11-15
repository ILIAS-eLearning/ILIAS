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

namespace ILIAS\ResourceStorage;

use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Collection\Collections;
use ILIAS\ResourceStorage\Consumer\ConsumerFactory;
use ILIAS\ResourceStorage\Consumer\Consumers;
use ILIAS\ResourceStorage\Consumer\InlineSrcBuilder;
use ILIAS\ResourceStorage\Consumer\SrcBuilder;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Consumer\StreamAccess\TokenFactory;
use ILIAS\ResourceStorage\Identification\UniqueIDCollectionIdentificationGenerator;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\FileNamePolicyStack;
use ILIAS\ResourceStorage\Preloader\RepositoryPreloader;
use ILIAS\ResourceStorage\Preloader\StandardRepositoryPreloader;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * Class Services
 * @public
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services
{
    protected \ILIAS\ResourceStorage\Manager\Manager $manager;
    protected \ILIAS\ResourceStorage\Consumer\Consumers $consumers;
    protected \ILIAS\ResourceStorage\Collection\Collections $collections;
    protected \ILIAS\ResourceStorage\Preloader\RepositoryPreloader $preloader;

    /**
     * Services constructor.
     * @param StorageHandler $storage_handler_factory
     */
    public function __construct(
        StorageHandlerFactory $storage_handler_factory,
        Repositories $repositories,
        LockHandler $lock_handler,
        FileNamePolicy $file_name_policy,
        StreamAccess $stream_access,
        SrcBuilder $src_builder = null,
        RepositoryPreloader $preloader = null
    ) {
        $src_builder ??= new InlineSrcBuilder();
        $stream_info_factory = new TokenFactory(
            $storage_handler_factory->getBaseDir()
        );
        $file_name_policy_stack = new FileNamePolicyStack();
        $file_name_policy_stack->addPolicy($file_name_policy);
        $resource_builder = new ResourceBuilder(
            $storage_handler_factory,
            $repositories,
            $lock_handler,
            $stream_access,
            $file_name_policy_stack
        );
        $collection_builder = new CollectionBuilder(
            $repositories->getCollectionRepository(),
            new UniqueIDCollectionIdentificationGenerator(),
            $lock_handler
        );
        $this->preloader = $preloader ?? new StandardRepositoryPreloader($repositories);
        $this->manager = new Manager(
            $resource_builder,
            $collection_builder,
            $this->preloader
        );
        $this->consumers = new Consumers(
            new ConsumerFactory(
                $stream_access,
                $file_name_policy_stack
            ),
            $resource_builder,
            $collection_builder,
            $src_builder
        );
        $this->collections = new Collections(
            $resource_builder,
            $collection_builder,
            $this->preloader
        );
    }

    public function manage(): Manager
    {
        return $this->manager;
    }

    public function consume(): Consumers
    {
        return $this->consumers;
    }

    public function collection(): Collections
    {
        return $this->collections;
    }

    public function preload(array $identification_strings): void
    {
        $this->preloader->preload($identification_strings);
    }
}
