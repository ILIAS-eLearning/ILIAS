<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

abstract class ilImportDirectory implements ilImportDirectoryHandler
{
    /**
     * @var string
     */
    private const PATH_UPLOAD_PREFIX = 'upload';

    /**
     * @var string
     */
    private $relative_path;

    /**
     * @var Filesystem
     */
    protected $storage;

    /**
     * @var ilLogger
     */
    protected $logger;

    public function __construct(Filesystem $storage, ilLogger $logger)
    {
        $this->storage = $storage;
        $this->logger = $logger;
        $this->init();
    }

    public function getRelativePath() : string
    {
        return $this->relative_path;
    }

    /**
     * @return bool
     */
    public function exists() : bool
    {
        return $this->storage->hasDir($this->relative_path);
    }

    /**
     * @return string
     */
    public function getAbsolutePath() : string
    {
        if (!$this->exists()) {
            return '';
        }
        return ilUtil::getDataDir() . '/' . $this->relative_path;
    }

    abstract protected function getPathPrefix() : string;


    private function init()
    {
        $this->relative_path = self::PATH_UPLOAD_PREFIX . '/' . $this->getPathPrefix();
    }
}
