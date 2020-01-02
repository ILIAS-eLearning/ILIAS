<?php

namespace ILIAS\Filesystem;

use ILIAS\Filesystem\Exception\IllegalStateException;

/**
 * Class Filesystems
 *
 * The Filesystems interface defines the access methods which can be used to fetch the different filesystems.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
interface Filesystems
{

    /**
     * Fetches the web filesystem.
     * The web filesystem points to the data directory within the ILIAS web root.
     *
     * @return Filesystem
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     * @since 5.3
     */
    public function web() : Filesystem;


    /**
     * Fetches the storage filesystem.
     * The storage filesystem is the data directory which located outside of the ILIAS web root.
     *
     * @return Filesystem
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     * @since 5.3
     */
    public function storage() : Filesystem;


    /**
     * Fetches the temporary filesystem which can be used for temporary file operations.
     *
     * @return Filesystem
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     * @since 5.3
     */
    public function temp() : Filesystem;


    /**
     * Fetches the customizing filesystem which is located at the root of the customizing directory of ILIAS.
     *
     * @return Filesystem
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     * @since 5.3
     */
    public function customizing() : Filesystem;
    /**
     * Fetches the libs filesystem which is located at the root of the libs directory of ILIAS. This is read only
     *
     * @return Filesystem
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     * @since 5.3
     */
    public function libs() : Filesystem;
}
