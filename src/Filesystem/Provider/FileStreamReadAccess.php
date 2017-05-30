<?php

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use Psr\Http\Message\StreamInterface;

/**
 * Interface FileStreamReadAccess
 *
 * This interface describes all readonly streaming filesystem operations.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @see FileStreamAccess
 *
 * @public
 */
interface FileStreamReadAccess {

	/**
	 * Opens a readable stream of the file.
	 * Please make sure to close the stream after the work is done with "fclose($stream)"
	 *
	 * @param string $path  The path to the file which should be used to open the new stream.
	 *
	 * @return StreamInterface The newly created file stream.
	 *
	 * @throws FileNotFoundException    If the file could not be found.
	 * @throws IOException              If the stream could not be opened.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function readStream($path);
}