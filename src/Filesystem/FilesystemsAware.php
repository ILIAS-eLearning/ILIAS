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

namespace ILIAS\Filesystem;

/**
 * Trait which ease the filesystem integration within legacy ILIAS components.
 * This trait should not be used within new components.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
trait FilesystemsAware
{
    private static Filesystems $filesystems;

    /**
     * Returns the loaded filesystems.
     */
    private static function filesystems(): Filesystems
    {
        if (!isset(self::$filesystems)) {
            global $DIC;
            self::$filesystems = $DIC->filesystem();
        }

        return self::$filesystems;
    }
}
