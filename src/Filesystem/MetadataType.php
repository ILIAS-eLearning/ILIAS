<?php

namespace ILIAS\Filesystem;

use ILIAS\Filesystem\DTO\Metadata;

/**
 * Class MetadataType
 *
 * The possible metadata types of the filesystem metadata.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @see Metadata
 */
interface MetadataType
{
    /**
     * The subject is file.
     */
    const FILE = 'file';
    /**
     * The subject is a directory.
     */
    const DIRECTORY = 'dir';
}
