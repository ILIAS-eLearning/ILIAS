<?php

namespace ILIAS\FileUpload;

/**
 * Interface Location
 *
 * Defines the valid filesystem locations for the file upload service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
interface Location
{

    /**
     * The filesystem within the ilias web root.
     * Equal to the filesystem->web
     */
    const WEB = 1;
    /**
     * The filesystem outside of the ilias web root.
     * Equal to the filesystem->storage
     */
    const STORAGE = 2;
    /**
     * The filesystem within the web root where all the skins and plugins are saved.
     * Equal to the filesystem->customizing
     */
    const CUSTOMIZING = 3;
    /**
     * The ILIAS temporary directory.
     * Equal to the filesystem->temp
     */
    const TEMPORARY = 4;
}
