<?php

declare(strict_types=1);

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
 * Class FilesystemsImpl
 *
 * The Filesystems implementation holds the configuration for the filesystem service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 *
 */
final class FilesystemsImpl implements Filesystems
{
    private Filesystem $node_modules;
    private Filesystem $libs;
    private Filesystem $storage;
    private Filesystem $web;
    private Filesystem $temp;
    private Filesystem $customizing;


    /**
     * FilesystemsImpl constructor.
     *
     * @param Filesystem $storage
     * @param Filesystem $web
     * @param Filesystem $temp
     * @param Filesystem $customizing
     * @param FileSystem $libs
     * @param FileSystem $node_modules
     */
    public function __construct(
        Filesystem $storage,
        Filesystem $web,
        Filesystem $temp,
        Filesystem $customizing,
        FileSystem $libs,
        FileSystem $node_modules
    ) {
        $this->storage = $storage;
        $this->web = $web;
        $this->temp = $temp;
        $this->customizing = $customizing;
        $this->libs = $libs;
        $this->node_modules = $node_modules;
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
