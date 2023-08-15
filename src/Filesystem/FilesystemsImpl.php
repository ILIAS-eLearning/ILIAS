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

namespace ILIAS\Filesystem;

/**
 * The Filesystems implementation holds the configuration for the filesystem service.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class FilesystemsImpl implements Filesystems
{
    /**
     * FilesystemsImpl constructor.
     */
    public function __construct(
        private Filesystem $storage,
        private Filesystem $web,
        private Filesystem $temp,
        private Filesystem $customizing,
        private FileSystem $libs,
        private FileSystem $node_modules
    ) {
    }

    /**
     * @inheritDoc
     */
    public function web(): Filesystem
    {
        return $this->web;
    }

    /**
     * @inheritDoc
     */
    public function storage(): Filesystem
    {
        return $this->storage;
    }

    /**
     * @inheritDoc
     */
    public function temp(): Filesystem
    {
        return $this->temp;
    }

    /**
     * @inheritDoc
     */
    public function customizing(): Filesystem
    {
        return $this->customizing;
    }

    /**
     * @inheritDoc
     */
    public function libs(): Filesystem
    {
        return $this->libs;
    }

    /**
     * @inheritDoc
     */
    public function nodeModules(): Filesystem
    {
        return $this->node_modules;
    }
}
