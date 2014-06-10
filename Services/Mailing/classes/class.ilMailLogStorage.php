<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilMailLogStorage.
*
* Objects of that class store files for the ilMailLog. Instead of storing
* the file with it's original name, an md5 hash of the content is calculated
* and used as name. Therefore similar files only need to be stored once, while
* different files with same names can be stored as well.
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

require_once("Services/FileSystemStorage/classes/class.ilFileSystemStorage.php");

class ilMailLogStorage extends ilFileSystemStorage {
	private $obj_prefix;

	/**
	 * Construct the storage for the mail log.
	 *
	 * @param integer $a_obj_id The id of the object the storage is responsible for.
	 */
	public function __construct($a_obj_id) {
		$this->obj_prefix = ilObject::_lookupType($a_obj_id);
		parent::__construct(self::STORAGE_DATA, false, $a_obj_id);
	}

	/**
	 * Add a file to the storage.
	 * Will calculate hash of the file to store similar files only once.
	 *
	 * @param string $a_filepath The path to the file to be stored.
	 * @return string The hash for the file.
	 */
	public function addFile($a_filepath) {
		if(!@file_exists($a_filepath)) {
			throw new Exception("Can't store file ".$a_filepath.". It does not exist.");
		}

		$hash = hash_file("md5", $a_filepath);

		$this->create();
		$this->copyFile($a_filepath, $this->getAbsolutePath()."/".$hash);

		return $hash;
	}

	// Implemented for ilFileSystemStorage
	protected function getPathPostfix() {
		return $this->obj_prefix;
	}

	protected function getPathPrefix() {
		return "ilMailLogStorage";
	}
}

?>