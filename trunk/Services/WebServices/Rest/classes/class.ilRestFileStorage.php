<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/FileSystem/classes/class.ilFileSystemStorage.php';

/**
 * File storage handling
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilRestFileStorage extends ilFileSystemStorage
{

	const AVAILABILITY_IN_DAYS = 1;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			ilFileSystemStorage::STORAGE_DATA,
			false,
			0
		);
	}

	/**
	 * Get path prefix
	 */
	protected function  getPathPrefix()
	{
		return 'ilRestFileStorage';
	}

	/**
	 * Get path prefix
	 */
	protected function   getPathPostfix()
	{
		return 'files';
	}

	/**
	 * init and create directory
	 */
	protected function init()
	{
		parent::init();
		$this->create();
	}

	/**
	 * Get file by md5 hash
	 * @param <type> $name
	 */
	public function getFile($name)
	{
		//$return = new stdClass();

		$GLOBALS['ilLog']->write(__METHOD__.' '.$this->getPath().'/'.$name);
		if(file_exists($this->getPath().'/'.$name))
		{
			$GLOBALS['ilLog']->write(__METHOD__.' file exists');
			$return = file_get_contents($this->getPath().'/'.$name);
		}

		// Responce header
		Slim::getInstance()->response()->header('Content-Type', 'application/json');
		Slim::getInstance()->response()->body($return);
	}

	/**
	 * Get file by md5 hash
	 * @param <type> $name
	 */
	public function createFile()
	{
		$request = Slim::getInstance()->request();
		$body = $request->post("content");

		$tmpname = ilUtil::ilTempnam();
		$path = $this->getPath().'/'.basename($tmpname);

		$this->writeToFile($body, $path);
		$return = basename($tmpname);

		$GLOBALS['ilLog']->write(__METHOD__.' Writing to path '.$path);

		Slim::getInstance()->response()->header('Content-Type', 'application/json');
		Slim::getInstance()->response()->body($return);
	}

	public function storeFileForRest($content)
	{
		$tmpname = ilUtil::ilTempnam();
		$path = $this->getPath().'/'.basename($tmpname);

		$this->writeToFile($content, $path);
		return basename($tmpname);
	}

	public function getStoredFilePath($tmpname)
	{
		return $this->getPath().'/'.$tmpname;
	}

	/**
	 * Delete deprecated files
	 */
	public function deleteDeprecated()
	{
		$max_age = time() - self::AVAILABILITY_IN_DAYS * 24 * 60 * 60;
		$ite = new DirectoryIterator($this->getPath());
		foreach($ite as $file)
		{
			if($file->getCTime() <= $max_age)
			{
				try {
					@unlink($file->getPathname());
				}
				catch(Exception $e) {
					$GLOBALS['ilLog']->write(__METHOD__.' '. $e->getMessage());
				}
			}
		}
	}
}
?>
