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

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Decorator\FilesystemWhitelistDecorator;
use ILIAS\Filesystem\Decorator\ReadOnlyDecorator;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\FileSystems\FilesystemWeb;
use ILIAS\Filesystem\Configuration\DirectoryPathConfig;
use ILIAS\Filesystem\FileSystems\FilesystemStorage;
use ILIAS\Filesystem\FileSystems\FilesystemTemp;
use ILIAS\Filesystem\FileSystems\FilesystemCustomizing;
use ILIAS\Filesystem\FileSystems\FilesystemLibs;
use ILIAS\Filesystem\FileSystems\FilesystemNodeModules;

/**
 * The delegating filesystem factory delegates the instance creation to the
 * factory of the concrete implementation and applies all necessary decorators.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class DelegatingFilesystemFactory implements FilesystemFactory
{
    private FlySystemFilesystemFactory $implementation;

    /**
     * DelegatingFilesystemFactory constructor.
     */
    public function __construct(
        private FilenameSanitizer $sanitizer,
        private DirectoryPathConfig $directory_path_config
    ) {
        /*
         * ---------- ABSTRACTION SWITCH -------------
         * Change the factory to switch to another filesystem abstraction!
         * current: FlySystem from the php league
         * -------------------------------------------
         */
        $this->implementation = new FlySystemFilesystemFactory();
    }

    /**
     * @deprectaed
     */
    public function getLocal(LocalConfig $config, bool $read_only = false): Filesystem
    {
        if ($read_only) {
            return new ReadOnlyDecorator(
                new FilesystemWhitelistDecorator($this->implementation->getLocal($config), $this->sanitizer)
            );
        }
        return new FilesystemWhitelistDecorator($this->implementation->getLocal($config), $this->sanitizer);
    }

    public function buildFor(string $fqdn_interface, bool $read_only = false): Filesystem
    {
        $config = match ($fqdn_interface) {
            FilesystemWeb::class => new LocalConfig($this->directory_path_config->getWebDirectoryPath()),
            FilesystemStorage::class => new LocalConfig($this->directory_path_config->getStorageDirectoryPath()),
            FilesystemTemp::class => new LocalConfig($this->directory_path_config->getTempDirectoryPath()),
            FilesystemCustomizing::class => new LocalConfig(
                $this->directory_path_config->getCustomizingDirectoryPath()
            ),
            FilesystemLibs::class => new LocalConfig($this->directory_path_config->getLibsDirectoryPath()),
            FilesystemNodeModules::class => new LocalConfig(
                $this->directory_path_config->getNodeModulesDirectoryPath()
            ),
            default => throw new \InvalidArgumentException("Unknown filesystem interface $fqdn_interface")
        };

        if ($read_only) {
            return new ReadOnlyDecorator(
                new FilesystemWhitelistDecorator($this->implementation->getLocal($config), $this->sanitizer)
            );
        }
        return new FilesystemWhitelistDecorator($this->implementation->getLocal($config), $this->sanitizer);
    }

}
