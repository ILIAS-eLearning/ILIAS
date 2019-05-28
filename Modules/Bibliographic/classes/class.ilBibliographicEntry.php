<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBibliographicEntry
 *
 * @author  Gabriel Comte
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id: class.ilBibliographicEntry.php 2012-11-15 16:11:42Z gcomte $
 */
class ilBibliographicEntry {

	/**
	 * ILIAS-Id of bibliographic-object
	 *
	 * @var int
	 */
	protected $bibliographic_obj_id;
	/**
	 * Internal id of entry
	 *
	 * @var int
	 */
	protected $entry_id;
	/**
	 * type of entry
	 *
	 * @var string
	 */
	protected $type;
	/**
	 * array containing all types of attributes the entry has, except the type
	 *
	 * @var string[]
	 */
	protected $attributes;
	/**
	 * file type (bib (also: bibtex) | ris)
	 *
	 * @var string
	 */
	protected $file_type;
	/**
	 * @var string
	 */
	protected $overview = '';
	/**
	 * @var ilBibliographicEntry[]
	 */
	protected static $instances = array();


	/**
	 * @param      $file_type
	 * @param null $entry_id
	 *
	 * @return ilBibliographicEntry
	 */
	public static function getInstance($file_type, $entry_id = null) {
		if (!$entry_id) {
			return new self($file_type, $entry_id);
		}

		if (!isset(self::$instances[$entry_id])) {
			self::$instances[$entry_id] = new self($file_type, $entry_id);
		}

		return self::$instances[$entry_id];
	}


	/**
	 * @param $entry_id
	 * @param $obj_id
	 *
	 * @return bool
	 */
	public static function exists($entry_id, $obj_id) {
		$q = "SELECT * FROM il_bibl_entry WHERE id = %s AND data_id = %s";
		global $DIC;
		$ilDB = $DIC['ilDB'];
		/**
		 * @var $ilDB ilDBInterface
		 */
		$r = $ilDB->queryF($q, array('integer', 'integer'), array($entry_id, $obj_id));

		return ($r->numRows() > 0);
	}


	/**
	 * @param      $file_type
	 * @param null $entry_id
	 */
	protected function __construct($file_type, $entry_id = null) {
		$this->file_type = $file_type;
		if ($entry_id) {
			$this->setEntryId($entry_id);
			$this->doRead();
		}
	}


