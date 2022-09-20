<?php

namespace ILIAS\Filesystem;

use ILIAS\Filesystem\Finder\Finder;
use ILIAS\Filesystem\Provider\FileStreamAccess;
use ILIAS\Filesystem\Provider\FileAccess;
use ILIAS\Filesystem\Provider\DirectoryAccess;

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
 * Interface Filesystem
 *
 * The filesystem interface provides the public interface for the
 * Filesystem service API consumer.
 *
 * The interface consists of several more specific interfaces which are defining the actual access methods of the filesystem. With the smaller interfaces
 * a developer is able to expose only certain parts of the filesystem functionality to his own code.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @since 5.3
 * @version 1.0
 *
 * @public
 */
interface Filesystem extends FileStreamAccess, FileAccess, DirectoryAccess
{
    /**
     * @return Finder
     */
    public function finder(): Finder;
}
