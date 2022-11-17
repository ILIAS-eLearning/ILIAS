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

use ILIAS\DI\Container;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Consumer\InlineSrcBuilder;
use ILIAS\ResourceStorage\Consumer\SrcBuilder;
use ILIAS\ResourceStorage\Information\Repository\InformationDBRepository;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\FileNamePolicyStack;
use ILIAS\ResourceStorage\Policy\NoneFileNamePolicy;
use ILIAS\ResourceStorage\Preloader\DBRepositoryPreloader;
use ILIAS\ResourceStorage\Repositories;
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceDBRepository;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Revision\Repository\RevisionDBRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\FileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\MaxNestingFileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * Responsible for loading the Resource Storage into the dependency injection container of ILIAS
 */
class InitResourceStorage
{
    public const D_SERVICE = 'resource_storage';
    public const D_REPOSITORIES = self::D_SERVICE . '.repositories';
    public const D_STORAGE_HANDLERS = self::D_SERVICE . '.storage_handlers';
    public const D_BUILDER = self::D_SERVICE . '.resource_builder';
    public const D_REPOSITORY_PRELOADER = self::D_SERVICE . '.repository_preloader';
    public const D_SOURCE_BUILDER = self::D_SERVICE . '.source_builder';
    public const D_LOCK_HANDLER = self::D_SERVICE . '.lock_handler';
    public const D_FILENAME_POLICY = self::D_SERVICE . '.filename_policy';

    /**
     * @internal Do not use this in your code. This is only for the DIC to load the Resource Storage
     * and for some migrations Please contact fabian@sr.solutions if you need this as well.
     */
    public function getResourceBuilder(\ILIAS\DI\Container $c): ResourceBuilder
    {
        self::init($c);
        $c[self::D_BUILDER] = function (Container $c): ResourceBuilder {
            /** @var Repositories $repositories */
            $repositories = $c[self::D_REPOSITORIES];
            return new ResourceBuilder(
                $c[self::D_STORAGE_HANDLERS],
                $repositories->getRevisionRepository(),
                $repositories->getResourceRepository(),
                $repositories->getInformationRepository(),
                $repositories->getStakeholderRepository(),
                $c[self::D_LOCK_HANDLER],
                $c[self::D_FILENAME_POLICY],
            );
        };
        return $c[self::D_BUILDER];
    }

    public function init(\ILIAS\DI\Container $c): void
    {
        $c[self::D_REPOSITORIES] = static function (Container $c): Repositories {
            return new Repositories(
                new RevisionDBRepository($c->database()),
                new ResourceDBRepository($c->database()),
                new CollectionDBRepository($c->database()),
                new InformationDBRepository($c->database()),
                new StakeholderDBRepository($c->database())
            );
        };
        $c[self::D_STORAGE_HANDLERS] = static function (Container $c): StorageHandlerFactory {
            $base_dir = defined('CLIENT_DATA_DIR') ? CLIENT_DATA_DIR : $c['filesystem.base_dir'];
            return new StorageHandlerFactory([
                new MaxNestingFileSystemStorageHandler($c['filesystem.storage'], Location::STORAGE),
                new FileSystemStorageHandler($c['filesystem.storage'], Location::STORAGE)
            ], $base_dir);
        };
        $c[self::D_REPOSITORY_PRELOADER] = static function (Container $c) {
            return new DBRepositoryPreloader(
                $c->database(),
                $c[self::D_REPOSITORIES]
            );
        };
        $c[self::D_SOURCE_BUILDER] = static function (Container $c): ?SrcBuilder {
            return null;
        };
        $c[self::D_LOCK_HANDLER] = static function (Container $c): LockHandler {
            return new LockHandlerilDB($c->database());
        };
        $c[self::D_FILENAME_POLICY] = static function (Container $c): FileNamePolicy {
            if ($c->isDependencyAvailable('settings') && $c->isDependencyAvailable('clientIni')) {
                return new ilFileServicesPolicy($c->fileServiceSettings());
            }
            return new NoneFileNamePolicy();
        };

        $c[self::D_SERVICE] = static function (Container $c): \ILIAS\ResourceStorage\Services {
            return new \ILIAS\ResourceStorage\Services(
                $c[self::D_STORAGE_HANDLERS],
                $c[self::D_REPOSITORIES],
                $c[self::D_LOCK_HANDLER],
                $c[self::D_FILENAME_POLICY],
                $c[self::D_SOURCE_BUILDER],
                $c[self::D_REPOSITORY_PRELOADER]
            );
        };
    }
}
