<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldModel.php';
include_once './Modules/DataCollection/classes/Fields/Base/class.ilDclDatatype.php';

/**
 * Class ilDclBaseFieldModel
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclStandardField extends ilDclBaseFieldModel
{
	/*
	 * doRead
	 */
	public function doRead()
	{
		global $DIC;
		$ilLog = $DIC['ilLog'];
		$message = "Standard fields cannot be read from DB";
		ilUtil::sendFailure($message);
		$ilLog->write("[ilDclStandardField] ".$message);
	}
	
	/*
	 * doCreate
	 */
	public function doCreate()
	{
		global $DIC;
		$ilLog = $DIC['ilLog'];
		$message = "Standard fields cannot be written to DB";
		ilUtil::sendFailure($message);
		$ilLog->write("[ilDclStandardField] ".$message);
	}
	
	/*
	 * doUpdate
	 */
	public function doUpdate()
	{
		$this->updateTableFieldSetting();
	}


	/**
	 * @param ilDclStandardField $original_record
	 */
	public function cloneStructure($original_record) {
		$this->setLocked($original_record->getLocked());
		$this->setOrder($original_record->getOrder());
		$this->setRequired($original_record->getRequired());
		$this->setUnique($original_record->isUnique());
		$this->setExportable($original_record->getExportable());

		$this->doUpdate();
	}
	
	/*
	 * getLocked
	 */
	public function getLocked()
	{
		return true;
	}
	
	/*
	 * _getStandardFieldsAsArray
	 */
	static function _getStandardFieldsAsArray()
	{

		//TODO: this isn't particularly pretty especially as $lng is used in the model. On the long run the standard fields should be refactored into "normal" fields.
		global $DIC;
		$lng = $DIC['lng'];
		$stdfields = array(
			array("id"=>"id", "title" => $lng->txt("dcl_id"), "description" => $lng->txt("dcl_id_description"), "datatype_id" => ilDclDatatype::INPUTFORMAT_NUMBER, "required" => true),
			array("id"=>"create_date", "title" => $lng->txt("dcl_creation_date"), "description" => $lng->txt("dcl_creation_date_description"), "datatype_id" => ilDclDatatype::INPUTFORMAT_DATETIME, "required" => true),
			array("id"=>"last_update", "title" => $lng->txt("dcl_last_update"), "description" => $lng->txt("dcl_last_update_description"), "datatype_id" => ilDclDatatype::INPUTFORMAT_DATETIME, "required" => true),
			array("id"=>"owner", "title" => $lng->txt("dcl_owner"), "description" => $lng->txt("dcl_owner_description"), "datatype_id" => ilDclDatatype::INPUTFORMAT_TEXT, "required" => true),
			array("id"=>"last_edit_by", "title" => $lng->txt("dcl_last_edited_by"), "description" => $lng->txt("dcl_last_edited_by_description"), "datatype_id" => ilDclDatatype::INPUTFORMAT_TEXT, "required" => true),
		    array('id' => 'comments', 'title' => $lng->txt('dcl_comments'), 'description' => $lng->txt('dcl_comments_desc'), 'datatype_id' => ilDclDatatype::INPUTFORMAT_NONE, 'required' => false),
        );
		return $stdfields;
	}
	
	/*
	 * _getStandardFields
	 */
	static function _getStandardFields($table_id)
	{
		$stdFields = array();
		foreach(self::_getStandardFieldsAsArray() as $array)
		{
			$array["table_id"] = $table_id;
			//$array["datatype_id"] = self::_getDatatypeForId($array["id"]);
			$field = new ilDclStandardField();
			$field->buildFromDBRecord($array);
			$stdFields[] = $field;
		}
		return $stdFields;
	}

	/**
	 * @return array all possible standardfield titles, in all languages (used for excel-import);
	 */
	static function _getAllStandardFieldTitles() {
		global $ilDB;
		$identifiers = '';
		foreach (array('dcl_id', 'dcl_creation_date', 'dcl_last_update', 'dcl_owner', 'dcl_last_edited_by', 'dcl_comments') as $id) {
			$identifiers .= $ilDB->quote($id, 'text') . ',';
		}
		$identifiers = rtrim($identifiers, ',');
		$sql = $ilDB->query('SELECT value FROM lng_data WHERE identifier IN (' . $identifiers . ')');
		$titles = array();
		while ($rec = $ilDB->fetchAssoc($sql)) {
			$titles[] = $rec['value'];
		}
		return $titles;
	}


	/*
	 * _isStandardField
	 */
	static function _isStandardField($field_id)
	{
		$return = false;
		foreach(self::_getStandardFieldsAsArray() as $field)
		{
			if($field["id"] == $field_id)
			{
				$return = true;
			}
		}
			
		return $return;
	}

	/**
	 * gives you the datatype id of a specified standard field.
	 * @param $id the id of the standardfield eg. "create_date"
	 */
	public static function _getDatatypeForId($id)
	{
        $datatype = null;
        foreach (self::_getStandardFieldsAsArray() as $fields_data) {
            if ($id == $fields_data['id']) {
                $datatype = $fields_data['datatype_id'];
                break;
            }
        }
        return $datatype;
	}

	/*
	 * isStandardField
	 */
	public function isStandardField()
	{
		return true;
	}
	
	/*
	 * isUnique
	 */
	public function isUnique()
	{
		return false;
	}


	/**
	 * Returns a query-object for building the record-loader-sql-query
	 *
	 * @param string $direction
	 * @param boolean $sort_by_status The specific sort object is a status field
	 *
	 * @return null|ilDclRecordQueryObject
	 */
	public function getRecordQuerySortObject($direction = "asc", $sort_by_status = false){
		$sql_obj = new ilDclRecordQueryObject();

		$join_str = "";
		if ($this->getId() == 'owner' || $this->getId() == 'last_edit_by') {
			$join_str = "LEFT JOIN usr_data AS sort_usr_data_{$this->getId()} ON (sort_usr_data_{$this->getId()}.usr_id = record.{$this->getId()})";
			$select_str = " sort_usr_data_{$this->getId()}.login AS field_{$this->getId()},";
		} else {
			$select_str = " record.{$this->getId()} AS field_{$this->getId()},";
		}

		$sql_obj->setSelectStatement($select_str);
		$sql_obj->setJoinStatement($join_str);

		if($this->getId() !== "comments") {
			$sql_obj->setOrderStatement("field_{$this->getId()} ".$direction);
		}

		return $sql_obj;
	}


	/**
	 * Returns a query-object for building the record-loader-sql-query
	 *
	 * @param string              $filter_value
	 * @param ilDclBaseFieldModel $sort_field
	 *
	 * @return ilDclRecordQueryObject|null
	 */
	public function getRecordQueryFilterObject($filter_value = "", ilDclBaseFieldModel $sort_field = NULL) {
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$where_additions = "";
		$join_str = "";
		if($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_TEXT) {
				$join_str =
					"INNER JOIN usr_data AS filter_usr_data_{$this->getId()} ON (filter_usr_data_{$this->getId()}.usr_id = record.{$this->getId()} AND filter_usr_data_{$this->getId()}.login LIKE "
					. $ilDB->quote("%$filter_value%", 'text') . ") ";

		} else if($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_NUMBER) {
			$from = (isset($filter_value['from'])) ? $filter_value['from'] : NULL;
			$to = (isset($filter_value['to'])) ? $filter_value['to'] : NULL;
			if (is_numeric($from)) {
				$where_additions .= " AND record.{$this->getId()} >= " . $ilDB->quote($from, 'integer');
			}
			if (is_numeric($to)) {
				$where_additions .= " AND record.{$this->getId()} <= " . $ilDB->quote($to, 'integer');
			}

		} else if ($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_DATETIME) {
			$date_from = (isset($filter_value['from']) && is_object($filter_value['from'])) ? $filter_value['from'] : NULL;
			$date_to = (isset($filter_value['to']) && is_object($filter_value['to'])) ? $filter_value['to'] : NULL;

			// db->quote(.. date) at some point invokes ilDate->_toString, which adds a <br /> to the string,
			// that's why strip_tags is used
			if ($date_from) {
				$where_additions .= " AND (record.{$this->getId()} >= " . strip_tags($ilDB->quote($date_from, 'date')) . ")";
			}
			if ($date_to) {
				$where_additions .= " AND (record.{$this->getId()} <= " . strip_tags($ilDB->quote($date_to, 'date')) . ")";
			}

		}

		$sql_obj = new ilDclRecordQueryObject();
		$sql_obj->setJoinStatement($join_str);
		$sql_obj->setWhereStatement($where_additions);
		return $sql_obj;
	}


	public function getSortField() {
		if($this->getId() == 'comments') {
			return 'n_comments';
		}
	}


	public function hasNumericSorting() {
		if($this->getId() == 'comments') {
			return true;
		}

		return parent::hasNumericSorting();
	}

	/**
	 * @return bool
	 */
	public function allowFilterInListView() {
		//comments are filterable if they are enabled in the tables settings
		return $this->id != 'comments' || ilDclCache::getTableCache($this->getTableId())->getPublicCommentsEnabled();
	}


	/**
	 * @param $records
	 */
	public function afterClone($records) {
		return; //0020762; afterClone of 'comments' leads to an error since comments has no datatype
	}
}

?>