	public function doCreate() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		//auto-increment il_bibl_entry
		$this->setEntryId($ilDB->nextID('il_bibl_entry'));
		//table il_bibl_entry
		$ilDB->manipulate("INSERT INTO il_bibl_entry " . "(data_id, id, type) VALUES ("
		                  . $ilDB->quote($this->getBibliographicObjId(), "integer") . ","
		                  . // data_id
		                  $ilDB->quote($this->getEntryId(), "integer") . "," . // id
		                  $ilDB->quote($this->getType(), "text") . // type
		                  ")");
		//table il_bibl_attribute
		foreach ($this->getAttributes() as $attribute) {
			//auto-increment il_bibl_attribute
			$id = $ilDB->nextID('il_bibl_attribute');
			$ilDB->manipulate("INSERT INTO il_bibl_attribute "
			                  . "(entry_id, name, value, id) VALUES ("
			                  . $ilDB->quote($this->getEntryId(), "integer") . "," . // entry_id
			                  $ilDB->quote($attribute['name'], "text") . "," . // name
			                  $ilDB->quote($attribute['value'], "text") . "," . // value
			                  $ilDB->quote($id, "integer") . // id
			                  ")");
		}
	}


	public function doRead() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		//table il_bibl_entry
		$set = $ilDB->query("SELECT * FROM il_bibl_entry " . " WHERE id = "
		                    . $ilDB->quote($this->getEntryId(), "integer"));
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->setType($rec['type']);
		}
		$this->setAttributes($this->loadAttributes());
		$this->initOverviewHTML();
	}


	public function doUpdate() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		//table il_bibl_entry
		$ilDB->manipulate($up = "UPDATE il_bibl_entry SET " . " type = "
		                        . $ilDB->quote($this->getType(), "integer") . // type
		                        " WHERE id = " . $ilDB->quote($this->getEntryId(), "integer"));
		//table il_bibl_attribute
		foreach ($this->getAttributes() as $attribute) {
			$ilDB->manipulate($up = "UPDATE il_bibl_attribute SET " . " name = "
			                        . $ilDB->quote($attribute['name'], "integer") . "," . // name
			                        " value = " . $ilDB->quote($attribute['value'], "integer") . ","
			                        . // value
			                        " WHERE id = " . $ilDB->quote($attribute['id'], "integer"));
		}
	}


	public function doDelete() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$this->emptyCache();
		$this->deleteOptions();
		$ilDB->manipulate("DELETE FROM il_bibl_entry WHERE id = "
		                  . $ilDB->quote($this->getEntryId(), "integer"));
		$ilDB->manipulate("DELETE FROM il_bibl_attribute WHERE entry_id = "
		                  . $ilDB->quote($this->getEntryId(), "integer"));
	}


	/**
	 * Reads all the entrys attributes from database
	 *
	 * @return array Attributes of an entry
	 */
	protected function loadAttributes() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$all_attributes = array();
		//table il_bibl_attribute
		$set = $ilDB->query("SELECT * FROM il_bibl_attribute " . " WHERE entry_id = "
		                    . $ilDB->quote($this->getEntryId(), "integer"));
		while ($rec = $ilDB->fetchAssoc($set)) {
			$all_attributes[$rec['name']] = $rec['value'];
		}
		if ($this->file_type == "ris") {
			//for RIS-Files also add the type;
			$type = $this->getType();
		} else {
			$type = 'default';
		}
		$parsed_attributes = array();
		foreach ($all_attributes as $key => $value) {
			// surround links with <a href="">
			// Allowed signs in URL: a-z A-Z 0-9 . ? & _ / - ~ ! ' * ( ) + , : ; @ = $ # [ ] %
			$value = preg_replace('!(http)(s)?:\/\/[a-zA-Z0-9.?&_/\-~\!\'\*()+,:;@=$#\[\]%]+!', "<a href=\"\\0\" target=\"_blank\" rel=\"noopener\">\\0</a>", $value);
			$parsed_attributes[strtolower($this->file_type . '_' . $type . '_' . $key)] = $value;
		}

		return $parsed_attributes;
	}


	/**
	 * @param $attributes
	 */
	public function setAttributes($attributes) {
		$this->attributes = $attributes;
	}


	/**
	 * @return string[]
	 */
	public function getAttributes() {
		return $this->attributes;
	}


	public function initOverviewHTML() {
		$ilBiblOverviewGUI = new ilBiblOverviewGUI($this);
		$this->setOverview($ilBiblOverviewGUI->getHtml());
	}


	/**
	 * @return string
	 */
	public function getOverview() {
		return $this->overview;
	}


	/**
	 * @param string $overview
	 */
	public function setOverview($overview) {
		$this->overview = $overview;
	}


	/**
	 * @param int $bibliographic_obj_id
	 */
	public function setBibliographicObjId($bibliographic_obj_id) {
		$this->bibliographic_obj_id = $bibliographic_obj_id;
	}


	/**
	 * @return int
	 */
	public function getBibliographicObjId() {
		return $this->bibliographic_obj_id;
	}


	/**
	 * @param int $entry_id
	 */
	public function setEntryId($entry_id) {
		$this->entry_id = $entry_id;
	}


	/**
	 * @return int
	 */
	public function getEntryId() {
		return $this->entry_id;
	}


	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}


	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @return string
	 */
	public function getFileType() {
		return $this->file_type;
	}


	/**
	 * @param string $file_type
	 */
	public function setFileType($file_type) {
		$this->file_type = $file_type;
	}


	/**
	 * Read all entries from the database
	 *
	 * @param $object_id
	 *
	 * @return array
	 */
	static function getAllEntries($object_id) {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$entries = array();
		$set = $ilDB->query("SELECT id FROM il_bibl_entry " . " WHERE data_id = "
		                    . $ilDB->quote($object_id, "integer"));
		while ($rec = $ilDB->fetchAssoc($set)) {
			$entries[]['entry_id'] = $rec['id'];
		}

		return $entries;
	}
}
