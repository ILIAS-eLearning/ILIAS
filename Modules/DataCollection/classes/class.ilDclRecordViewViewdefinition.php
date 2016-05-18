<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclRecordViewViewdefinition extends ilPageObject {

	/**
	 * @var bool
	 */
	protected $active = false;
	/**
	 * @var int
	 */
	protected $table_id;
	/**
	 * @var int
	 */
	protected $type = 0;        // [int]  0 = recordview
	/**
	 * @var int
	 */
	protected $formtype = 0;    // [int]  0 = copage
	/**
	 * @var array Cache record views per table-id, key=table-id, value=view definition id
	 */
	protected static $record_view_cache = array();
	/**
	 * @var ilDclRecordViewViewdefinition[]
	 */
	protected static $instances = array();


	/**
	 * @param $key
	 *
	 * @return ilDclRecordViewViewdefinition
	 */
	public static function getInstance($key) {
		self::$instances[$key] = new self($key);

		return self::$instances[$key];
	}


	/**
	 * @param $table_id
	 *
	 * @return bool
	 */
	public function removeDclView($table_id) {
		if (! $table_id) {
			return false;
		}
		global $ilDB;

		$query = "DELETE FROM il_dcl_view WHERE table_id = " . $table_id . " AND type = " . $ilDB->quote(0, "integer") . " AND formtype = "
			. $ilDB->quote(0, "integer");
		$ilDB->manipulate($query);

		return true;
	}


	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType() {
		return "dclf";
	}


	/**
	 * Set Table ID
	 *
	 * @param int $a_id
	 */
	public function setTableId($a_id) {
		$this->table_id = $a_id;
	}


	/**
	 * Get Table ID
	 *
	 * @return int
	 */
	public function getTableId() {
		return $this->table_id;
	}


	/**
	 * Get type
	 *
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * Get Formtype
	 *
	 * @return int
	 */
	public function getFormtype() {
		return $this->formtype;
	}


	/**
	 * Read Viewdefinition
	 */
	public function doRead() {
		global $ilDB;

		$query = "SELECT * FROM il_dcl_view WHERE table_id = " . $ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setActive(false);

		$this->setTableId($rec["table_id"]);
		$this->type = $rec["type"];
		$this->formtype = $rec["formtype"];
	}


	/**
	 * Create new Viewdefinition
	 */
	public function create($prevent_page_creation = false) {
		global $ilDB;
		$this->setActive(false);
		$id = $ilDB->nextId("il_dcl_view");
		$this->setId($id);

		$query = "INSERT INTO il_dcl_view (" . "id" . ", table_id" . ", type" . ", formtype" . " ) VALUES (" . $ilDB->quote($this->getId(), "integer")
			. "," . $ilDB->quote($this->getTableId(), "integer") . "," . $ilDB->quote($this->getType(), "integer") . ","
			. $ilDB->quote($this->getFormtype(), "integer") . ")";
		$ilDB->manipulate($query);

		if (! $prevent_page_creation) {
			parent::create();
		}
	}


	/**
	 * Update Viewdefinition
	 *
	 * @param bool $a_validate
	 * @param bool $a_no_history
	 *
	 * @return boolean
	 */
	public function update($a_validate = true, $a_no_history = false) {
		//TODO
		//Page-Object updaten
		//Es wäre auch möglich direkt in der GUI-Klasse ilPageObject aufzurufen. Falls wir aber bei doCreate, 
		//das Page-Object anlegen, fänd ich es sinnvoll, wenn wir auch hier das PageObject updaten würden.
		//Andernfalls sämtliche Page-Object-Methoden in der GUI-Klasse aufrufen.

		parent::update($a_validate, $a_no_history);

		return true;
	}


	/**
	 * Get view definition id by table id
	 *
	 * In the moment we have only one View-Viewdefinition per Table
	 *
	 * @param int $a_table_id
	 *
	 * @return inte
	 */
	public static function getIdByTableId($a_table_id) {
		if (! isset(self::$record_view_cache[$a_table_id])) {
			global $ilDB;
			//FIXME die werte bei type und formtype sollten vom constructor genommen werden
			$query = "SELECT id FROM il_dcl_view" . " WHERE table_id = " . $ilDB->quote($a_table_id, "integer") . " AND type = "
				. $ilDB->quote(0, "integer") . " and formtype = " . $ilDB->quote(0, "integer");
			$set = $ilDB->query($query);
			$row = $ilDB->fetchObject($set);

			self::$record_view_cache[$a_table_id] = $row->id;
		}

		return self::$record_view_cache[$a_table_id];
	}


	/**
	 * @param $table_id
	 *
	 * @return ilDclRecordViewViewdefinition
	 */
	public static function getInstanceByTableId($table_id) {
		$id = self::getIdByTableId($table_id);

		return self::getInstance($id);
	}


	/**
	 * Get all placeholders for table id
	 *
	 * @param int  $a_table_id
	 * @param bool $a_verbose
	 *
	 * @return array
	 */
	public static function getAvailablePlaceholders($a_table_id, $a_verbose = false) {
		$all = array();

		require_once("./Modules/DataCollection/classes/class.ilDclTable.php");
		$objTable = ilDclCache::getTableCache($a_table_id);
		$fields = $objTable->getRecordFields();
		$standardFields = $objTable->getStandardFields();

		foreach ($fields as $field) {

			if (! $a_verbose) {
				$all[] = "[" . $field->getTitle() . "]";

				if ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_REFERENCE) {
					$all[] = '[dclrefln field="' . $field->getTitle() . '"][/dclrefln]';
				}
				// SW 14.10.2015 http://www.ilias.de/mantis/view.php?id=16874
				//				if ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_ILIAS_REF) {
				//					$all[] = '[dcliln field="' . $field->getTitle() . '"][/dcliln]';
				//				}
			} else {
				$all["[" . $field->getTitle() . "]"] = $field;

				if ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_REFERENCE) {
					$all['[dclrefln field="' . $field->getTitle() . '"][/dclrefln]'] = $field;
				}
				// SW: 14.10.2015 http://www.ilias.de/mantis/view.php?id=16874
				//				if ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_ILIAS_REF) {
				//					$all['[dcliln field="' . $field->getTitle() . '"][/dcliln]'] = $field;
				//				}
			}
		}

		foreach ($standardFields as $field) {
			$all[] = "[" . $field->getId() . "]";
		}

		return $all;
	}
}

?>