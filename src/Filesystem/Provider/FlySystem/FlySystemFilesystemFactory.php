<?php

declare(strict_types=1);

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FilesystemFactory;

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
    public function getLocal(LocalConfig $config, bool $read_only = false): Filesystem
    {
        $localFactory = new FlySystemLocalFilesystemFactory();

        return $localFactory->getInstance($config);
    }
}
