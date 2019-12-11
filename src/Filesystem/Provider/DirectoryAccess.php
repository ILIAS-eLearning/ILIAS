<?php

namespace ILIAS\Filesystem\Provider;

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
interface DirectoryAccess extends DirectoryReadAccess, DirectoryWriteAccess
{
}
