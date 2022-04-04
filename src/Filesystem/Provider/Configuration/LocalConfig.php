<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Provider\Configuration;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class LocalConfig
 *
 * This class is used to configure the local filesystem adapter.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
final class LocalConfig
{

    /**
     * This is the default behaviour because links violate the root filesystem constraint.
     * Throws an exception if an link is encountered.
     */
    const DISALLOW_LINKS = 1;
    /**
     * Skip encountered links.
     */
    const SKIP_LINKS = 2;

    private int $fileAccessPublic;
    private int $fileAccessPrivate;
    private int $directoryAccessPublic;
    private int $directoryAccessPrivate;
    private string $rootPath;
    private int $lockMode;
    private int $linkBehaviour;


    /**
     * LocalConfig constructor.
     *
     * Please note that php threads int values with a leading zero as octal values.
     * Therefore the int 0755 equals 493.
     *
     * The permission mask: ugo
     * u = owner
     * g = group
     * o = other
     *
     * r = 4
     * rx = 5
     * rw = 6
     * rwx = 7
     *
     * read = r
     * read-execute = rx
     * read-write = rw
     * read-write-execute = rwx
     *
     * Example public mask:
     * u = rwx
     * g = r
     * o = r
     *
     * rwx r-- r-- which equals 0744
     *
     * Example private mask:
     * u = rwx
     * g = -
     * o = -
     *
     * rwx --- --- which equals 0700
     *
     *
     * @param string $rootPath                  The path to the new filesystem root.
     * @param int    $fileAccessPublic          Public file access mask in octal.
     * @param int    $fileAccessPrivate         Private file access mask in octal.
     * @param int    $directoryAccessPublic     Public directory access mask in octal.
     * @param int    $directoryAccessPrivate    Private directory access mask in octal.
     * @param int    $lockMode                  Lock modes are defined as build in constants (LOCK_SH, LOCK_EX).
     * @param int    $linkBehaviour             The behaviour how filesystem links should be threaded.
     */
    public function __construct(
        string $rootPath,
        int $fileAccessPublic = 0744,
        int $fileAccessPrivate = 0700,
        int $directoryAccessPublic = 0755,
        int $directoryAccessPrivate = 0700,
        int $lockMode = LOCK_EX,
        int $linkBehaviour = self::SKIP_LINKS
    ) {
        $this->rootPath = $rootPath;
        $this->fileAccessPublic = $fileAccessPublic;
        $this->fileAccessPrivate = $fileAccessPrivate;
        $this->directoryAccessPublic = $directoryAccessPublic;
        $this->directoryAccessPrivate = $directoryAccessPrivate;
        $this->lockMode = $lockMode;
        $this->linkBehaviour = $linkBehaviour;
    }


    /**
     * @since 5.3
     */
    public function getFileAccessPublic() : int
    {
        return $this->fileAccessPublic;
    }


    /**
     * @since 5.3
     */
    public function getFileAccessPrivate() : int
    {
        return $this->fileAccessPrivate;
    }


    /**
     * @since 5.3
     */
    public function getDirectoryAccessPublic() : int
    {
        return $this->directoryAccessPublic;
    }


    /**
     * @since 5.3
     */
    public function getDirectoryAccessPrivate() : int
    {
        return $this->directoryAccessPrivate;
    }


    /**
     * @since 5.3
     */
    public function getRootPath() : string
    {
        return $this->rootPath;
    }


    /**
     * @since 5.3
     */
    public function getLockMode() : int
    {
        return $this->lockMode;
    }


    /**
     * @since 5.3
     */
    public function getLinkBehaviour() : int
    {
        return $this->linkBehaviour;
    }
}
