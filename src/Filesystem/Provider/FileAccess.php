<?php

namespace ILIAS\Filesystem\Provider;

/**
 * Interface FileAccess
 *
 * The FileAccess interface defines all file operations.
 * Filesystem role interface.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @public
 */
interface FileAccess extends FileReadAccess, FileWriteAccess
{
}
