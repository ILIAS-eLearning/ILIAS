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
	/**
	 * @param integer $activeId
	 */
	public function __construct($activeId)
	{
		parent::__construct(ilFileSystemStorage::STORAGE_DATA, true, $activeId);
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
		return 'active';
	}

	public function create()
	{
		if(!file_exists($this->getPath()))
		{
			ilUtil::makeDirParents($this->getPath());
		}
		return true;
	}
} 