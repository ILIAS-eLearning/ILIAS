<?php

namespace ILIAS\Filesystem;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    public const PUBLIC_ACCESS = 'public';
    /**
     * Private file visibility.
     * @since 5.3
     */
    public const PRIVATE_ACCESS = 'private';
}
