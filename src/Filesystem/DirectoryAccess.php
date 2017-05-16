<?php

namespace ILIAS\Filesystem;

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\IllegalArgumentException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * Interface DirectoryAccess
 *
 * Defines all directory access operations of the filesystem.
 * Filesystem role interface.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @public
 */
interface DirectoryAccess {

	/**
	 * Create a new directory.
	 *
	 * Please note that the Visibility interface defines two constants PUBLIC_ACCESS and PRIVATE_ACCESS
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
	public function createDir($path, $visibility = Visibility::PUBLIC_ACCESS);


	/**
	 * Copy all childes of the source recursive to the destination.
	 * The file access rights will be copied as well.
	 *
	 * @param string $source        The source which should be scanned and copied.
	 * @param string $destination   The destination of the recursive copy.
	 *
	 * @throws IOException                  Thrown if the directory could not be copied.
	 * @throws FileAlreadyExistsException   Thrown if a file already exists at the destination.
	 * @throws DirectoryNotFoundException   Thrown if the source or target directory could not be found.
	 *
	 * @return void
	 */
	public function copyDir($source, $destination);

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
	public function deleteDir($path);


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
	public function listContents($path = '', $recursive = false);
}