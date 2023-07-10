<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Decorator\FilesystemWhitelistDecorator;
use ILIAS\Filesystem\Decorator\ReadOnlyDecorator;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;

/**
 * The delegating filesystem factory delegates the instance creation to the
 * factory of the concrete implementation and applies all necessary decorators.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class DelegatingFilesystemFactory implements FilesystemFactory
{
    private FlySystemFilesystemFactory $implementation;

    /**
     * DelegatingFilesystemFactory constructor.
     */
    public function __construct(private FilenameSanitizer $sanitizer)
    {
        /*
         * ---------- ABSTRACTION SWITCH -------------
         * Change the factory to switch to another filesystem abstraction!
         * current: FlySystem from the php league
         * -------------------------------------------
         */
        $this->implementation = new FlySystemFilesystemFactory();
    }

    /**
     * @inheritDoc
     */
    public function getLocal(LocalConfig $config, bool $read_only = false): Filesystem
    {
        if ($read_only) {
            return new ReadOnlyDecorator(
                new FilesystemWhitelistDecorator($this->implementation->getLocal($config), $this->sanitizer)
            );
        }
        return new FilesystemWhitelistDecorator($this->implementation->getLocal($config), $this->sanitizer);
    }
}
