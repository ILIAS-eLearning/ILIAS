<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

abstract class ilImportDirectory implements ilImportDirectoryHandler
{
    private const PATH_UPLOAD_PREFIX = 'upload';
    private string $relative_path;

    protected Filesystem $storage;
    protected ilLogger $logger;

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
     * @inheritDoc
     */
    public function exists() : bool
    {
        return $this->storage->hasDir($this->relative_path);
    }

    /**
     * @inheritDoc
     */
    public function getAbsolutePath() : string
    {
        if (!$this->exists()) {
            return '';
        }
        return ilFileUtils::getDataDir() . '/' . $this->relative_path;
    }

    abstract protected function getPathPrefix() : string;

    private function init() : void
    {
        $this->relative_path = self::PATH_UPLOAD_PREFIX . '/' . $this->getPathPrefix();
    }
}
