<?php
declare(strict_types=1);

namespace ILIAS\Filesystem;

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
 * Trait FilesystemsAware
 *
 * Trait which ease the filesystem integration within legacy ILIAS components.
 * This trait should not be used within new components.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
trait FilesystemsAware
{
    private static Filesystems $filesystems;

    /**
     * Returns the loaded filesystems.
     *
     * @return Filesystems
     */
    private static function filesystems() : Filesystems
    {
        if (!isset(self::$filesystems)) {
            global $DIC;
            self::$filesystems = $DIC->filesystem();
        }

        return self::$filesystems;
    }
}
