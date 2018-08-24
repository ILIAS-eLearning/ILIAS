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
	 * Check if soap administration is enabled
	 */
	protected function checkWebserviceActivation()
	{
		$settings = $GLOBALS['ilSetting'];
		if(!$settings->get('soap_user_administration',0))
		{
			Slim::getInstance()->response()->header('Content-Type','text/html');
			Slim::getInstance()->response()->status(403);
			Slim::getInstance()->response()->body('Webservices not enabled.');
			return false;
		}
		return true;
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
		if(!$this->checkWebserviceActivation())
		{
			return false;
		}
		
		$GLOBALS['ilLog']->write(__METHOD__.' original name: '.$this->getPath().'/'.$name);
		
		$real_path = realpath($this->getPath().'/'.$name);
		if(!$real_path)
		{
			$GLOBALS['ilLog']->write(__METHOD__.' no realpath found for: '.$this->getPath().'/'.$name);
			$this->responeNotFound();
			return;
		}
		$file_name = basename($real_path);
		$GLOBALS['ilLog']->write(__METHOD__.' translated name: '.$this->getPath().'/'.$file_name);
		if(
			$file_name &&
			is_file($this->getPath().'/'.$file_name) && 
			file_exists($this->getPath().'/'.$file_name)
		)
		{
			$GLOBALS['ilLog']->write(__METHOD__.' delivering file: ' . $this->getPath().'/'.$file_name);
			$return = file_get_contents($this->getPath().'/'.$file_name);
			// Response header
			Slim::getInstance()->response()->header('Content-Type', 'application/json');
			Slim::getInstance()->response()->body($return);
			return;
		}
		
		$this->responeNotFound();
	}
	
	
	/**
	 * Send not found response
	 */
	protected function responeNotFound()
	{
		$GLOBALS['ilLog']->write(__METHOD__.' file not found.');
		Slim::getInstance()->response()->header('Content-Type','text/html');
		Slim::getInstance()->response()->status(404);
		Slim::getInstance()->response()->body('Not found');
	}

	/**
	 * Get file by md5 hash
	 * @param <type> $name
	 */
	public function createFile()
	{
		if(!$this->checkWebserviceActivation())
		{
			return false;
		}
		
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
