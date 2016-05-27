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

	const PARENT_TYPE = 'dclf';

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
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType() {
		return self::PARENT_TYPE;
	}
	
	/**
	 * @param $table_id
	 *
	 * @return ilDclRecordViewViewdefinition
	 */
	public static function getInstanceByTableViewId($tableview_id) {
		$id = self::getIdByTableViewId($tableview_id);

		return self::getInstance($id);
	}

	public static function getIdByTableViewId($tableview_id){
		global $ilDB;
		$sql = $ilDB->query('SELECT id FROM page_object WHERE parent_id = ' . $ilDB->quote($tableview_id, 'text') .
			' AND parent_type = ' . $ilDB->quote('dclf', 'text'));
		if ($sql->numRows()) {
			return $ilDB->fetchObject($sql)->id;
		} else {
			
		}
	}
	/**
	 * Get all placeholders for table id
	 *
	 * @param int  $a_table_id
	 * @param bool $a_verbose
	 *
	 * @return array
	 */
	public function getAvailablePlaceholders() {
		$all = array();

		require_once("./Modules/DataCollection/classes/class.ilDclTable.php");
		require_once("./Modules/DataCollection/classes/TableView/class.ilDclTableView.php");
		$tableview = new ilDclTableView($this->id);
		$table_id = $tableview->getTableId();
		$objTable = ilDclCache::getTableCache($table_id);
		$fields = $objTable->getRecordFields();
		$standardFields = $objTable->getStandardFields();

		foreach ($fields as $field) {
			$all[] = "[" . $field->getTitle() . "]";

			if ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_REFERENCE) {
				$all[] = '[dclrefln field="' . $field->getTitle() . '"][/dclrefln]';
			}
			// SW 14.10.2015 http://www.ilias.de/mantis/view.php?id=16874
			//				if ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_ILIAS_REF) {
			//					$all[] = '[dcliln field="' . $field->getTitle() . '"][/dcliln]';
			//				}
		}

		foreach ($standardFields as $field) {
			$all[] = "[" . $field->getId() . "]";
		}

		return $all;
	}

	public static function exists($id)
	{
		return parent::_exists(self::PARENT_TYPE, $id);
	}
	
	public static function isActive($id)
	{
		return parent::_lookupActive($id, self::PARENT_TYPE);
	}
}

?>