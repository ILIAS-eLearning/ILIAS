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

use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\DI\Container;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Artifacts;
use ILIAS\ResourceStorage\Consumer\SrcBuilder;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Information\Repository\InformationDBRepository;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\NoneFileNamePolicy;
use ILIAS\ResourceStorage\Preloader\DBRepositoryPreloader;
use ILIAS\ResourceStorage\Repositories;
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\FlavourDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceDBRepository;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Revision\Repository\RevisionDBRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\FileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\MaxNestingFileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Flavour\FlavourBuilder;
use ILIAS\ResourceStorage\Flavour\Machine\Factory;
use ILIAS\ResourceStorage\StorageHandler\Migrator;
use ILIAS\ResourceStorage\Events\Subject;

/**
 * Responsible for loading the Resource Storage into the dependency injection container of ILIAS
 * @deprecated Use from bootstrapping
 */
class InitResourceStorage
{
    public const D_SERVICE = 'resource_storage';
    public const D_REPOSITORIES = self::D_SERVICE . '.repositories';
    public const D_STORAGE_HANDLERS = self::D_SERVICE . '.storage_handlers';
    public const D_RESOURCE_BUILDER = self::D_SERVICE . '.resource_builder';
    public const D_FLAVOUR_BUILDER = self::D_SERVICE . '.flavour_builder';
    public const D_REPOSITORY_PRELOADER = self::D_SERVICE . '.repository_preloader';
    public const D_SOURCE_BUILDER = self::D_SERVICE . '.source_builder';
    public const D_LOCK_HANDLER = self::D_SERVICE . '.lock_handler';
    public const D_FILENAME_POLICY = self::D_SERVICE . '.filename_policy';
    public const D_STREAM_ACCESS = self::D_SERVICE . '.stream_access';
    public const D_ARTIFACTS = self::D_SERVICE . '.artifacts';
    public const D_MACHINE_FACTORY = self::D_SERVICE . '.machine_factory';
    public const D_MIGRATOR = self::D_SERVICE . '.migrator';

    private bool $init = false;

    /**
     * @internal Do not use this in your code. This is only for the DIC to load the Resource Storage
     * and for some migrations Please contact fabian@sr.solutions if you need this as well.
     */
    public function getResourceBuilder(Container $c): ResourceBuilder
    {
        $this->init($c);
        $c[self::D_RESOURCE_BUILDER] = (fn(Container $c): ResourceBuilder => new ResourceBuilder(
            $c[self::D_STORAGE_HANDLERS],
            $c[self::D_REPOSITORIES],
            $c[self::D_LOCK_HANDLER],
            $c[self::D_STREAM_ACCESS],
            $c[self::D_FILENAME_POLICY],
        ));
        return $c[self::D_RESOURCE_BUILDER];
    }

    /**
     * @internal Do not use this in your code. This is only for the DIC to load the Resource Storage
     * and for some migrations Please contact fabian@sr.solutions if you need this as well.
     */
    public function getFlavourBuilder(Container $c): FlavourBuilder
    {
        $this->init($c);
        $c[self::D_FLAVOUR_BUILDER] = (fn(Container $c): FlavourBuilder => new FlavourBuilder(
            $c[self::D_REPOSITORIES]->getFlavourRepository(),
            $c[self::D_MACHINE_FACTORY],
            $c[self::D_RESOURCE_BUILDER],
            $c[self::D_STORAGE_HANDLERS],
            $c[self::D_STREAM_ACCESS],
            new Subject(),
        ));
        return $c[self::D_FLAVOUR_BUILDER];
    }

    public function init(Container $c): void
    {
        if ($this->init) {
            return;
        }
        $base_dir = $this->buildBasePath();

        // DB Repositories
        $c[self::D_REPOSITORIES] = (static fn(Container $c): Repositories => new Repositories(
            new RevisionDBRepository($c->database()),
            new ResourceDBRepository($c->database()),
            new CollectionDBRepository($c->database()),
            new InformationDBRepository($c->database()),
            new StakeholderDBRepository($c->database()),
            new FlavourDBRepository($c->database()),
        ));

        // Repository Preloader
        $c[self::D_REPOSITORY_PRELOADER] = (static fn(Container $c): DBRepositoryPreloader => new DBRepositoryPreloader(
            $c->database(),
            $c[self::D_REPOSITORIES]
        ));

        // Lock Handler
        $c[self::D_LOCK_HANDLER] = (static fn(Container $c): LockHandler => new LockHandlerilDB($c->database()));

        // Storage Handlers
        $c[self::D_STORAGE_HANDLERS] = (static fn(Container $c): StorageHandlerFactory => new StorageHandlerFactory([
            new MaxNestingFileSystemStorageHandler($c['filesystem.storage'], Location::STORAGE),
            new FileSystemStorageHandler($c['filesystem.storage'], Location::STORAGE)
        ], $base_dir));

        // Source Builder for Consumers
        $c[self::D_SOURCE_BUILDER] = (static fn(Container $c): SrcBuilder => new ilSecureTokenSrcBuilder(
            $c->fileDelivery()
        ));

        // Filename Policy for Consumers
        $c[self::D_FILENAME_POLICY] = static function (Container $c): FileNamePolicy {
            if ($c->isDependencyAvailable('settings') && $c->isDependencyAvailable('clientIni')) {
                return new ilFileServicesPolicy($c->fileServiceSettings());
            }
            return new NoneFileNamePolicy();
        };

        // Artifacts
        $c[self::D_ARTIFACTS] = static function (Container $c): Artifacts {
            $flavour_data = is_readable(ilResourceStorageFlavourArtifact::PATH()) ?
                include ilResourceStorageFlavourArtifact::PATH()
                : [];
            return new Artifacts(
                $flavour_data['machines'] ?? [],
                $flavour_data['definitions'] ?? []
            );
        };

        // Stream Access for Consumers and internal Usage
        $c[self::D_STREAM_ACCESS] = (static fn(Container $c): StreamAccess => new StreamAccess(
            $base_dir,
            $c[self::D_STORAGE_HANDLERS]
        ));

        // Flavours
        $c[self::D_MACHINE_FACTORY] = (static fn(Container $c): Factory => new Factory(
            new \ILIAS\ResourceStorage\Flavour\Engine\Factory(),
            $c[self::D_ARTIFACTS]->getFlavourMachines()
        ));

        //
        // IRSS
        //
        $c[self::D_SERVICE] = (static fn(Container $c): Services => new Services(
            $c[self::D_STORAGE_HANDLERS],
            $c[self::D_REPOSITORIES],
            $c[self::D_ARTIFACTS],
            $c[self::D_LOCK_HANDLER],
            $c[self::D_FILENAME_POLICY],
            $c[self::D_STREAM_ACCESS],
            $c[self::D_MACHINE_FACTORY],
            $c[self::D_SOURCE_BUILDER],
            $c[self::D_REPOSITORY_PRELOADER],
            static fn(): \ilLogger => $c->logger()->irss(),
        ));

        $c[self::D_MIGRATOR] = function (Container $c) use ($base_dir): Migrator {
            $rb = $this->getResourceBuilder($c);
            return new Migrator(
                $c[self::D_STORAGE_HANDLERS],
                static fn(StorableResource $r): bool => $rb->remove($r),
                $c->database(),
                $base_dir
            );
        };

        $this->init = true;
    }

    protected function buildBasePath(): string
    {
        return (defined('ILIAS_DATA_DIR') && defined('CLIENT_ID'))
            ? rtrim((string) ILIAS_DATA_DIR, "/") . "/" . CLIENT_ID
            : '-';
    }
}
