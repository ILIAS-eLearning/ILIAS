<?php
require_once('class.arConnector.php');
require_once(dirname(__FILE__) . '/../Exception/class.arException.php');

/**
 * Class arConnectorDB
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class arConnectorDB extends arConnector {

	/**
	 * @return ilDB
	 */
	protected function returnDB() {
		global $ilDB;

		return $ilDB;
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public function checkConnection(ActiveRecord $ar) {
		return is_object($this->returnDB());
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return mixed
	 */
	public function nextID(ActiveRecord $ar) {
		return $this->returnDB()->nextId($ar::returnDbTableName());
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $fields
	 *
	 * @return bool
	 */
	public function installDatabase(ActiveRecord $ar, $fields) {
		$ilDB = $this->returnDB();
		$ilDB->createTable($ar::returnDbTableName(), $fields);
		$arFieldList = $ar->getArFieldList();
		if ($arFieldList->getPrimaryField()->getName()) {
			$ilDB->addPrimaryKey($ar::returnDbTableName(), array( $arFieldList->getPrimaryField()->getName() ));
		}
		if ($arFieldList->getPrimaryField()->getFieldType() === 'integer') {
			$ilDB->createSequence($ar::returnDbTableName());
		} else {
			/**
			 * @var ilDB $ilDB
			 */
			// $ilDB->addFulltextIndex() FSX TODO
		}

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public function updateDatabase(ActiveRecord $ar) {
		$ilDB = $this->returnDB();

		foreach ($ar->getArFieldList()->getFields() as $field) {
			if (! $ilDB->tableColumnExists($ar::returnDbTableName(), $field->getName())) {
				$ilDB->addTableColumn($ar::returnDbTableName(), $field->getName(), $field->getAttributesForConnector());
			}
		}

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public function resetDatabase(ActiveRecord $ar) {
		$ilDB = $this->returnDB();
		if ($ilDB->tableExists($ar::returnDbTableName())) {
			$ilDB->dropTable($ar::returnDbTableName());
		}
		$ar->installDB();

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function truncateDatabase(ActiveRecord $ar) {
		$ilDB = $this->returnDB();
		$query = 'TRUNCATE TABLE ' . $ar::returnDbTableName();
		$ilDB->query($query);
		if ($ilDB->tableExists($ar::returnDbTableName() . '_seq')) {
			$ilDB->dropSequence($ar::returnDbTableName());
			$ilDB->createSequence($ar::returnDbTableName());
		}
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return mixed
	 */
	public function checkTableExists(ActiveRecord $ar) {
		$ilDB = $this->returnDB();

		/**
		 * @TODO: This is the proper ILIAS approach on how to do this BUT: This is exteremely slow (listTables is used)! However, this is not the place to fix this issue. Report.
		 */

		return $ilDB->tableExists($ar::returnDbTableName());
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $field_name
	 *
	 * @return mixed
	 */
	public function checkFieldExists(ActiveRecord $ar, $field_name) {
		$ilDB = $this->returnDB();

		return $ilDB->tableColumnExists($ar::returnDbTableName(), $field_name);
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $field_name
	 *
	 * @return bool
	 * @throws arException
	 */
	public function removeField(ActiveRecord $ar, $field_name) {
		$ilDB = $this->returnDB();
		if ($ilDB->tableColumnExists($ar::returnDbTableName(), $field_name)) {
			//throw new arException($field_name, arException::COLUMN_DOES_NOT_EXIST);
		}
		if ($ilDB->tableColumnExists($ar::returnDbTableName(), $field_name)) {
			$ilDB->dropTableColumn($ar::returnDbTableName(), $field_name);

			return true;
		}
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $old_name
	 * @param              $new_name
	 *
	 * @return bool
	 * @throws arException
	 */
	public function renameField(ActiveRecord $ar, $old_name, $new_name) {
		$ilDB = $this->returnDB();
		if ($ilDB->tableColumnExists($ar::returnDbTableName(), $old_name)) {
			//throw new arException($old_name, arException::COLUMN_DOES_NOT_EXIST);

			if (! $ilDB->tableColumnExists($ar::returnDbTableName(), $new_name)) {
				//throw new arException($new_name, arException::COLUMN_DOES_ALREADY_EXIST);
				$ilDB->renameTableColumn($ar::returnDbTableName(), $old_name, $new_name);
			}
		}

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function create(ActiveRecord $ar) {
		$ilDB = $this->returnDB();
		$ilDB->insert($ar::returnDbTableName(), $ar->getArrayForConnector());
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return array
	 */
	public function read(ActiveRecord $ar) {
		$ilDB = $this->returnDB();

		$query = 'SELECT * FROM ' . $ar::returnDbTableName() . ' ' . ' WHERE ' . arFieldCache::getPrimaryFieldName($ar)
			. ' = ' . $ilDB->quote($ar->getPrimaryFieldValue(), arFieldCache::getPrimaryFieldType($ar));

		$set = $ilDB->query($query);
		$records = array();
		while ($rec = $ilDB->fetchObject($set)) {
			$records[] = $rec;
		}

		return $records;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function update(ActiveRecord $ar) {
		$ilDB = $this->returnDB();

		$ilDB->update($ar::returnDbTableName(), $ar->getArrayForConnector(), array(
			arFieldCache::getPrimaryFieldName($ar) => array(
				arFieldCache::getPrimaryFieldType($ar),
				$ar->getPrimaryFieldValue()
			),
		));
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function delete(ActiveRecord $ar) {
		$ilDB = $this->returnDB();

		$ilDB->manipulate('DELETE FROM ' . $ar::returnDbTableName() . ' WHERE ' . arFieldCache::getPrimaryFieldName($ar) . ' = '
			. $ilDB->quote($ar->getPrimaryFieldValue(), arFieldCache::getPrimaryFieldType($ar)));
	}


	/**
	 * @param ActiveRecordList $arl
	 *
	 * @internal param $q
	 *
	 * @return array
	 */
	public function readSet(ActiveRecordList $arl) {
		$ilDB = $this->returnDB();
		$set = $ilDB->query(self::buildQuery($arl));
		$records = array();
		while ($rec = $ilDB->fetchAssoc($set)) {
			$records[] = $rec;
		}

		return $records;
	}


	/**
	 * @param ActiveRecordList $arl
	 *
	 * @return int
	 */
	public function affectedRows(ActiveRecordList $arl) {
		$ilDB = $this->returnDB();
		$q = self::buildQuery($arl);

		$set = $ilDB->query($q);

		return $ilDB->numRows($set);
	}


	protected function buildNewQuery() {
	}


	/**
	 * @param ActiveRecordList $arl
	 *
	 * @return mixed|string
	 */
	protected function buildQuery(ActiveRecordList $arl) {
		$ilDB = $this->returnDB();
		$ar = $arl->getAR();
		$table_name = $ar::returnDbTableName();
		$arFieldList = $ar->getArFieldList();

		$q = 'SELECT * FROM ' . $table_name;
		// JOINS
		foreach ($arl->getJoins() as $join_table_name => $on) {
			$q .= ' JOIN ' . $join_table_name . ' on ' . $table_name . '.' . key($on) . ' = ' . $join_table_name . '.' . current($on);
		}
		if (count($arl->getWhere()) OR count($arl->getStringWheres())) {
			$q .= ' WHERE ';
		}
		foreach ($arl->getStringWheres() as $str) {
			$q .= $str . ' AND ';
		}
		foreach ($arl->getWhere() as $w) {
			$field = $w['fieldname'];
			$value = $w['value'];
			$operator = ' ' . $w['operator'] . ' ';
			$fieldType = $arFieldList->getFieldByName($field)->getFieldType();
			if (is_array($value)) {
				$q .= $ilDB->in($field, $value, false, $fieldType) . ' AND ';
			} else {
				switch ($fieldType) {
					case 'integer':
					case 'float':
					case 'timestamp':
					case 'time':
					case 'date':
						$q .= $field . $operator . $ilDB->quote($value, $fieldType) . ' AND ';
						break;
					case 'text':
					case 'clob':
					default:
						$q .= $field . $operator . $ilDB->quote($value, $fieldType) . ' AND ';
						break;
				}
			}
		}
		$q = str_ireplace('  ', ' ', $q);
		if (count($arl->getWhere()) OR count($arl->getStringWheres())) {
			$q = substr($q, 0, - 4);
		}
		if ($arl->getOrderBy() AND $arFieldList->isField($arl->getOrderBy())) {
			$q .= ' ORDER BY ' . $arl->getOrderBy() . ' ' . $arl->getOrderDirection();
		}
		if ($arl->getStart() !== NULL AND $arl->getEnd() !== NULL) {
			$q .= ' LIMIT ' . $arl->getStart() . ', ' . $arl->getEnd();
		}
		if ($arl->getDebug()) {
			var_dump($q); // FSX
		}
		$arl->setLastQuery($q);

		return $q;
	}
}

?>
