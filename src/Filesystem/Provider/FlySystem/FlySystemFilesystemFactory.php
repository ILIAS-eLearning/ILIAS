<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FilesystemFactory;

/**
 * Class FlySystemFilesystemFactory
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class FlySystemFilesystemFactory implements FilesystemFactory
{

    /**
     * @inheritDoc
     */
    public function getLocal(LocalConfig $config, bool $read_only = false) : Filesystem
    {
        $localFactory = new FlySystemLocalFilesystemFactory();

        return $localFactory->getInstance($config);
    }
}
