<?php
require_once('class.arConnector.php');
require_once(dirname(__FILE__) . '/../Exception/class.arException.php');

/**
 * Class arConnectorDB
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
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
		return $this->returnDB()->nextId($ar->getConnectorContainerName());
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $fields
	 *
	 * @return bool
	 */
	public function installDatabase(ActiveRecord $ar, $fields) {
		$ilDB = $this->returnDB();
		$ilDB->createTable($ar->getConnectorContainerName(), $fields);
		$arFieldList = $ar->getArFieldList();
		if ($arFieldList->getPrimaryField()->getName()) {
			$ilDB->addPrimaryKey($ar->getConnectorContainerName(), array( $arFieldList->getPrimaryField()->getName() ));
		}
		if ($arFieldList->getPrimaryField()->getFieldType() === 'integer' AND $arFieldList->getPrimaryField()->getSequence() === 'true') {
			$ilDB->createSequence($ar->getConnectorContainerName());
		}
		$this->updateIndices($ar);

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function updateIndices(ActiveRecord $ar) {
		$ilDB = $this->returnDB();
		$arFieldList = $ar->getArFieldList();
		$res = $ilDB->query('SHOW INDEX FROM ' . $ar->getConnectorContainerName());
		$existing_indices = array();
		while ($rec = $ilDB->fetchObject($res)) {
			$existing_indices[] = $rec->column_name;
		}
		foreach ($arFieldList->getFields() as $i => $arField) {
			if ($arField->getIndex() === 'true') {
				if (!in_array($arField->getName(), $existing_indices)) {
					$ilDB->addIndex($ar->getConnectorContainerName(), array( $arField->getName() ), 'i' . $i);
				}
			}
		}
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public function updateDatabase(ActiveRecord $ar) {
		$ilDB = $this->returnDB();
		foreach ($ar->getArFieldList()->getFields() as $field) {
			if (!$ilDB->tableColumnExists($ar->getConnectorContainerName(), $field->getName())) {
				$ilDB->addTableColumn($ar->getConnectorContainerName(), $field->getName(), $field->getAttributesForConnector());
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
		if ($ilDB->tableExists($ar->getConnectorContainerName())) {
			$ilDB->dropTable($ar->getConnectorContainerName());
		}
		$ar->installDB();

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function truncateDatabase(ActiveRecord $ar) {
		$ilDB = $this->returnDB();
		$query = 'TRUNCATE TABLE ' . $ar->getConnectorContainerName();
		$ilDB->query($query);
		if ($ilDB->tableExists($ar->getConnectorContainerName() . '_seq')) {
			$ilDB->dropSequence($ar->getConnectorContainerName());
			$ilDB->createSequence($ar->getConnectorContainerName());
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

		return $ilDB->tableExists($ar->getConnectorContainerName());
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $field_name
	 *
	 * @return mixed
	 */
	public function checkFieldExists(ActiveRecord $ar, $field_name) {
		$ilDB = $this->returnDB();

		return $ilDB->tableColumnExists($ar->getConnectorContainerName(), $field_name);
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
		if ($ilDB->tableColumnExists($ar->getConnectorContainerName(), $field_name)) {
			//throw new arException($field_name, arException::COLUMN_DOES_NOT_EXIST);
		}
		if ($ilDB->tableColumnExists($ar->getConnectorContainerName(), $field_name)) {
			$ilDB->dropTableColumn($ar->getConnectorContainerName(), $field_name);

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
		if ($ilDB->tableColumnExists($ar->getConnectorContainerName(), $old_name)) {
			//throw new arException($old_name, arException::COLUMN_DOES_NOT_EXIST);

			if (!$ilDB->tableColumnExists($ar->getConnectorContainerName(), $new_name)) {
				//throw new arException($new_name, arException::COLUMN_DOES_ALREADY_EXIST);
				$ilDB->renameTableColumn($ar->getConnectorContainerName(), $old_name, $new_name);
			}
		}

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function create(ActiveRecord $ar) {
		$ilDB = $this->returnDB();
		$ilDB->insert($ar->getConnectorContainerName(), $ar->getArrayForConnector());
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return array
	 */
	public function read(ActiveRecord $ar) {
		$ilDB = $this->returnDB();

		$query = 'SELECT * FROM ' . $ar->getConnectorContainerName() . ' ' . ' WHERE ' . arFieldCache::getPrimaryFieldName($ar) . ' = '
			. $ilDB->quote($ar->getPrimaryFieldValue(), arFieldCache::getPrimaryFieldType($ar));

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

		$ilDB->update($ar->getConnectorContainerName(), $ar->getArrayForConnector(), array(
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

		$ilDB->manipulate('DELETE FROM ' . $ar->getConnectorContainerName() . ' WHERE ' . arFieldCache::getPrimaryFieldName($ar) . ' = '
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


	/**
	 * @param ActiveRecordList $arl
	 *
	 * @return mixed|string
	 */
	protected function buildQuery(ActiveRecordList $arl) {
		// SELECTS
		$q = $arl->getArSelectCollection()->asSQLStatement();
		// Concats
		$q .= $arl->getArConcatCollection()->asSQLStatement();
		$q .= ' FROM '.$arl->getAR()->getConnectorContainerName();
		// JOINS
		$q .= $arl->getArJoinCollection()->asSQLStatement();
		// WHERE
		$q .= $arl->getArWhereCollection()->asSQLStatement();
		// ORDER
		$q .= $arl->getArOrderCollection()->asSQLStatement();
		// LIMIT
		$q .= $arl->getArLimitCollection()->asSQLStatement();

		//TODO: using template in the model.
		if ($arl->getDebug()) {
			global $tpl;
			if ($tpl instanceof ilTemplate) {
				ilUtil::sendInfo($q);
			} else {
				var_dump($q); // FSX
			}
		}
		$arl->setLastQuery($q);

		return $q;
	}


	/**
	 * @param $value
	 * @param $type
	 *
	 * @return string
	 */
	public function quote($value, $type) {
		$ilDB = $this->returnDB();

		return $ilDB->quote($value, $type);
	}
}

?>
