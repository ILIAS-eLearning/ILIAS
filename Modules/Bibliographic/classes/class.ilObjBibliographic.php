<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once "Services/Object/classes/class.ilObject2.php";
require_once "Modules/Bibliographic/classes/class.ilBibliographicEntry.php";
require_once('./Modules/Bibliographic/classes/Types/Ris/class.ilRis.php');

/**
 * Class ilObjBibliographic
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>, Gabriel Comte <gc@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id: class.ilObjBibliographic.php 2012-01-11 10:37:11Z otruffer $
 *
 * @extends ilObject2
 */
class ilObjBibliographic extends ilObject2 {

	/**
	 * Number of maximum allowed characters for attributes in order to fit in the database
	 *
	 * @var int
	 */
	const ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH = 4000;
	/**
	 * Id of literary articles
	 *
	 * @var int
	 */
	protected $filename;
	/**
	 * Id of literary articles
	 *
	 * @var ilBibliographicEntry[]
	 */
	protected $entries;
	/**
	 * Models describing how the overview of each entry is showed
	 *
	 * @var overviewModels[]
	 */
	protected $overviewModels;
	/**
	 * Models describing how the overview of each entry is showed
	 *
	 * @var is_online
	 */
	protected $is_online;


	/**
	 * initType
	 *
	 * @return void
	 */
	public function initType() {
		$this->type = "bibl";
	}


	/**
	 * If bibliographic object exists, read it's data from database, otherwise create it
	 *
	 * @param $existant_bibl_id int is not set when object is getting created
	 *
	 * @return \ilObjBibliographic
	 */
	public function __construct($existant_bibl_id = 0) {
		if ($existant_bibl_id) {
			$this->setId($existant_bibl_id);
			$this->doRead();
		}
		parent::__construct($existant_bibl_id, false);
	}


