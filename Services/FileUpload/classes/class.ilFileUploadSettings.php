<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilFileUploadSettings
 *
 * This class encapsulates accesses to settings which are relevant for the
 * file upload functionality of ILIAS.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @package ServicesFileUpload
 */
class ilFileUploadSettings
{
	const CONCURRENT_UPLOADS_DEFAULT = 3;
	const CONCURRENT_UPLOADS_MAX = 10;
	
	/**
	 * The instance of the ilFileUploadSettings.
	 * @var ilFileUploadSettings
	 */
	private static $instance = null;
	
	/**
	 * Settings object
	 * @var ilSetting
	 */
	private $settings = null;
	
	/**
	 * Indicates whether drag and drop file upload is enabled in general.
	 * @var bool
	 */
	private $dndUploadEnabled = true;

	/**
	 * Indicates whether drag and drop file upload in the repository is enabled.
	 * @var bool
	 */
	private $repositoryDndUploadEnabled = true;

	/**
	 * Defines the number of files that can be uploaded at the same time.
	 * @var int
	 */
	private $concurrentUploads = self::CONCURRENT_UPLOADS_DEFAULT;

	/**
	 * Private constructor
	 */
	private function __construct()
	{
		$this->settings = new ilSetting("fileupload");
		$this->dndUploadEnabled = $this->settings->get("dnd_upload_enabled", true) == true;
		$this->repositoryDndUploadEnabled = $this->settings->get("repository_dnd_upload_enabled", true) == true;
		$this->concurrentUploads = $this->settings->get("concurrent_upload_count", self::CONCURRENT_UPLOADS_DEFAULT);
	}

	/**
	 * Sets whether drag and drop file upload is enabled.
	 * 
	 * @param	bool	new value
	 * @return	void
	 */
	public static function setDragAndDropUploadEnabled($newValue)
	{
		$instance = self::getInstance();
		$instance->dndUploadEnabled = $newValue == true;
		$instance->settings->set("dnd_upload_enabled", $instance->dndUploadEnabled);
	}
	
	/**
	 * Gets whether drag and drop file upload is enabled.
	 * 
	 * @return	boolean	value
	 */
	public static function isDragAndDropUploadEnabled()
	{
		return self::getInstance()->dndUploadEnabled;
	}

	/**
	 * Sets whether drag and drop file upload in the repository is enabled.
	 * 
	 * @param	bool	new value
	 * @return	void
	 */
	public static function setRepositoryDragAndDropUploadEnabled($newValue)
	{
		$instance = self::getInstance();
		$instance->repositoryDndUploadEnabled = $newValue == true;
		$instance->settings->set("repository_dnd_upload_enabled", $instance->repositoryDndUploadEnabled);
	}
	
	/**
	 * Gets whether drag and drop file upload in the repository is enabled.
	 * 
	 * @return	boolean	value
	 */
	public static function isRepositoryDragAndDropUploadEnabled()
	{
		return self::getInstance()->repositoryDndUploadEnabled;
	}

	/**
	 * Sets the number of files that can be uploaded at the same time.
	 * 
	 * @param	int	new value
	 * @return	void
	 */
	public static function setConcurrentUploads($newValue)
	{
		// is number?
		if (is_numeric($newValue))
		{
			// don't allow to large numbers
			$newValue = (int)$newValue;
			if ($newValue < 1)
				$newValue = 1;
			else if ($newValue > self::CONCURRENT_UPLOADS_MAX)
				$newValue = self::CONCURRENT_UPLOADS_MAX;
		}
		else
		{
			$newValue = self::CONCURRENT_UPLOADS_DEFAULT;
		}
		
		$instance = self::getInstance();
		$instance->concurrentUploads = $newValue;
		$instance->settings->set("concurrent_upload_count", $instance->concurrentUploads);
	}
	
	/**
	 * Gets the number of files that can be uploaded at the same time.
	 * 
	 * @return	int	value
	 */
	public static function getConcurrentUploads()
	{
		return self::getInstance()->concurrentUploads;
	}
	
	/**
	 * Gets the instance of the ilFileUploadSettings.
	 * @return ilFileUploadSettings
	 */
	private static function getInstance()
	{
		if (self::$instance == null)
			self::$instance = new ilFileUploadSettings();
		
		return self::$instance;
	}
}
?>
