<?php

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;

/**
 * Interface DirectoryReadAccess
 *
 * Defines the readonly directory access operations of the filesystem.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @see DirectoryAccess
 *
 * @public
 */
interface DirectoryReadAccess
{

    /**
     * Checks whether the directory exists or not.
     *
     * @param string $path  The path which should be checked.
     *
     * @return bool True if the directory exists otherwise false.
     *
     * @since 5.3
     * @version 1.0
     */
    public function hasDir(string $path) : bool;


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
}
