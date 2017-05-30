<?php

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use Psr\Http\Message\StreamInterface;

/**
 * Class FileStreamWriteAccess
 *
 * This interface describes the write operations for the streaming filesystem access.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @see FileStreamAccess
 *
 * @public
 */
interface FileStreamWriteAccess {

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
	 * @param string            $path        The file which should be used to write the stream into.
	 * @param StreamInterface   $stream      The stream which should be written into the new file.
	 *
	 * @return void
	 *
	 * @throws IOException                  If the file could not be written to the filesystem.
	 * @throws FileAlreadyExistsException   If the file already exists.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function writeStream($path, StreamInterface $stream);


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
	 * @param string            $path       The file which should be used to write the stream into.
	 * @param StreamInterface   $stream     The stream which should be written to the file.
	 *
	 * @return void
	 *
	 * @throws IOException If the stream could not be written to the file.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function putStream($path, StreamInterface $stream);


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
	 * @param string            $path        The path to the file which should be updated.
	 * @param StreamInterface   $stream      The stream which should be used to update the file content.
	 *
	 * @return void
	 *
	 * @throws FileNotFoundException    If the file which should be updated doesn't exist.
	 * @throws IOException              If the file could not be updated.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function updateStream($path, StreamInterface $stream);
}