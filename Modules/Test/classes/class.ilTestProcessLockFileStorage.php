<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/FileSystem/classes/class.ilFileSystemStorage.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestProcessLockFileStorage extends ilFileSystemStorage
{
    public function __construct(int $contextId)
    {
        parent::__construct(ilFileSystemStorage::STORAGE_DATA, true, $contextId);
    }

    /**
     * Get path prefix. Prefix that will be prepended to the path
     * No trailing slash. E.g ilFiles for files
     *
     * @access protected
     *
     * @return string path prefix e.g files
     */
    protected function getPathPrefix()
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
    protected function getPathPostfix()
    {
        return 'context';
    }

    public function create()
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new ErrorException($message, $severity, 0, $file, $line);
        });

        try {
            ilUtil::makeDirParents($this->getPath());
            restore_error_handler();
        } catch (Exception $e) {
            restore_error_handler();
        }

        if (!file_exists($this->getPath())) {
            throw new ErrorException(sprintf('Could not find directory: %s', $this->getPath()));
        }

        return true;
    }
}
