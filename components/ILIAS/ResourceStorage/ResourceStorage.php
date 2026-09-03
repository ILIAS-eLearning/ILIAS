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

namespace ILIAS;

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\Component\Component;
use ILIAS\Setup\Agent;
use ILIAS\Refinery\Factory;
use ILIAS\Database\PDO\External;
use ILIAS\Filesystem\FileSystems\FilesystemStorage;
use ILIAS\Filesystem\Configuration\FilesystemConfig;
use ILIAS\FileDelivery\FileDeliveryServices;
use ILIAS\Environment\Configuration\Installation\IliasIni;
use ILIAS\Environment\Configuration\Installation\ClientIdProvider;
use ILIAS\ResourceStorage\IRSSServices;
use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Repositories;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\FileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\MaxNestingFileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\Migrator;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Flavour\Machine\Factory as MachineFactory;
use ILIAS\ResourceStorage\Flavour\Engine\Factory as EngineFactory;
use ILIAS\ResourceStorage\Preloader\DBRepositoryPreloader;
use ILIAS\ResourceStorage\Consumer\SrcBuilder;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\FileServices\Policy\FileServicesPolicy;
use ILIAS\ResourceStorage\Artifacts;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\ResourceStorage\Information\Repository\InformationDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\FlavourDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceDBRepository;
use ILIAS\ResourceStorage\Revision\Repository\RevisionDBRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Events\Observer;
use ILIAS\ResourceStorage\IRSSEventLogObserver;
use ILIAS\ResourceStorage\Events\ObserverCollection;
use ILIAS\Environment\Configuration\Installation\Directories;

class ResourceStorage implements Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $define[] = IRSSServices::class;

        $contribute[Agent::class] = static fn(): \ilResourceStorageSetupAgent =>
            new \ilResourceStorageSetupAgent(
                $pull[Factory::class]
            );

        $contribute[Observer::class] = static fn(): Observer =>
            new IRSSEventLogObserver(
                $use[Logging\Logger\LoggerFactoryInterface::class]->getLazy('irss')
            );

        // DB Repositories
        $internal[Repositories::class] = static fn(): Repositories => new Repositories(
            new RevisionDBRepository($use[External::class]),
            new ResourceDBRepository($use[External::class]),
            new CollectionDBRepository($use[External::class]),
            new InformationDBRepository($use[External::class]),
            new StakeholderDBRepository($use[External::class]),
            new FlavourDBRepository($use[External::class]),
        );

        // Lock Handler
        $internal[LockHandler::class] = static fn(): LockHandler =>
            new LockHandlerilDB($use[External::class]);

        // Storage Handler Factory
        $internal[StorageHandlerFactory::class] = static fn(): StorageHandlerFactory =>
            new StorageHandlerFactory(
                [
                    new MaxNestingFileSystemStorageHandler($use[FilesystemStorage::class], Location::STORAGE),
                    new FileSystemStorageHandler($use[FilesystemStorage::class], Location::STORAGE),
                ],
                $use[Directories::class]->getDataDir()
            );
        // Stream Access
        $internal[StreamAccess::class] = static fn(): StreamAccess =>
            new StreamAccess(
                rtrim((string) $use[IliasIni::class]->getDataDirectory(), '/') . '/' . $use[ClientIdProvider::class]->getClientId()->toString(),
                $internal[StorageHandlerFactory::class]
            );

        // Artifacts
        $internal[Artifacts::class] = static function (): Artifacts {
            $flavour_data = is_readable(\ilResourceStorageFlavourArtifact::PATH())
                ? include \ilResourceStorageFlavourArtifact::PATH()
                : [];
            return new Artifacts(
                $flavour_data['machines'] ?? [],
                $flavour_data['definitions'] ?? []
            );
        };

        // Machine Factory
        $internal[MachineFactory::class] = static fn(): MachineFactory =>
            new MachineFactory(
                new EngineFactory(),
                $internal[Artifacts::class]->getFlavourMachines()
            );

        // File Name Policy
        $internal[FileNamePolicy::class] = static fn(): FileNamePolicy =>
            new FileServicesPolicy($use[FilesystemConfig::class]);

        // Source Builder
        $internal[SrcBuilder::class] = static fn(): SrcBuilder =>
            new \ilSecureTokenSrcBuilder($use[FileDeliveryServices::class]);

        // Repository Preloader
        $internal[DBRepositoryPreloader::class] = static fn(): DBRepositoryPreloader =>
            new DBRepositoryPreloader(
                $use[External::class],
                $internal[Repositories::class]
            );

        // Migrator — lazy closure for remover breaks the ResourceBuilder↔Migrator cycle
        $internal[Migrator::class] = static fn(): Migrator =>
            new Migrator(
                $internal[StorageHandlerFactory::class],
                static fn(StorableResource $r) => $internal[ResourceBuilder::class]->remove($r),
                $use[External::class],
                rtrim((string) $use[IliasIni::class]->getDataDirectory(), '/') . '/' . $use[ClientIdProvider::class]->getClientId()->toString()
            );

        // Resource Builder (internal)
        $internal[ResourceBuilder::class] = static fn(): ResourceBuilder =>
            new ResourceBuilder(
                $internal[StorageHandlerFactory::class],
                $internal[Repositories::class],
                $internal[LockHandler::class],
                $internal[StreamAccess::class],
                $internal[FileNamePolicy::class],
                $internal[Migrator::class],
            );

        $internal[ObserverCollection::class] = static fn(): ObserverCollection => new ObserverCollection(
            ...$seek[Observer::class]
        );

        // IRSS Services
        $implement[IRSSServices::class] = static fn(): Services =>
            new Services(
                $internal[StorageHandlerFactory::class],
                $internal[Repositories::class],
                $internal[Artifacts::class],
                $internal[LockHandler::class],
                $internal[FileNamePolicy::class],
                $internal[StreamAccess::class],
                $internal[MachineFactory::class],
                $internal[SrcBuilder::class],
                $internal[DBRepositoryPreloader::class],
                $internal[ObserverCollection::class]
            );
    }
}
