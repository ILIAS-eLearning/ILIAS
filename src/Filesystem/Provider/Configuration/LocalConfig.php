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

namespace ILIAS\Filesystem\Provider\Configuration;

/**
 * This class is used to configure the local filesystem adapter.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class LocalConfig
{
    /**
     * This is the default behaviour because links violate the root filesystem constraint.
     * Throws an exception if an link is encountered.
     */
    public const DISALLOW_LINKS = 1;
    /**
     * Skip encountered links.
     */
    public const SKIP_LINKS = 2;

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
     * @param string $rootPath               The path to the new filesystem root.
     * @param int    $fileAccessPublic       Public file access mask in octal.
     * @param int    $fileAccessPrivate      Private file access mask in octal.
     * @param int    $directoryAccessPublic  Public directory access mask in octal.
     * @param int    $directoryAccessPrivate Private directory access mask in octal.
     * @param int    $lockMode               Lock modes are defined as build in constants (LOCK_SH, LOCK_EX).
     * @param int    $linkBehaviour          The behaviour how filesystem links should be threaded.
     */
    public function __construct(
        private string $rootPath,
        private int $fileAccessPublic = 0744,
        private int $fileAccessPrivate = 0700,
        private int $directoryAccessPublic = 0755,
        private int $directoryAccessPrivate = 0700,
        private int $lockMode = LOCK_EX,
        private int $linkBehaviour = self::SKIP_LINKS
    ) {
    }

    /**
     */
    public function getFileAccessPublic(): int
    {
        return $this->fileAccessPublic;
    }

    /**
     */
    public function getFileAccessPrivate(): int
    {
        return $this->fileAccessPrivate;
    }

    /**
     */
    public function getDirectoryAccessPublic(): int
    {
        return $this->directoryAccessPublic;
    }

    /**
     */
    public function getDirectoryAccessPrivate(): int
    {
        return $this->directoryAccessPrivate;
    }

    /**
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     */
    public function getLockMode(): int
    {
        return $this->lockMode;
    }

    /**
     */
    public function getLinkBehaviour(): int
    {
        return $this->linkBehaviour;
    }
}
