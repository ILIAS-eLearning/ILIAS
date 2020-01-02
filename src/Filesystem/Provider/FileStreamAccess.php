<?php

namespace ILIAS\Filesystem\Provider;

/**
 * Interface FileStreamAccess
 *
 * This interface describes all streaming filesystem operations.
 * Filesystem role interface.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @public
 */
interface FileStreamAccess extends FileStreamReadAccess, FileStreamWriteAccess
{
}
