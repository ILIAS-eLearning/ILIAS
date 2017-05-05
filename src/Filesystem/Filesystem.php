<?php
declare(strict_types=1);

namespace ILIAS\Filesystem;

use ILIAS\Filesystem\DTO\FileSize;
use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IllegalArgumentException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * Interface Filesystem
 *
 * The filesystem interface provides the public interface for the
 * Filesystem service API consumer.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @since 5.3
 * @version 1.0
 */
interface Filesystem {

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
	public function read(string $path) : string;


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
	public function has(string $path) : bool;


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
	public function getMimeType(string $path) : string;


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
	public function getTimestamp(string $path) : \DateTime;


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
	public function getSize(string $path, int $fileSizeUnit) : FileSize;


	/**
	 * Create a new directory.
	 *
	 * Please note that the Visibility interface defines two constants PUBLIC and PRIVATE
	 * to ease the development process.
	 *
	 * @param string $path          The directory path which should be created.
	 * @param string $visibility    The visibility of the directory. Defaults to visibility public.
	 *
	 * @return void
	 *
	 * @throws IOException                  If the directory could not be created.
	 * @throws IllegalArgumentException     If the visibility is not 'public' or 'private'.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function createDir(string $path, string $visibility = Visibility::PUBLIC);


	/**
	 * Deletes a directory recursive.
	 *
	 * @param string $path  The path which should be deleted.
	 *
	 * @return void
	 *
	 * @throws IOException If the path could not be deleted.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function deleteDir(string $path);


	/**
	 * Lists the content of a directory.
	 *
	 * @param string $path          The directory which should listed. Defaults to the adapter root directory.
	 * @param bool   $recursive     Set to true if the child directories also should be listed. Defaults to false.
	 *
	 * @return Metadata[]           An array of metadata about all known files, in the given directory.
	 *
	 * @throws DirectoryNotFoundException If the directory is not found or inaccessible.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function listContents(string $path = '', bool $recursive = false) : array;


	/**
	 * Sets the visibility for a file.
	 * Please note that the $visibility must 'public' or 'private'.
	 *
	 * The Visibility interface provides two constants PUBLIC and PRIVATE.
	 * We strongly encourage the consumers of this API to use the constants.
	 *
	 * @param string $path          The path to the file.
	 * @param string $visibility    The new visibility for the given file. This value must be 'private' or 'public'.
	 *
	 * @return bool                         True on success or false on failure.
	 * @throws IllegalArgumentException     If the visibility is not 'public' or 'private'.
	 * @throws FileNotFoundException        If the given file could not be found.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function setVisibility(string $path, string $visibility) : bool;


	/**
	 * Get the file visibility.
	 * The file visibility could be 'public' or 'private'.
	 *
	 * Please note that the Visibility interface defines two constants PUBLIC and PRIVATE
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
	public function getVisibility(string $path) : string;


	/**
	 * Writes the stream to a new file.
	 * The directory path to the file will be created.
	 *
	 * Please check if the resource is still valid after the
	 * writeStream operation is done, because some of the underlying filesystem
	 * implementations are closing the resource.
	 *
	 * The default behaviour of the filesystem implementation is to let the stream open if possible.
	 * Therefore always check and close the resource after the work with the resource is finished.
	 *
	 * @param string   $path        The file which should be used to write the stream into.
	 * @param resource $stream      The stream which should be written into the new file.
	 *
	 * @return void
	 *
	 * @throws IOException                  If the file could not be written to the filesystem.
	 * @throws FileAlreadyExistsException   If the file already exists.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function writeStream(string $path, resource $stream);


	/**
	 * Opens a readable stream of the file.
	 * Please make sure to close the resource after the work is done with "fclose($stream)"
	 *
	 * @param string $path  The path to the file which should be used to open the new stream.
	 *
	 * @return resource The newly created file stream.
	 *
	 * @throws FileNotFoundException    If the file could not be found.
	 * @throws IOException              If the stream could not be opened.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function readStream(string $path) : resource;


	/**
	 * Creates a new file or updates an existing one.
	 * If the file is updated its content will be truncated before writing the stream.
	 *
	 * Please check if the resource is still valid after the
	 * putStream operation is done, because some of the underlying filesystem
	 * implementations are closing the resource.
	 *
	 * The default behaviour of the filesystem implementation is to let the stream open if possible.
	 * Therefore always check and close the resource after the work with the resource is finished.
	 *
	 * @param string   $path       The file which should be used to write the stream into.
	 * @param resource $stream     The stream which should be written to the file.
	 *
	 * @return void
	 *
	 * @throws IOException If the stream could not be written to the file.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function putStream(string $path, resource $stream);


	/**
	 * Updates an existing file.
	 * The file content will be truncated to 0.
	 *
	 * Please check if the resource is still valid after the
	 * updateStream operation is done, because some of the underlying filesystem
	 * implementations are closing the resource.
	 *
	 * The default behaviour of the filesystem implementation is to let the stream open if possible.
	 * Therefore always check and close the resource after the work with the resource is finished.
	 *
	 * @param string   $path        The path to the file which should be updated.
	 * @param resource $stream      The stream which should be used to update the file content.
	 *
	 * @return void
	 *
	 * @throws FileNotFoundException    If the file which should be updated doesn't exist.
	 * @throws IOException              If the file could not be updated.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function updateStream(string $path, resource $stream);


}