<?php

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;

/**
 * Interface FilesystemFactory
 *
 * The filesystem factory produces different filesystem types.
 * The creation of the specific filesystem type will be delegated to a specific factory.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 */
interface FilesystemFactory
{

    /**
     * Creates a local filesystem instance with the given configuration.
     *
     * @param LocalConfig $config The local configuration which should be used to create the local filesystem.
     *
     * @param bool        $read_only
     *
     * @return Filesystem
     * @since   5.3
     * @version 1.0
     */
    public function getLocal(LocalConfig $config, bool $read_only = false) : Filesystem;
}
