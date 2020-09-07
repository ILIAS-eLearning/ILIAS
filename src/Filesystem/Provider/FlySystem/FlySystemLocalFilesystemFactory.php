<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Filesystem\FilesystemFacade;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use League\Flysystem\Adapter\Local;

/**
 * Class FlySystemLocalFilesystemFactory
 *
 * The local fly system filesystem factory creates instances of the local filesystem adapter which is provided by
 * the phpleague.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 */
final class FlySystemLocalFilesystemFactory
{
    const PRIVATE_ACCESS_KEY = 'private';
    const PUBLIC_ACCESS_KEY = 'public';
    const FILE_ACCESS_KEY = 'file';
    const DIRECTORY_ACCESS_KEY = 'dir';

    /**
     * Creates a new instance of the local filesystem adapter used by fly system.
     *
     * @param LocalConfig $config The configuration which should be used to initialise the adapter.
     *
     * @return Filesystem
     */
    public function getInstance(LocalConfig $config)
    {
        $this->validateFileLockMode($config->getLockMode());

        $adapter = new Local(
            $config->getRootPath(),
            $config->getLockMode(),
            $this->mapConfigLinkToLocalLinks($config->getLinkBehaviour()),
            [
                self::FILE_ACCESS_KEY => [
                    self::PRIVATE_ACCESS_KEY => $config->getFileAccessPrivate(),
                    self::PUBLIC_ACCESS_KEY => $config->getFileAccessPublic()
                ],
                self::DIRECTORY_ACCESS_KEY => [
                    self::PRIVATE_ACCESS_KEY => $config->getDirectoryAccessPrivate(),
                    self::PUBLIC_ACCESS_KEY => $config->getDirectoryAccessPublic()
                ]
            ]
        );

        //switch the path separator to a forward slash, see Mantis 0022554
        $reflection = new \ReflectionObject($adapter);
        $property = $reflection->getProperty("pathSeparator");
        $property->setAccessible(true);
        $property->setValue($adapter, '/');

        /* set new path separator in path prefix, the library will replace the old path ending
           while setting the path prefix.
        */
        $adapter->setPathPrefix($adapter->getPathPrefix());


        $filesystem = new \League\Flysystem\Filesystem($adapter);
        $fileAccess = new FlySystemFileAccess($filesystem);
        $facade = new FilesystemFacade(
            new FlySystemFileStreamAccess($filesystem),
            $fileAccess,
            new FlySystemDirectoryAccess($filesystem, $fileAccess)
        );

        return $facade;
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
    private function mapConfigLinkToLocalLinks($configLinkBehaviour)
    {
        switch ($configLinkBehaviour) {
            case LocalConfig::DISALLOW_LINKS:
                return Local::DISALLOW_LINKS;
            case LocalConfig::SKIP_LINKS:
                return Local::SKIP_LINKS;
            default:
                throw new \InvalidArgumentException("The supplied value \"$configLinkBehaviour\" is not a valid LocalConfig link behaviour constant.");
        }
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
    private function validateFileLockMode($code)
    {
        if ($code === LOCK_EX || $code === LOCK_SH) {
            return;
        }

        throw new \InvalidArgumentException("The supplied value \"$code\" is not a valid file lock mode please check your local file storage configurations.");
    }
}
