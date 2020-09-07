<?php

namespace ILIAS\Filesystem\Provider;

use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * Interface FileReadAccess
 *
 * All readonly file operations of the filesystem service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @see FileAccess
 *
 * @public
 */
interface FileReadAccess
{

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
     * @return \DateTimeImmutable  The timestamp of the file.
     *
     * @throws FileNotFoundException    If the file is not found.
     * @throws IOException              If the file can not be red.
     *
     * @since 5.3
     * @version 1.0
     */
    public function getTimestamp(string $path) : \DateTimeImmutable;


    /**
     * Get the size of a file.
     *
     * The file size units are provided by the DataSize class.
     *
     * @param string $path         The path to the file.
     * @param int    $fileSizeUnit The unit of the file size, which are defined in the DataSize class.
     *
     * @return DataSize
     * @since   5.3
     * @version 1.0
     *
     * @throws IOException              Thrown if the file is not accessible or the underlying filesystem adapter failed.
     * @throws FileNotFoundException    Thrown if the specified file was not found.
     *
     * @see     DataSize
     */
    public function getSize(string $path, int $fileSizeUnit) : DataSize;

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
    public function setVisibility(string $path, string $visibility) : bool;


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
    public function getVisibility(string $path) : string;
}
