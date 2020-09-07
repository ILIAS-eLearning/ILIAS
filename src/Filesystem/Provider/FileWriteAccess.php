<?php

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * Interface FileWriteAccess
 *
 * All write file operations of the filesystem service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @see FileAccess
 *
 * @public
 */
interface FileWriteAccess
{
    /**
     * Writes the content to a new file.
     *
     * @param string $path      The path to the file which should be created.
     * @param string $content   The content which should be written to the new file.
     *
     * @return void
     *
     * @throws FileAlreadyExistsException   If the file already exists.
     * @throws IOException                  If the file could not be created or written.
     *
     * @since 5.3
     * @version 1.0
     */
    public function write(string $path, string $content);


    /**
     * Updates the content of a file.
     * Replaces the file content with a new one.
     *
     * @param string $path          The path to the file which should be updated.
     * @param string $newContent    The new file content.
     *
     * @return void
     *
     * @throws FileNotFoundException    If the file is not found.
     * @throws IOException              If the file could not be updated.
     *
     * @since 5.3
     * @version 1.0
     */
    public function update(string $path, string $newContent);


    /**
     * Creates a file or updates an existing one.
     *
     * @param string $path      The path to the file which should be created or updated.
     * @param string $content   The content which should be written to the file.
     *
     * @return void
     *
     * @throws IOException  If the file could not be created or updated.
     *
     * @since 5.3
     * @version 1.0
     *
     * @since 5.3
     * @version 1.0
     */
    public function put(string $path, string $content);


    /**
     * Deletes a file.
     *
     * @param string $path              The path to the file which should be deleted.
     *
     * @return void
     *
     * @throws FileNotFoundException    If the file was not found.
     * @throws IOException              If the file was found but the delete operation finished with errors.
     *
     * @since 5.3
     * @version 1.0
     */
    public function delete(string $path);


    /**
     * Reads the entire file content into a string and removes the file afterwards.
     *
     * @param string $path  The file which should be red and removed.
     *
     * @return string       The entire file content.
     *
     * @throws FileNotFoundException    If the file was not found.
     * @throws IOException              If the file could not red or deleted.
     *
     * @since 5.3
     * @version 1.0
     */
    public function readAndDelete(string $path) : string;


    /**
     * Moves a file from the source to the destination.
     *
     * @param string $path      The current path of the file which should be moved.
     * @param string $newPath   The new path of the file.
     *
     * @return void
     *
     * @throws FileNotFoundException        If the source file is not found.
     * @throws FileAlreadyExistsException   If the destination file is already existing.
     * @throws IOException                  If the file could not be moved.
     *
     * @since 5.3
     * @version 1.0
     */
    public function rename(string $path, string $newPath);


    /**
     * Copy the source file to a destination.
     *
     * @param string $path      The source path to the file which should be copied.
     * @param string $copyPath  The destination path of the file copy.
     *
     * @return void
     *
     * @throws FileNotFoundException        If the source file does not exist.
     * @throws FileAlreadyExistsException   If the destination file already exists.
     * @throws IOException                  If the file could not be copied to the destination.
     *
     * @since 5.3
     * @version 1.0
     */
    public function copy(string $path, string $copyPath);
}