	/**
	 * Create object
	 *
	 * @return void
	 */
	protected function doCreate() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$ilDB->manipulate("INSERT INTO il_bibl_data " . "(id, filename, is_online) VALUES (" . $ilDB->quote($this->getId(), "integer") . "," . // id
		                  $ilDB->quote($this->getFilename(), "text") . "," . // filename
		                  $ilDB->quote($this->getOnline(), "integer") . // is_online
		                  ")");
	}


	protected function doRead() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$set = $ilDB->query("SELECT * FROM il_bibl_data " . " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
		while ($rec = $ilDB->fetchAssoc($set)) {
			if (!$this->getFilename()) {
				$this->setFilename($rec["filename"]);
			}
			$this->setOnline($rec['is_online']);
		}
	}


	/**
	 * Update data
	 */
	public function doUpdate() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		if (!empty($_FILES['bibliographic_file']['name'])) {
			$this->deleteFile();
			$this->moveFile();
		}
		// Delete the object, but leave the db table 'il_bibl_data' for being able to update it using WHERE, and also leave the file
		$this->doDelete(true, true);
		$ilDB->manipulate("UPDATE il_bibl_data SET " . "filename = " . $ilDB->quote($this->getFilename(), "text") . ", " . // filename
		                  "is_online = " . $ilDB->quote($this->getOnline(), "integer") . // is_online
		                  " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
		$this->writeSourcefileEntriesToDb($this);
	}


	/**
	 * @param bool|false $leave_out_il_bibl_data
	 * @param bool|false $leave_out_delete_file
	 */
	protected function doDelete($leave_out_il_bibl_data = false, $leave_out_delete_file = false) {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		if (!$leave_out_delete_file) {
			$this->deleteFile();
		}
		//il_bibl_attribute
		$ilDB->manipulate("DELETE FROM il_bibl_attribute WHERE il_bibl_attribute.entry_id IN "
		                  . "(SELECT il_bibl_entry.id FROM il_bibl_entry WHERE il_bibl_entry.data_id = " . $ilDB->quote($this->getId(), "integer")
		                  . ");");
		//il_bibl_entry
		$ilDB->manipulate("DELETE FROM il_bibl_entry WHERE data_id = " . $ilDB->quote($this->getId(), "integer"));
		if (!$leave_out_il_bibl_data) {
			//il_bibl_data
			$ilDB->manipulate("DELETE FROM il_bibl_data WHERE id = " . $ilDB->quote($this->getId(), "integer"));
		}
		// delete history entries
		require_once("./Services/History/classes/class.ilHistory.php");
		ilHistory::_removeEntriesForObject($this->getId());
	}


	/**
	 * @return string the folder is: $ILIAS-data-folder/bibl/$id
	 */
	public function getFileDirectory() {
		return ilUtil::getDataDir() . DIRECTORY_SEPARATOR . $this->getType() . DIRECTORY_SEPARATOR . $this->getId();
	}


	/**
	 * @param bool|false $file_to_copy
	 *
	 * @throws Exception
	 */
	public function moveFile($file_to_copy = false) {
		$target_dir = $this->getFileDirectory();
		if (!is_dir($target_dir)) {
			ilUtil::makeDirParents($target_dir);
		}
		if ($_FILES['bibliographic_file']['name']) {
			$filename = $_FILES['bibliographic_file']['name'];
		} elseif ($file_to_copy) {
			//file is not uploaded, but a clone is made out of another bibl
			$split_path = explode(DIRECTORY_SEPARATOR, $file_to_copy);
			$filename = $split_path[sizeof($split_path) - 1];
		} else {
			throw new Exception("Either a file must be delivered via \$_POST/\$_FILE or the file must be delivered via the method argument file_to_copy");
		}
		$target_full_filename = $target_dir . DIRECTORY_SEPARATOR . $filename;
		//If there is no file_to_copy (which is used for clones), copy the file from the temporary upload directory (new creation of object).
		//Therefore, a warning predicates nothing and can be suppressed.
		if (@!copy($file_to_copy, $target_full_filename)) {
			if (!empty($_FILES['bibliographic_file']['tmp_name'])) {
				ilUtil::moveUploadedFile($_FILES['bibliographic_file']['tmp_name'], $_FILES['bibliographic_file']['name'], $target_full_filename);
			} else {
				throw new Exception("The file delivered via the method argument file_to_copy could not be copied. The file '{$file_to_copy}' does probably not exist.");
			}
		}
		$this->setFilename($filename);
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);
	}


	function deleteFile() {
		$path = $this->getFilePath(true);
		self::__force_rmdir($path);
	}


	/**
	 * @param bool $without_filename
	 *
	 * @return array with all filepath
	 */
	public function getFilePath($without_filename = false) {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$set = $ilDB->query("SELECT filename FROM il_bibl_data " . " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
		$rec = $ilDB->fetchAssoc($set);
		{
			if ($without_filename) {
				return substr($rec['filename'], 0, strrpos($rec['filename'], DIRECTORY_SEPARATOR));
			} else {
				return $rec['filename'];
			}
		}
	}


	/**
	 * @param $filename
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
	}


	/**
	 * @return int
	 */
	public function getFilename() {
		return $this->filename;
	}


	/**
	 * @return string returns the absolute filepath of the bib/ris file. it's build as follows: $ILIAS-data-folder/bibl/$id/$filename
	 */
	public function getFileAbsolutePath() {
		return $this->getFileDirectory() . DIRECTORY_SEPARATOR . $this->getFilename();
	}


	/**
	 * @return string
	 */
	public function getFiletype() {
		//return bib for filetype .bibtex:
		if (strtolower(substr($this->getFilename(), - 6)) == "bibtex") {
			return "bib";
		}

		//else return its true filetype
		return strtolower(substr($this->getFilename(), - 3));
	}


	/**
	 * @return array
	 */
	public static function getAllOverviewModels() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$overviewModels = array();
		$set = $ilDB->query('SELECT * FROM il_bibl_overview_model');
		while ($rec = $ilDB->fetchAssoc($set)) {
			if ($rec['literature_type']) {
				$overviewModels[$rec['filetype']][$rec['literature_type']] = $rec['pattern'];
			} else {
				$overviewModels[$rec['filetype']] = $rec['pattern'];
			}
		}

		return $overviewModels;
	}


	/**
	 * remove a directory recursively
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	protected static function __force_rmdir($path) {
		if (!file_exists($path)) {
			return false;
		}
		if (is_file($path) || is_link($path)) {
			return unlink($path);
		}
		if (is_dir($path)) {
			$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$result = true;
			$dir = new DirectoryIterator($path);
			foreach ($dir as $file) {
				if (!$file->isDot()) {
					$result &= self::__force_rmdir($path . $file->getFilename(), false);
				}
			}
			$result &= rmdir($path);

			return $result;
		}
	}


	/**
	 * Clone BIBL
	 *
	 * @param ilObjBibliographic $new_obj
	 * @param                    $a_target_id
	 * @param int $a_copy_id copy id
	 *
	 * @return ilObjPoll
	 */
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null) {
		assert($new_obj instanceof ilObjBibliographic);
		//copy online status if object is not the root copy object
		$cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

		if (!$cp_options->isRootNode($this->getRefId())) {
			$new_obj->setOnline($this->getOnline());
		}

		$new_obj->cloneStructure($this->getId());

		return $new_obj;
	}


	/**
	 * @description Attention only use this for objects who have not yet been created (use like: $x = new ilObjDataCollection;
	 *              $x->cloneStructure($id))
	 *
	 * @param $original_id The original ID of the dataselection you want to clone it's structure
	 *
	 * @return void
	 */
	public function cloneStructure($original_id) {
		$original = new ilObjBibliographic($original_id);
		$this->moveFile($original->getFileAbsolutePath());
		$this->setDescription($original->getDescription());
		$this->setTitle($original->getTitle());
		$this->setType($original->getType());
		$this->doUpdate();
	}


	/**
	 * @param $input
	 *
	 * @deprecated
	 * @return string
	 */
	protected static function __removeSpacesAndDashesAtBeginning($input) {
		for ($i = 0; $i < strlen($input); $i ++) {
			if ($input[$i] != " " && $input[$i] != "-") {
				return substr($input, $i);
			}
		}
	}
	

	/**
	 * Reads out the source file and writes all entries to the database
	 *
	 * @return void
	 */
	public function writeSourcefileEntriesToDb() {
		//Read File
		$entries_from_file = array();
		switch ($this->getFiletype()) {
			case("ris"):
				$ilRis = new ilRis();
				$ilRis->readContent($this->getFileAbsolutePath());

				$entries_from_file = $ilRis->parseContent();
				break;
			case("bib"):
				$bib = new ilBibTex();
				$bib->readContent($this->getFileAbsolutePath());

				$entries_from_file = $bib->parseContent();
				break;
		}
		//fill each entry into a ilBibliographicEntry object and then write it to DB by executing doCreate()
		foreach ($entries_from_file as $file_entry) {
			$type = null;
			$x = 0;
			$parsed_entry = array();
			foreach ($file_entry as $key => $attribute) {
				// if the attribute is an array, make a comma separated string out of it
				if (is_array($attribute)) {
					$attribute = implode(", ", $attribute);
				}
				// reduce the attribute strings to a maximum of 4000 (ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH) characters, in order to fit in the database
				//if (mb_strlen($attribute, 'UTF-8') > self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH) {
				if (ilStr::strLen($attribute) > self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH) {
					// $attribute = mb_substr($attribute, 0, self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH - 3, 'UTF-8') . '...';
					$attribute = ilStr::subStr($attribute, 0, self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH - 3) . '...';
				}
				// ty (RIS) or entryType (BIB) is the type and is treated seperately
				if (strtolower($key) == 'ty' || strtolower($key) == 'entrytype') {
					$type = $attribute;
					continue;
				}
				//TODO - Refactoring for ILIAS 4.5 - get rid off array restructuring
				//change array structure (name not as the key, but under the key "name")
				$parsed_entry[$x]['name'] = $key;
				$parsed_entry[$x ++]['value'] = $attribute;
			}
			//create the entry and fill data into database by executing doCreate()
			$entry_model = ilBibliographicEntry::getInstance($this->getFiletype());
			$entry_model->setType($type);
			$entry_model->setAttributes($parsed_entry);
			$entry_model->setBibliographicObjId($this->getId());
			$entry_model->doCreate();
		}
	}


	/**
	 * @param $a_online
	 */
	public function setOnline($a_online) {
		$this->is_online = $a_online;
	}


	/**
	 * @return bool
	 */
	public function getOnline() {
		return $this->is_online;
	}
}
