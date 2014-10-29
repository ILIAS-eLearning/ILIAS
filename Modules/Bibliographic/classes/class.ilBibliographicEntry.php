<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjBibliographic
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
	 * @var ilBibliographicEntry[]
	 */
	protected static $instances = array();


	/**
	 * @param      $file_type
	 * @param null $entry_id
	 *
	 * @return ilBibliographicEntry
	 */
	public static function getInstance($file_type, $entry_id = NULL) {
		if (!$entry_id) {
			return new self($file_type, $entry_id);
		}

		if (!isset(self::$instances[$entry_id])) {
			self::$instances[$entry_id] = new self($file_type, $entry_id);
		}

		return self::$instances[$entry_id];
	}


	/**
	 * @param      $file_type
	 * @param null $entry_id
	 */
	protected function __construct($file_type, $entry_id = NULL) {
		$this->file_type = $file_type;
		if ($entry_id) {
			$this->setEntryId($entry_id);
			$this->doRead();
		}
	}


	/**
	 * Create object
	 *
	 *
	 */
	function doCreate() {
		global $ilDB;
		//auto-increment il_bibl_entry
		$this->setEntryId($ilDB->nextID('il_bibl_entry'));
		//table il_bibl_entry
		$ilDB->manipulate("INSERT INTO il_bibl_entry " . "(data_id, id, type) VALUES (" . $ilDB->quote($this->getBibliographicObjId(), "integer")
			. "," . // data_id
			$ilDB->quote($this->getEntryId(), "integer") . "," . // id
			$ilDB->quote($this->getType(), "text") . // type
			")");
		//table il_bibl_attribute
		foreach ($this->getAttributes() as $attribute) {
			//auto-increment il_bibl_attribute
			$id = $ilDB->nextID('il_bibl_attribute');
			$ilDB->manipulate("INSERT INTO il_bibl_attribute " . "(entry_id, name, value, id) VALUES (" . $ilDB->quote($this->getEntryId(), "integer")
				. "," . // entry_id
				$ilDB->quote($attribute['name'], "text") . "," . // name
				$ilDB->quote($attribute['value'], "text") . "," . // value
				$ilDB->quote($id, "integer") . // id
				")");
		}
	}


	/**
	 * Read data from database tables il_bibl_entry and il_bibl_attribute
	 */
	function doRead() {
		global $ilDB;
		//table il_bibl_entry
		$set = $ilDB->query("SELECT * FROM il_bibl_entry " . " WHERE id = " . $ilDB->quote($this->getEntryId(), "integer"));
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->setType($rec['type']);
		}
		$this->setAttributes($this->loadAttributes());
		$this->setOverwiew();
	}


	/**
	 * Update database tables il_bibl_entry and il_bibl_attribute
	 */
	function doUpdate() {
		global $ilDB;
		//table il_bibl_entry
		$ilDB->manipulate($up = "UPDATE il_bibl_entry SET " . " type = " . $ilDB->quote($this->getType(), "integer") . // type
			" WHERE id = " . $ilDB->quote($this->getEntryId(), "integer"));
		//table il_bibl_attribute
		foreach ($this->getAttributes() as $attribute) {
			$ilDB->manipulate($up = "UPDATE il_bibl_attribute SET " . " name = " . $ilDB->quote($attribute['name'], "integer") . "," . // name
				" value = " . $ilDB->quote($attribute['value'], "integer") . "," . // value
				" WHERE id = " . $ilDB->quote($attribute['id'], "integer"));
		}
	}


	/**
	 * Delete data from db
	 */
	function doDelete() {
		global $ilDB;
		$this->emptyCache();
		$this->deleteOptions();
		$ilDB->manipulate("DELETE FROM il_bibl_entry WHERE id = " . $ilDB->quote($this->getEntryId(), "integer"));
		$ilDB->manipulate("DELETE FROM il_bibl_attribute WHERE entry_id = " . $ilDB->quote($this->getEntryId(), "integer"));
	}


	/**
	 * Reads all the entrys attributes from database
	 *
	 * @return array Attributes of an entry
	 */
	protected function loadAttributes() {
		global $ilDB;
		$all_attributes = array();
		//table il_bibl_attribute
		$set = $ilDB->query("SELECT * FROM il_bibl_attribute " . " WHERE entry_id = " . $ilDB->quote($this->getEntryId(), "integer"));
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
			$value = preg_replace('!(http)(s)?:\/\/[a-zA-Z0-9.?&_/\-~\!\'\*()+,:;@=$#\[\]%]+!', "<a href=\"\\0\" target=\"_blank\">\\0</a>", $value);
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


	public function setOverwiew() {
		$attributes = $this->getAttributes();
		//Get the model which declares which attributes to show in the overview table and how to show them
		//example for overviewModels: $overviewModels['bib']['default'] => "[<strong>|bib_default_author|</strong>: ][|bib_default_title|. ]<Emph>[|bib_default_publisher|][, |bib_default_year|][, |bib_default_address|].</Emph>"
		$overviewModels = ilObjBibliographic::getAllOverviewModels();
		//get design for specific entry type or get filetypes default design if type is not specified
		$entryType = $this->getType();
		//if there is no model for the specific entrytype (book, article, ....) the entry overview will be structured by the default entrytype from the given filetype (ris, bib, ...)
		if (!$overviewModels[$this->file_type][$entryType]) {
			$entryType = 'default';
		}
		$single_entry = $overviewModels[$this->file_type][$entryType];
		//split the model into single attributes (which begin and end with a bracket, eg [|bib_default_title|. ] )
		//such values are saved in $placeholders[0] while the same values but whithout brackets are saved in $placeholders[1] (eg |bib_default_title|.  )
		preg_match_all('/\[(.*?)\]/', $single_entry, $placeholders);
		foreach ($placeholders[1] as $key => $placeholder) {
			//cut a moedel attribute like |bib_default_title|. in three pieces while $cuts[1] is the attribute key for the actual value and $cuts[0] is what comes before respectively $cuts[2] is what comes after the value if it is not empty.
			$cuts = explode('|', $placeholder);
			//if attribute key does not exist, because it comes from the default entry (e.g. ris_default_u2), we replace 'default' with the entrys type (e.g. ris_book_u2)
			if (!$attributes[$cuts[1]]) {
				$attribute_elements = explode('_', $cuts[1]);
				$attribute_elements[1] = strtolower($this->getType());
				$cuts[1] = implode('_', $attribute_elements);
			}
			if ($attributes[$cuts[1]]) {
				//if the attribute for the attribute key exists, replace one attribute in the overview text line of a single entry with its actual value and the text before and after the value given by the model
				$single_entry = str_replace($placeholders[0][$key], $cuts[0] . $attributes[$cuts[1]] . $cuts[2], $single_entry);
				// replace the <emph> tags with a span, in order to make text italic by css
				do {
					$first_sign_after_begin_emph_tag = strpos(strtolower($single_entry), '<emph>') + 6;
					$last_sign_after_end_emph_tag = strpos(strtolower($single_entry), '</emph>');
					$italic_text_length = $last_sign_after_end_emph_tag - $first_sign_after_begin_emph_tag;
					//would not be true if there is no <emph> tag left
					if ($last_sign_after_end_emph_tag) {
						$italic_text = substr($single_entry, $first_sign_after_begin_emph_tag, $italic_text_length);
						//parse
						$it_tpl = new ilTemplate("tpl.bibliographic_italicizer.html", true, true, "Modules/Bibliographic");
						$it_tpl->setCurrentBlock("italic_section");
						$it_tpl->setVariable('ITALIC_STRING', $italic_text);
						$it_tpl->parseCurrentBlock();
						//replace the emph tags and the text between with the parsed text from il_tpl
						$text_before_emph_tag = substr($single_entry, 0, $first_sign_after_begin_emph_tag - 6);
						$text_after_emph_tag = substr($single_entry, $last_sign_after_end_emph_tag + 7);
						$single_entry = $text_before_emph_tag . $it_tpl->get() . $text_after_emph_tag;
					}
				} while ($last_sign_after_end_emph_tag);
			} else {
				//if the attribute for the attribute key does not exist, just remove this attribute-key from the overview text line of a single entry
				$single_entry = str_replace($placeholders[0][$key], '', $single_entry);
			}
		}
		$this->Overwiew = $single_entry;
	}


	/**
	 * @return string
	 */
	public function getOverwiew() {
		return $this->Overwiew;
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
	 * Read all entries from the database
	 *
	 * @param $object_id
	 *
	 * @return array
	 */
	static function getAllEntries($object_id) {
		global $ilDB;
		$entries = array();
		$set = $ilDB->query("SELECT id FROM il_bibl_entry " . " WHERE data_id = " . $ilDB->quote($object_id, "integer"));
		while ($rec = $ilDB->fetchAssoc($set)) {
			$entries[]['entry_id'] = $rec['id'];
		}

		return $entries;
	}
}
