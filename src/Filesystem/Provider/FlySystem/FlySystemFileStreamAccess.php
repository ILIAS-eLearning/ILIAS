<?php

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Provider\FileStreamAccess;
use ILIAS\Filesystem\Stream\FileStream;
use League\Flysystem\Filesystem;

/**
 * Class FlySystemFileStreamAccess
 *
 * Streaming access implementation of the fly system library.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
class FlySystemFileStreamAccess implements FileStreamAccess {

	/**
	 * @var Filesystem $flySystemFS
	 */
	private $flySystemFS;

	/**
	 * FlySystemFileStreamAccess constructor.
	 *
	 * @param Filesystem $flySystemFS   A configured fly system filesystem instance.
	 */
	public function __construct(Filesystem $flySystemFS) { $this->flySystemFS = $flySystemFS; }

	/**
	 * Opens a readable stream of the file.
	 * Please make sure to close the stream after the work is done with "fclose($stream)"
	 *
	 * @param string $path The path to the file which should be used to open the new stream.
	 *
	 * @return FileStream The newly created file stream.
	 *
	 * @throws FileNotFoundException    If the file could not be found.
	 * @throws IOException              If the stream could not be opened.
	 *
	 * @since   5.3
	 * @version 1.0
	 */
	public function readStream($path) {
		// TODO: Implement readStream() method.
	}


	/**
	 * Writes the stream to a new file.
	 * The directory path to the file will be created.
	 *
	 * Please check if the stream is still valid after the
	 * writeStream operation is done, because some of the underlying filesystem
	 * implementations are closing the stream.
	 *
	 * The default behaviour of the filesystem implementation is to let the stream open if possible.
	 * Therefore always check and close the stream after the work with the stream is finished.
	 *
	 * @param string     $path   The file which should be used to write the stream into.
	 * @param FileStream $stream The stream which should be written into the new file.
	 *
	 * @return void
	 *
	 * @throws IOException                  If the file could not be written to the filesystem.
	 * @throws FileAlreadyExistsException   If the file already exists.
	 *
	 * @since   5.3
	 * @version 1.0
	 */
	public function writeStream($path, FileStream $stream) {
		// TODO: Implement writeStream() method.
	}


	/**
	 * Creates a new file or updates an existing one.
	 * If the file is updated its content will be truncated before writing the stream.
	 *
	 * Please check if the stream is still valid after the
	 * putStream operation is done, because some of the underlying filesystem
	 * implementations are closing the stream.
	 *
	 * The default behaviour of the filesystem implementation is to let the stream open if possible.
	 * Therefore always check and close the stream after the work with the stream is finished.
	 *
	 * @param string     $path   The file which should be used to write the stream into.
	 * @param FileStream $stream The stream which should be written to the file.
	 *
	 * @return void
	 *
	 * @throws IOException If the stream could not be written to the file.
	 *
	 * @since   5.3
	 * @version 1.0
	 */
	public function putStream($path, FileStream $stream) {
		// TODO: Implement putStream() method.
	}


	/**
	 * Updates an existing file.
	 * The file content will be truncated to 0.
	 *
	 * Please check if the stream is still valid after the
	 * updateStream operation is done, because some of the underlying filesystem
	 * implementations are closing the stream.
	 *
	 * The default behaviour of the filesystem implementation is to let the stream open if possible.
	 * Therefore always check and close the stream after the work with the stream is finished.
	 *
	 * @param string     $path   The path to the file which should be updated.
	 * @param FileStream $stream The stream which should be used to update the file content.
	 *
	 * @return void
	 *
	 * @throws FileNotFoundException    If the file which should be updated doesn't exist.
	 * @throws IOException              If the file could not be updated.
	 *
	 * @since   5.3
	 * @version 1.0
	 */
	public function updateStream($path, FileStream $stream) {
		// TODO: Implement updateStream() method.
	}
}