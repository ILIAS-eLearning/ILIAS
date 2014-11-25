<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once "Services/Object/classes/class.ilObject2.php";
require_once "Modules/Bibliographic/classes/class.ilBibliographicEntry.php";
/* Declaring namespace for library RISReader */
use \LibRIS\RISReader;

/**
 * Class ilObjBibliographic
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>, Gabriel Comte <gc@studer-raimann.ch>
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
	function doCreate() {
		global $ilDB;
		$ilDB->manipulate("INSERT INTO il_bibl_data " . "(id, filename, is_online) VALUES (" . $ilDB->quote($this->getId(), "integer") . "," . // id
			$ilDB->quote($this->getFilename(), "text") . "," . // filename
			$ilDB->quote($this->getOnline(), "integer") . // is_online
			")");
	}


	function doRead() {
		global $ilDB;
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
	function doUpdate() {
		global $ilDB;
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


	/*
	* Delete data from db
	*/
	function doDelete($leave_out_il_bibl_data = false, $leave_out_delete_file = false) {
		global $ilDB;
		if (!$leave_out_delete_file) {
			$this->deleteFile();
		}
		//il_bibl_attribute
		$ilDB->manipulate("DELETE FROM il_bibl_attribute WHERE il_bibl_attribute.entry_id IN "
			. "(SELECT il_bibl_entry.id FROM il_bibl_entry WHERE il_bibl_entry.data_id = " . $ilDB->quote($this->getId(), "integer") . ");");
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


	function  deleteFile() {
		$path = $this->getFilePath(true);
		self::__force_rmdir($path);
	}


	/**
	 * @param bool $without_filename
	 *
	 * @return array with all filepath
	 */
	public function getFilePath($without_filename = false) {
		global $ilDB;
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


	public function setFilename($filename) {
		$this->filename = $filename;
	}


	public function getFilename() {
		return $this->filename;
	}


	/**
	 * @return string returns the absolute filepath of the bib/ris file. it's build as follows: $ILIAS-data-folder/bibl/$id/$filename
	 */
	public function getFileAbsolutePath() {
		return $this->getFileDirectory() . DIRECTORY_SEPARATOR . $this->getFilename();
	}


	public function getFiletype() {
		//return bib for filetype .bibtex:
		if (strtolower(substr($this->getFilename(), - 6)) == "bibtex") {
			return "bib";
		}

		//else return its true filetype
		return strtolower(substr($this->getFilename(), - 3));
	}


	static function getAllOverviewModels() {
		global $ilDB;
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


	static function __readRisFile($full_filename) {
		self::__setCharsetToUtf8($full_filename);
		require_once "./Modules/Bibliographic/lib/LibRIS/src/LibRIS/RISReader.php";
		$ris_reader = new RISReader();
		$ris_reader->parseFile($full_filename);

		return $ris_reader->getRecords();
	}


	static function __readBibFile($full_filename) {
		self::__setCharsetToUtf8($full_filename);
		require_once 'Modules/Bibliographic/lib/PEAR_BibTex_1.0.0RC5/Structures/BibTex.php';
		$bibtex_reader = new Structures_BibTex();
		//Loading and parsing the file example.bib
		$bibtex_reader->loadFile($full_filename);
		//replace bibtex special chars with the real characters
		$bibtex_reader->content = self::__convertBibSpecialChars($bibtex_reader->content);
		$bibtex_reader->setOption('extractAuthor', false);
		$bibtex_reader->parse();
		// Remove library-bug: if there is no cite, the library mixes up the key for the type and the first attribute.
		// It also shows an empty and therefore unwanted cite in the array.
		//
		// The cite is the text coming right after the type. Example:
		// ﻿@book {cite,
		// author = { "...."},
		foreach ($bibtex_reader->data as $key => $entry) {
			if (empty($entry['cite'])) {
				unset($bibtex_reader->data[$key]['cite']);
				foreach ($entry as $attr_key => $attribute) {
					if (strpos($attr_key, '{') !== false) {
						unset($bibtex_reader->data[$key][$attr_key]);
						$attr_key_exploaded = explode('{', $attr_key);
						$bibtex_reader->data[$key]['entryType'] = trim($attr_key_exploaded[0]);
						$bibtex_reader->data[$key][trim($attr_key_exploaded[1])] = $attribute;
					}
				}
			}
			// formating the author to the following type of string
			// Smith, John / Comte, Gabriel / von Gunten Jr, Thomas
			foreach ($entry as $attr_key => $attribute) {
				if ($attr_key == 'author' && is_array($attribute)) {
					$attribute_string = array();
					foreach ($attribute as $author_key => $author) {
						$lastname = array( $author['von'], $author['last'], $author['jr'] );
						$attribute_string[$author_key] = implode(' ', array_filter($lastname));
						if (!empty($author['first'])) {
							$attribute_string[$author_key] .= ', ' . $author['first'];
						}
					}
					$bibtex_reader->data[$key][$attr_key] = implode(' / ', $attribute_string);
				}
			}
		}

		return $bibtex_reader->data;
	}


	static function __setCharsetToUtf8($full_filename) {
		//If file charset does not seem to be Unicode, we assume that it is ISO-8859-1, and convert it to UTF-8.
		$filedata = file_get_contents($full_filename);
		if (strlen($filedata) == strlen(utf8_decode($filedata))) {
			// file charset is not UTF-8
			$filedata = mb_convert_encoding($filedata, 'UTF-8', 'ISO-8859-1');
			file_put_contents($full_filename, $filedata);
		}
	}


	/**
	 * Replace BibTeX Special Characters with real characters
	 * Most systems do not use this encoding. In those cases, nothing will be replaced
	 *
	 * @param String $file_content The string with containing encodings
	 *
	 * @return String (UTF-8) without encodings
	 */
	static function __convertBibSpecialChars($file_content) {
		$bibtex_special_chars['ä'] = '{\"a}';
		$bibtex_special_chars['ë'] = '{\"e}';
		$bibtex_special_chars['ï'] = '{\"i}';
		$bibtex_special_chars['ö'] = '{\"o}';
		$bibtex_special_chars['ü'] = '{\"u}';
		$bibtex_special_chars['Ä'] = '{\"A}';
		$bibtex_special_chars['Ë'] = '{\"E}';
		$bibtex_special_chars['Ï'] = '{\"I}';
		$bibtex_special_chars['Ö'] = '{\"O}';
		$bibtex_special_chars['Ü'] = '{\"U}';
		$bibtex_special_chars['â'] = '{\^a}';
		$bibtex_special_chars['ê'] = '{\^e}';
		$bibtex_special_chars['î'] = '{\^i}';
		$bibtex_special_chars['ô'] = '{\^o}';
		$bibtex_special_chars['û'] = '{\^u}';
		$bibtex_special_chars['Â'] = '{\^A}';
		$bibtex_special_chars['Ê'] = '{\^E}';
		$bibtex_special_chars['Î'] = '{\^I}';
		$bibtex_special_chars['Ô'] = '{\^O}';
		$bibtex_special_chars['Û'] = '{\^U}';
		$bibtex_special_chars['à'] = '{\`a}';
		$bibtex_special_chars['è'] = '{\`e}';
		$bibtex_special_chars['ì'] = '{\`i}';
		$bibtex_special_chars['ò'] = '{\`o}';
		$bibtex_special_chars['ù'] = '{\`u}';
		$bibtex_special_chars['À'] = '{\`A}';
		$bibtex_special_chars['È'] = '{\`E}';
		$bibtex_special_chars['Ì'] = '{\`I}';
		$bibtex_special_chars['Ò'] = '{\`O}';
		$bibtex_special_chars['Ù'] = '{\`U}';
		$bibtex_special_chars['á'] = '{\\\'a}';
		$bibtex_special_chars['é'] = '{\\\'e}';
		$bibtex_special_chars['í'] = '{\\\'i}';
		$bibtex_special_chars['ó'] = '{\\\'o}';
		$bibtex_special_chars['ú'] = '{\\\'u}';
		$bibtex_special_chars['Á'] = '{\\\'A}';
		$bibtex_special_chars['É'] = '{\\\'E}';
		$bibtex_special_chars['Í'] = '{\\\'I}';
		$bibtex_special_chars['Ó'] = '{\\\'O}';
		$bibtex_special_chars['Ú'] = '{\\\'U}';
		$bibtex_special_chars['à'] = '{\`a}';
		$bibtex_special_chars['è'] = '{\`e}';
		$bibtex_special_chars['ì'] = '{\`i}';
		$bibtex_special_chars['ò'] = '{\`o}';
		$bibtex_special_chars['ù'] = '{\`u}';
		$bibtex_special_chars['À'] = '{\`A}';
		$bibtex_special_chars['È'] = '{\`E}';
		$bibtex_special_chars['Ì'] = '{\`I}';
		$bibtex_special_chars['Ò'] = '{\`O}';
		$bibtex_special_chars['Ù'] = '{\`U}';
		$bibtex_special_chars['ç'] = '{\c c}';
		$bibtex_special_chars['ß'] = '{\ss}';
		$bibtex_special_chars['ñ'] = '{\~n}';
		$bibtex_special_chars['Ñ'] = '{\~N}';

		return str_replace($bibtex_special_chars, array_keys($bibtex_special_chars), $file_content);
	}


	/**
	 * Clone BIBL
	 *
	 * @param ilObjBibliographic $new_obj
	 * @param                    $a_target_id
	 * @param int                $a_copy_id copy id
	 *
	 * @internal param \new $ilObjDataCollection object
	 * @return ilObjPoll
	 */
	public function doCloneObject(ilObjBibliographic $new_obj, $a_target_id, $a_copy_id = 0) {
		$new_obj->cloneStructure($this->getId());

		return $new_obj;
	}


	/**
	 * @description Attention only use this for objects who have not yet been created (use like: $x = new ilObjDataCollection; $x->cloneStructure($id))
	 *
	 * @param $original_id The original ID of the dataselection you want to clone it's structure
	 *
	 * @return void
	 */
	public function cloneStructure($original_id) {
		$original = new ilObjBibliographic($original_id);
		$this->moveFile($original->getFileAbsolutePath());
		$this->setOnline(false);
		$this->setDescription($original->getDescription());
		$this->setTitle($original->getTitle());
		$this->setType($original->getType());
		$this->doUpdate();
	}


	/**
	 * @param $input
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
		switch ($this->getFiletype()) {
			case("ris"):
				$entries_from_file = self::__readRisFile($this->getFileAbsolutePath());
				break;
			case("bib"):
				$entries_from_file = self::__readBibFile($this->getFileAbsolutePath());
				break;
		}
		//fill each entry into a ilBibliographicEntry object and then write it to DB by executing doCreate()
		foreach ($entries_from_file as $file_entry) {
			$type = NULL;
			$x = 0;
			$parsed_entry = array();
			foreach ($file_entry as $key => $attribute) {
				// if the attribute is an array, make a comma separated string out of it
				if (is_array($attribute)) {
					$attribute = implode(", ", $attribute);
				}
				// reduce the attribute strings to a maximum of 4000 (ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH) characters, in order to fit in the database
				if (mb_strlen($attribute, 'UTF-8') > self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH) {
					$attribute = mb_substr($attribute, 0, self::ATTRIBUTE_VALUE_MAXIMAL_TEXT_LENGTH - 3, 'UTF-8') . '...';
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
	 * @return is_online
	 */
	public function getOnline() {
		return $this->is_online;
	}
}
