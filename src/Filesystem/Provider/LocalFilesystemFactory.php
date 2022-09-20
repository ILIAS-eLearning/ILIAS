<?php

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;

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
 * Interface LocalFilesystemFactory
 *
 * The local filesystem factory creates instances of the local filesystem adapter.
 * The purpose of the specific factory is to hold the FilesystemFactories clean from specific bootstrap code of each adapter.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @see FilesystemFactory
 */
interface LocalFilesystemFactory
{
    /**
     * Creates a local filesystem instance with the given configuration.
     *
     * @param LocalConfig $config   The local configuration which should be used to create the local filesystem.
     *
     * @return Filesystem
     */
    public function getInstance(LocalConfig $config): Filesystem;
}
