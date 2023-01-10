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

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
interface DirectoryReadAccess
{
    /**
     * Checks whether the directory exists or not.
     *
     * @param string $path The path which should be checked.
     *
     * @return bool True if the directory exists otherwise false.
     */
    public function hasDir(string $path): bool;

    /**
     * Lists the content of a directory.
     *
     * @param string $path      The directory which should listed. Defaults to the adapter root directory.
     * @param bool   $recursive Set to true if the child directories also should be listed. Defaults to false.
     *
     * @return Metadata[]           An array of metadata about all known files, in the given directory.
     *
     * @throws DirectoryNotFoundException If the directory is not found or inaccessible.
     *
     */
    public function listContents(string $path = '', bool $recursive = false): array;
}
