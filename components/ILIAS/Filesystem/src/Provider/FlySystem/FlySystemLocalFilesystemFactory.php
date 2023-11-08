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

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Filesystem\FilesystemFacade;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class FlySystemLocalFilesystemFactory
{
    public const PRIVATE_ACCESS_KEY = 'private';
    public const PUBLIC_ACCESS_KEY = 'public';
    public const FILE_ACCESS_KEY = 'file';
    public const DIRECTORY_ACCESS_KEY = 'dir';

    /**
     * Creates a new instance of the local filesystem adapter used by fly system.
     *
     * @param LocalConfig $config The configuration which should be used to initialise the adapter.
     */
    public function getInstance(LocalConfig $config): \ILIAS\Filesystem\FilesystemFacade
    {
        $this->validateFileLockMode($config->getLockMode());

        $visibility = new PortableVisibilityConverter(
            $config->getFileAccessPublic(),
            $config->getFileAccessPrivate(),
            $config->getDirectoryAccessPublic(),
            $config->getDirectoryAccessPrivate()
        );

        $adapter = new LocalFilesystemAdapter(
            $config->getRootPath(),
            $visibility,
            $config->getLockMode(),
            $this->mapConfigLinkToLocalLinks($config->getLinkBehaviour())
        );

        $filesystem = new \League\Flysystem\Filesystem($adapter);

        $fileAccess = new FlySystemFileAccess($filesystem);

        return new FilesystemFacade(
            new FlySystemFileStreamAccess($filesystem),
            $fileAccess,
            new FlySystemDirectoryAccess($filesystem, $fileAccess)
        );
    }

    /**
     * Maps a constant of the LocalConfig class into a constant of the Local class.
     *
     * Example:
     *
     * @param int $configLinkBehaviour The code of the config link behaviour constant.
     *
     * @return int The mapped code of the Local filesystem adapter.
     */
    private function mapConfigLinkToLocalLinks(int $configLinkBehaviour): int
    {
        return match ($configLinkBehaviour) {
            LocalConfig::DISALLOW_LINKS => LocalFilesystemAdapter::DISALLOW_LINKS,
            LocalConfig::SKIP_LINKS => LocalFilesystemAdapter::SKIP_LINKS,
            default => throw new \InvalidArgumentException(
                "The supplied value \"$configLinkBehaviour\" is not a valid LocalConfig link behaviour constant."
            ),
        };
    }

    /**
     * Checks if the supplied file lock mode is valid.
     * Valid values are LOCK_SH and LOCK_EX.
     *
     * LOCK_SH -> shared lock (read is possible for others)
     * LOCK_EX -> no access for other processes
     *
     * @param int $code The code of the file lock mode which should be checked.
     *
     * @see LOCK_SH
     * @see LOCK_EX
     */
    private function validateFileLockMode(int $code): void
    {
        if ($code === LOCK_EX) {
            return;
        }
        if ($code === LOCK_SH) {
            return;
        }
        throw new \InvalidArgumentException(
            "The supplied value \"$code\" is not a valid file lock mode please check your local file storage configurations."
        );
    }
}
