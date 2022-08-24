<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestProcessLockFileStorage extends ilFileSystemAbstractionStorage
{
    public function __construct(int $contextId)
    {
        parent::__construct(ilFileSystemAbstractionStorage::STORAGE_DATA, true, $contextId);
    }

    /**
     * Get path prefix. Prefix that will be prepended to the path
     * No trailing slash. E.g ilFiles for files
     *
     * @access protected
     *
     * @return string path prefix e.g files
     */
    protected function getPathPrefix(): string
    {
        return 'ilTestProcessLocks';
    }

    /**
     * Get directory name. E.g for files => file
     * Only relative path, no trailing slash
     * '_<obj_id>' will be appended automatically
     *
     * @access protected
     *
     * @return string directory name
     */
    protected function getPathPostfix(): string
    {
        return 'context';
    }

    public function create(): void
    {
        set_error_handler(function ($severity, $message, $file, $line): void {
            throw new ErrorException($message, $severity, 0, $file, $line);
        });

        try {
            ilFileUtils::makeDirParents($this->getPath());
            restore_error_handler();
        } catch (Exception $e) {
            restore_error_handler();
        }

        if (!file_exists($this->getPath())) {
            throw new ErrorException(sprintf('Could not find directory: %s', $this->getPath()));
        }
    }
}
