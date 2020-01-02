<?php

namespace ILIAS\Filesystem;

/**
 * Interface Visibility
 *
 * This interface provides the available
 * options for the filesystem right management
 * of the filesystem service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @version 1.0
 * @since 5.3
 *
 * @public
 */
interface Visibility
{

    /**
     * Public file visibility.
     * @since 5.3
     */
    const PUBLIC_ACCESS = 'public';
    /**
     * Private file visibility.
     * @since 5.3
     */
    const PRIVATE_ACCESS = 'private';
}
