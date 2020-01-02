<?php
declare(strict_types=1);

namespace ILIAS\Filesystem;

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

    /**
     * @var Filesystem
     */
    private $libs;
    /**
     * @var Filesystem $storage
     */
    private $storage;
    /**
     * @var Filesystem $storage
     */
    private $web;
    /**
     * @var Filesystem $storage
     */
    private $temp;
    /**
     * @var Filesystem $storage
     */
    private $customizing;


    /**
     * FilesystemsImpl constructor.
     *
     * @param Filesystem $storage
     * @param Filesystem $web
     * @param Filesystem $temp
     * @param Filesystem $customizing
     * @param FileSystem $libs
     */
    public function __construct(Filesystem $storage, Filesystem $web, Filesystem $temp, Filesystem $customizing, FileSystem $libs)
    {
        $this->storage = $storage;
        $this->web = $web;
        $this->temp = $temp;
        $this->customizing = $customizing;
        $this->libs = $libs;
    }


    /**
     * @inheritDoc
     */
    public function web() : Filesystem
    {
        return $this->web;
    }


    /**
     * @inheritDoc
     */
    public function storage() : Filesystem
    {
        return $this->storage;
    }


    /**
     * @inheritDoc
     */
    public function temp() : Filesystem
    {
        return $this->temp;
    }


    /**
     * @inheritDoc
     */
    public function customizing() : Filesystem
    {
        return $this->customizing;
    }


    /**
     * @inheritDoc
     */
    public function libs() : Filesystem
    {
        return $this->libs;
    }
}
