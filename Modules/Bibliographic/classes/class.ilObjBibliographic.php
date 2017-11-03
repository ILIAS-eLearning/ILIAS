<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once "Services/Object/classes/class.ilObject2.php";
require_once "Modules/Bibliographic/classes/class.ilBibliographicEntry.php";

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

	const FILETYPE_RIS = "ris";
	const FILETYPE_BIB = "bib";
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

		$upload = $DIC->upload();
		if ($upload->hasUploads() && !$upload->hasBeenProcessed()) {
			$upload->process();
			$this->moveUploadedFile($upload);
		}

		$DIC->database()->insert("il_bibl_data", [
			"id" => ["integer", $this->getId()],
			"filename" => ["text", $this->getFilename()],
			"is_online" => ["integer", $this->getOnline()],
		]);
	}


	protected function doRead() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$set = $ilDB->query("SELECT * FROM il_bibl_data " . " WHERE id = "
		                    . $ilDB->quote($this->getId(), "integer"));
		while ($rec = $ilDB->fetchAssoc($set)) {
			if (!$this->getFilename()) {
				$this->setFilename($rec["filename"]);
			}
			$this->setOnline($rec['is_online']);
		}
	}


	public function doUpdate() {
		global $DIC;

		$upload = $DIC->upload();
		if ($upload->hasUploads() && !$upload->hasBeenProcessed()) {
			$upload->process();
			$this->deleteFile();
			$this->moveUploadedFile($upload);
		}

		// Delete the object, but leave the db table 'il_bibl_data' for being able to update it using WHERE, and also leave the file
		$this->doDelete(true, true);

		$DIC->database()->update("il_bibl_data", [
			"filename" => ["text", $this->getFilename()],
			"is_online" => ["integer", $this->getOnline()],
		], ["id" => ["integer", $this->getId()]]);

		$this->writeSourcefileEntriesToDb();
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
		                  . "(SELECT il_bibl_entry.id FROM il_bibl_entry WHERE il_bibl_entry.data_id = "
		                  . $ilDB->quote($this->getId(), "integer") . ")");
		//il_bibl_entry
		$ilDB->manipulate("DELETE FROM il_bibl_entry WHERE data_id = "
		                  . $ilDB->quote($this->getId(), "integer"));
		if (!$leave_out_il_bibl_data) {
			//il_bibl_data
			$ilDB->manipulate("DELETE FROM il_bibl_data WHERE id = "
			                  . $ilDB->quote($this->getId(), "integer"));
		}
		// delete history entries
		ilHistory::_removeEntriesForObject($this->getId());
	}


	/**
	 * @return string the folder is: $ILIAS-data-folder/bibl/$id
	 */
	public function getFileDirectory() {
		return "{$this->getType()}/{$this->getId()}";
	}


	/**
	 * @param \ILIAS\FileUpload\FileUpload $upload
	 */
	protected function moveUploadedFile(\ILIAS\FileUpload\FileUpload $upload) {
		$result = array_values($upload->getResults())[0];
		if ($result->getStatus() == \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
			$this->deleteFile();
			$upload->moveFilesTo($this->getFileDirectory(), \ILIAS\FileUpload\Location::STORAGE);
			$this->setFilename($result->getName());
		}
	}


	/**
	 * @param $file_to_copy
	 */
	private function copyFile($file_to_copy) {
		$target = $this->getFileDirectory() . '/' . basename($file_to_copy);
		$this->getFileSystem()->copy($file_to_copy, $target);
	}


	/**
	 * @return bool
	 */
	protected function deleteFile() {
		$path = $this->getFileDirectory();
		try {
			$this->getFileSystem()->deleteDir($path);
		} catch (\ILIAS\Filesystem\Exception\IOException $e) {
			return false;
		}

		return true;
	}


	/**
	 * @return \ILIAS\Filesystem\Filesystem
	 */
	private function getFileSystem() {
		global $DIC;

		return $DIC["filesystem"]->storage();
	}


	/**
	 * @param bool $without_filename
	 *
	 * @return string
	 */
	public function getFilePath($without_filename = false) {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$set = $ilDB->query("SELECT filename FROM il_bibl_data " . " WHERE id = "
		                    . $ilDB->quote($this->getId(), "integer"));
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
	 * @return string returns the absolute filepath of the bib/ris file. it's build as follows:
	 *                /bibl/$id/$filename
	 */
	public function getFileAbsolutePath() {
		return $this->getFileDirectory() . DIRECTORY_SEPARATOR . $this->getFilename();
	}


	public function getLegacyAbsolutePath() {
		$stream = $this->getFileSystem()->readStream($this->getFileAbsolutePath());

		return $stream->getMetadata('uri');
	}


	/**
	 * @return string
	 */
	public function getFiletype() {
		//return bib for filetype .bibtex:
		$filename = $this->getFilename();
		if (strtolower(substr($filename, - 6)) == "bibtex") {
			return self::FILETYPE_BIB;
		}

		//else return its true filetype
		return strtolower(substr($filename, - 3));
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
	 * Clone BIBL
	 *
	 * @param ilObjBibliographic $new_obj
	 * @param                    $a_target_id
	 * @param int                $a_copy_id copy id
	 *
	 * @return ilObjPoll
	 */
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null, $a_omit_tree = false) {
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
	 * @description Attention only use this for objects who have not yet been created (use like: $x
	 *              = new ilObjDataCollection;
	 *              $x->cloneStructure($id))
	 *
	 * @param int $original_id The original ID of the dataselection you want to clone it's structure
	 *
	 * @return void
	 */
	public function cloneStructure($original_id) {
		$original = new ilObjBibliographic($original_id);
		$this->setFilename($original->getFilename());
		$this->copyFile($original->getFileAbsolutePath());
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
		$filetype = $this->getFiletype();
		switch ($filetype) {
			case(self::FILETYPE_RIS):
				$ilRis = new ilRis();
				$ilRis->readContent($this->getFileAbsolutePath());

				$entries_from_file = $ilRis->parseContent();
				break;
			case(self::FILETYPE_BIB):
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
					$attribute = ilStr::subStr($attribute, 0, self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH
					                                          - 3) . '...';
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
