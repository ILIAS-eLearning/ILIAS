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

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
interface FileWriteAccess
{
    /**
     * Writes the content to a new file.
     *
     * @param string $path    The path to the file which should be created.
     * @param string $content The content which should be written to the new file.
     *
     *
     * @throws FileAlreadyExistsException   If the file already exists.
     * @throws IOException                  If the file could not be created or written.
     */
    public function write(string $path, string $content): void;

    /**
     * Updates the content of a file.
     * Replaces the file content with a new one.
     *
     * @param string $path       The path to the file which should be updated.
     * @param string $newContent The new file content.
     *
     *
     * @throws FileNotFoundException    If the file is not found.
     * @throws IOException              If the file could not be updated.
     *
     */
    public function update(string $path, string $new_content): void;

    /**
     * Creates a file or updates an existing one.
     *
     * @param string $path    The path to the file which should be created or updated.
     * @param string $content The content which should be written to the file.
     *
     *
     * @throws IOException  If the file could not be created or updated.
     *
     *
     */
    public function put(string $path, string $content): void;

    /**
     * Deletes a file.
     *
     * @param string $path The path to the file which should be deleted.
     *
     *
     * @throws FileNotFoundException    If the file was not found.
     * @throws IOException              If the file was found but the delete operation finished with errors.
     *
     */
    public function delete(string $path): void;

    /**
     * Reads the entire file content into a string and removes the file afterwards.
     *
     * @param string $path The file which should be red and removed.
     *
     * @return string       The entire file content.
     *
     * @throws FileNotFoundException    If the file was not found.
     * @throws IOException              If the file could not red or deleted.
     *
     */
    public function readAndDelete(string $path): string;

    /**
     * Moves a file from the source to the destination.
     *
     * @param string $path    The current path of the file which should be moved.
     * @param string $newPath The new path of the file.
     *
     *
     * @throws FileNotFoundException        If the source file is not found.
     * @throws FileAlreadyExistsException   If the destination file is already existing.
     * @throws IOException                  If the file could not be moved.
     *
     */
    public function rename(string $path, string $new_path): void;

    /**
     * Copy the source file to a destination.
     *
     * @param string $path     The source path to the file which should be copied.
     * @param string $copyPath The destination path of the file copy.
     *
     *
     * @throws FileNotFoundException        If the source file does not exist.
     * @throws FileAlreadyExistsException   If the destination file already exists.
     * @throws IOException                  If the file could not be copied to the destination.
     *
     */
    public function copy(string $path, string $copy_path): void;
}
