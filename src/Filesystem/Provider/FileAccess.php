<?php

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\DTO\FileSize;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * Interface FileAccess
 *
 * The FileAccess interface defines all file operations.
 * Filesystem role interface.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @public
 */
interface FileAccess {

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
	public function write($path, $content);


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
	public function update($path, $newContent);


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
	public function put($path, $content);


	/**
	 * Reads a file content to a string.
	 *
	 * @param string $path                  The path to the file which should be read.
	 *
	 * @return string   The file content.
	 *
	 * @throws FileNotFoundException        If the file doesn't exist.
	 * @throws IOException                  If the file could not be red.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function read($path);


	/**
	 * Checks whether a file exists.
	 *
	 * @param string $path The file path which should be checked.
	 *
	 * @return bool True if the file exists, otherwise false.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function has($path);


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
	public function delete($path);


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
	public function readAndDelete($path);


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
	public function rename($path, $newPath);


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
	public function copy($path, $copyPath);


	/**
	 * Get a files mime-type.
	 *
	 * @param string $path  The file which should be used to get the mime-type.
	 *
	 * @return string   The mime-type of the file.
	 *
	 * @throws FileNotFoundException    If the file is not found.
	 * @throws IOException              If the mime-type could not be determined.
	 */
	public function getMimeType($path);


	/**
	 * Get the timestamp of the file.
	 *
	 * @param string $path  The path to the file.
	 *
	 * @return \DateTime  The timestamp of the file.
	 *
	 * @throws FileNotFoundException    If the file is not found.
	 * @throws IOException              If the file can not be red.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function getTimestamp($path);


	/**
	 * Get the size of a file.
	 *
	 * The file size units are provided by the FileSize class.
	 *
	 * @param string $path         The path to the file.
	 * @param int    $fileSizeUnit The unit of the file size, which are defined in the FileSize class.
	 *
	 * @return FileSize
	 * @since   5.3
	 * @version 1.0
	 *
	 * @see FileSize
	 */
	public function getSize($path, $fileSizeUnit);

	/**
	 * Sets the visibility for a file.
	 * Please note that the $visibility must 'public' or 'private'.
	 *
	 * The Visibility interface provides two constants PUBLIC_ACCESS and PRIVATE_ACCESS.
	 * We strongly encourage the consumers of this API to use the constants.
	 *
	 * @param string $path          The path to the file.
	 * @param string $visibility    The new visibility for the given file. This value must be 'private' or 'public'.
	 *
	 * @return bool                         True on success or false on failure.
	 * @throws \InvalidArgumentException     If the visibility is not 'public' or 'private'.
	 * @throws FileNotFoundException        If the given file could not be found.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function setVisibility($path, $visibility);


	/**
	 * Get the file visibility.
	 * The file visibility could be 'public' or 'private'.
	 *
	 * Please note that the Visibility interface defines two constants PUBLIC_ACCESS and PRIVATE_ACCESS
	 * to ease the development process.
	 *
	 * @param string $path  The path to the file which should be used.
	 *
	 * @return string       The string 'public' or 'private'.
	 *
	 * @throws FileNotFoundException If the file could not be found.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function getVisibility($path);
}