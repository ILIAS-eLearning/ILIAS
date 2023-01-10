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

use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Visibility;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
interface DirectoryWriteAccess
{
    /**
     * Create a new directory.
     *
     * Please note that the Visibility interface defines two constants PUBLIC_ACCESS and PRIVATE_ACCESS
     * to ease the development process.
     *
     * @param string $path       The directory path which should be created.
     * @param string $visibility The visibility of the directory. Defaults to visibility public.
     *
     *
     * @throws IOException                  If the directory could not be created.
     * @throws \InvalidArgumentException     If the visibility is not 'public' or 'private'.
     *
     */
    public function createDir(string $path, string $visibility = Visibility::PUBLIC_ACCESS): void;

    /**
     * Copy all childes of the source recursive to the destination.
     * The file access rights will be copied as well.
     *
     * The operation will fail fast if the destination directory is not empty.
     * All destination folders will be created if needed.
     *
     * @param string $source      The source which should be scanned and copied.
     * @param string $destination The destination of the recursive copy.
     *
     *
     * @throws DirectoryNotFoundException   Thrown if the source directory could not be found.
     *
     * @throws IOException                  Thrown if the directory could not be copied.
     */
    public function copyDir(string $source, string $destination): void;

    /**
     * Deletes a directory recursive.
     *
     * @param string $path The path which should be deleted.
     *
     *
     * @throws IOException If the path could not be deleted.
     *
     */
    public function deleteDir(string $path): void;
}
