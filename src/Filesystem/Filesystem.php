<?php

namespace ILIAS\Filesystem;

use ILIAS\Filesystem\DTO\FileSize;
use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IllegalArgumentException;
use ILIAS\Filesystem\Exception\IOException;
use Psr\Http\Message\StreamInterface;

/**
 * Interface Filesystem
 *
 * The filesystem interface provides the public interface for the
 * Filesystem service API consumer.
 *
 * The interface consists of several more specific interfaces which are defining the actual access methods of the filesystem. With the smaller interfaces
 * a developer is able to expose only certain parts of the filesystem functionality to his own code.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @since 5.3
 * @version 1.0
 *
 * @public
 */
interface Filesystem extends FileStreamAccess, FileAccess, DirectoryAccess {

}