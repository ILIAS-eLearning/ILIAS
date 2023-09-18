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

    public function getRelativePath(): string
    {
        return $this->relative_path;
    }

    /**
     * @inheritDoc
     */
    public function exists(): bool
    {
        return $this->storage->hasDir($this->relative_path);
    }

    /**
     * @inheritDoc
     */
    public function getAbsolutePath(): string
    {
        if (!$this->exists()) {
            return '';
        }
        return ilFileUtils::getDataDir() . '/' . $this->relative_path;
    }

    abstract protected function getPathPrefix(): string;

    private function init(): void
    {
        $this->relative_path = self::PATH_UPLOAD_PREFIX . '/' . $this->getPathPrefix();
    }
}
