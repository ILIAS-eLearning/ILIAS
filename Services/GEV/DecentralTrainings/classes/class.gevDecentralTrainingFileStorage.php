<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class gevDecentralTrainingFileStorage.
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training>
*/
require_once("Services/FileSystem/classes/class.ilFileSystemStorage.php");
class gevDecentralTrainingFileStorage extends ilFileSystemStorage {
	private $obj_prefix;

	/**
	 * Construct the storage for the mail log.
	 *
	 * @param integer $a_obj_id The id of the object the storage is responsible for.
	 */
	public function __construct($a_obj_id) {
		$this->obj_prefix = "attachments";
		parent::__construct(self::STORAGE_DATA, false, $a_obj_id);
	}

	/**
	 * Add a file to the storage.
	 * Will calculate hash of the file to store similar files only once.
	 *
	 * @param string $a_filepath The path to the file to be stored.
	 * @return string The hash for the file.
	 */
	public function addFile($a_filepath,$a_filename) {
		if(!@file_exists($a_filepath)) {
			throw new Exception("Can't store file ".$a_filepath.". It does not exist.");
		}

		$this->create();
		$this->copyFile($a_filepath, $this->getAbsolutePath()."/".$a_filename);

		return $hash;
	}

	/**
	 * Delete file
	 *
	 * @access public
	 * @param string absolute name
	 * 
	 */
	public function deleteFile($filename)
	{
		if(@file_exists($this->getAbsolutePath()."/".$filename))
		{
			@unlink($this->getAbsolutePath()."/".$filename);
			return true;
		}
		return false;
	}

		/**
	 * Delete directory
	 *
	 * @access public
	 * @param string absolute name
	 * 
	 */
	function deleteDirectory()
	{
		if(@file_exists($this->getAbsolutePath()))
		{
			ilUtil::delDir($this->getAbsolutePath());
			return true;
		}
		return false;
	}
 
	public function getAllFiles() {
		if(is_dir($this->getAbsolutePath())) {
			return scandir($this->getAbsolutePath());
		}
		
		return false;
	}

	// Implemented for ilFileSystemStorage
	protected function getPathPostfix() {
		return $this->obj_prefix;
	}

	protected function getPathPrefix() {
		return "gevDecentralTrainingFileStorage";
	}
}