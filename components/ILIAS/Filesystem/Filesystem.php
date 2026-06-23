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

use ILIAS\Filesystem\Configuration\DirectoryPathConfig;
use ILIAS\Filesystem\FilesystemsImpl;
use ILIAS\Component\Component;
use ILIAS\Setup\Agent;
use ILIAS\Refinery\Factory;
use ILIAS\Filesystem\Provider\FilesystemFactory;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\Security\Sanitizing\DefaultFilenameSanitizer;
use ILIAS\Filesystem\Configuration\FilesystemConfig;
use ILIAS\Filesystem\FileSystems\FilesystemWeb;
use ILIAS\Filesystem\FileSystems\FilesystemStorage;
use ILIAS\Filesystem\FileSystems\FilesystemTemp;
use ILIAS\Filesystem\FileSystems\FilesystemCustomizing;
use ILIAS\Filesystem\FileSystems\FilesystemLibs;
use ILIAS\Filesystem\FileSystems\FilesystemNodeModules;
use ILIAS\Filesystem\Provider\DelegatingFilesystemFactory;
use ILIAS\Filesystem\FileSystems\ConfiguredFilesystemWeb;
use ILIAS\Filesystem\FileSystems\ConfiguredFilesystemStorage;
use ILIAS\Filesystem\FileSystems\ConfiguredFilesystemTemp;
use ILIAS\Filesystem\FileSystems\ConfiguredFilesystemCustomizing;
use ILIAS\Filesystem\FileSystems\ConfiguredFilesystemLibs;
use ILIAS\Filesystem\FileSystems\ConfiguredFilesystemNodeModules;
use ILIAS\Filesystem\Filesystems;
use ILIAS\Filesystem\Configuration\DirectoryPathConfigFromIni;
use ILIAS\Environment\Configuration\Instance\IliasIni;
use ILIAS\Environment\Configuration\Instance\ClientIni;
use ILIAS\Environment\Configuration\Instance\ClientIdProvider;
use ILIAS\Filesystem\Configuration\DatabaseBackedFilesystemConfig;
use ILIAS\Database\PDO\External;
use ILIAS\FileUpload\Processor\PreProcessor;
use ILIAS\Filesystem\Upload\FilenameSanitizerPreProcessor;
use ILIAS\Filesystem\Upload\InsecureFilenameSanitizerPreProcessor;
use ILIAS\Filesystem\Upload\SVGBlacklistPreProcessor;

class Filesystem implements Component
{
    public function init(
        array|\ArrayAccess &$define,
        array|\ArrayAccess &$implement,
        array|\ArrayAccess &$use,
        array|\ArrayAccess &$contribute,
        array|\ArrayAccess &$seek,
        array|\ArrayAccess &$provide,
        array|\ArrayAccess &$pull,
        array|\ArrayAccess &$internal,
    ): void {
        // Sanitizing
        $define[] = FilenameSanitizer::class;
        // Configs
        $define[] = DirectoryPathConfig::class;
        $define[] = FilesystemConfig::class;
        // Filesystems
        $define[] = FilesystemWeb::class;
        $define[] = FilesystemStorage::class;
        $define[] = FilesystemTemp::class;
        $define[] = FilesystemCustomizing::class;
        $define[] = FilesystemLibs::class;
        $define[] = FilesystemNodeModules::class;
        $define[] = Filesystems::class;

        // Sevices
        $define[] = FilesystemFactory::class;

        // Implementations
        $implement[FilenameSanitizer::class] = static fn() => new DefaultFilenameSanitizer(
            $use[FilesystemConfig::class]
        );

        $implement[DirectoryPathConfig::class] = static fn(
        ) => new DirectoryPathConfigFromIni(
            $use[IliasIni::class],
            $use[ClientIni::class],
            $use[ClientIdProvider::class]
        );
        $implement[FilesystemConfig::class] = static fn() => new DatabaseBackedFilesystemConfig(
            $use[External::class]
        );

        $implement[FilesystemFactory::class] = static fn(
        ) => new DelegatingFilesystemFactory(
            $use[FilenameSanitizer::class],
            $use[DirectoryPathConfig::class]
        );

        $implement[FilesystemWeb::class] = static fn() => new ConfiguredFilesystemWeb(
            $use[FilesystemFactory::class]
        );
        $implement[FilesystemStorage::class] = static fn() => new ConfiguredFilesystemStorage(
            $use[FilesystemFactory::class]
        );
        $implement[FilesystemTemp::class] = static fn() => new ConfiguredFilesystemTemp(
            $use[FilesystemFactory::class]
        );
        $implement[FilesystemCustomizing::class] = static fn(
        ) => new ConfiguredFilesystemCustomizing(
            $use[FilesystemFactory::class]
        );
        $implement[FilesystemLibs::class] = static fn() => new ConfiguredFilesystemLibs(
            $use[FilesystemFactory::class]
        );
        $implement[FilesystemNodeModules::class] = static fn(
        ) => new ConfiguredFilesystemNodeModules(
            $use[FilesystemFactory::class]
        );

        $implement[Filesystems::class] = static fn() => new FilesystemsImpl(
            $use[FilesystemStorage::class],
            $use[FilesystemWeb::class],
            $use[FilesystemTemp::class],
            $use[FilesystemCustomizing::class],
            $use[FilesystemLibs::class],
            $use[FilesystemNodeModules::class]
        );

        // ASSETS AND AGENTS
        $contribute[Agent::class] = static fn() => new \ilFileSystemSetupAgent(
            $pull[Factory::class]
        );

        // UPLOAD PRE-PROCESSORS
        $contribute[PreProcessor::class] = static fn() => new FilenameSanitizerPreProcessor();
        $contribute[PreProcessor::class] = static fn() => new InsecureFilenameSanitizerPreProcessor();
        $contribute[PreProcessor::class] = static fn() => new SVGBlacklistPreProcessor();
    }
}
