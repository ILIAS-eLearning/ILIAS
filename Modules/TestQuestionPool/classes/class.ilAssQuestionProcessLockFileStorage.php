<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/FileSystem/classes/class.ilFileSystemStorage.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionProcessLockFileStorage extends ilFileSystemStorage
{
	private $subPath;
	
	public function __construct($questionId, $userId)
	{
		parent::__construct(ilFileSystemStorage::STORAGE_DATA, true, $questionId);
		
		$this->initSubPath($userId);
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
		return 'ilAssQuestionProcessLocks';
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
		return 'question';
	}
	
	public function getPath()
	{
		return parent::getPath() . '/' . $this->subPath;
	}

	public function create()
	{
		if(!file_exists($this->getPath()))
		{
			ilUtil::makeDirParents($this->getPath());
		}
		return true;
	}
	
	private function initSubPath($userId)
	{
		$userId = (string)$userId;
		
		$path = array();

		for($i = 0, $max = strlen($userId); $i < $max; $i++)
		{
			$path[] = substr($userId, $i, 1);
		}

		$this->subPath = implode('/', $path);

	}
} 