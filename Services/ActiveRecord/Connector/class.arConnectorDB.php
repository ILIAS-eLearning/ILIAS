<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Connector/class.arConnector.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Exception/class.arException.php');

/**
 * Class arConnectorDB
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class arConnectorDB extends arConnector {

	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public static function checkConnection(ActiveRecord $ar) {
		global $ilDB;

		/**
		 * @var $ilDB ilDB
		 */

		return is_object($ilDB);
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $fields
	 *
	 * @return bool
	 */
	public static function installDatabase(ActiveRecord $ar, $fields) {
		global $ilDB;
		$ilDB->createTable($ar::returnDbTableName(), $fields);
		if ($ar::returnPrimaryFieldName()) {
			$ilDB->addPrimaryKey($ar::returnDbTableName(), array( $ar::returnPrimaryFieldName() ));
		}
		if ($ar::returnPrimaryFieldType() === 'integer') {
			$ilDB->createSequence($ar::returnDbTableName());
		}

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public static function updateDatabase(ActiveRecord $ar) {
		global $ilDB;
		/**
		 * @var ilDB $ilDB
		 */
		foreach ($ar::returnDbFields() as $field_name => $field_infos) {
			if (! $ilDB->tableColumnExists($ar::returnDbTableName(), $field_name)) {
				$ilDB->addTableColumn($ar::returnDbTableName(), $field_name, $ar->getDBAttributesOfField($field_infos));
			}
		}

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public static function resetDatabase(ActiveRecord $ar) {
		global $ilDB;
		if ($ilDB->tableExists($ar::returnDbTableName())) {
			$ilDB->dropTable($ar::returnDbTableName());
		}
		self::installDatabase($ar, $ar::returnDbFields());

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public static function truncateDatabase(ActiveRecord $ar) {
		global $ilDB;
		$query = 'TRUNCATE TABLE ' . $ar::returnDbTableName();
		$ilDB->query($query);
		if ($ilDB->tableExists($ar::returnDbTableName() . '_seq')) {
			$ilDB->dropSequence($ar::returnDbTableName());
			$ilDB->createSequence($ar::returnDbFields());
		}
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return mixed
	 */
	public static function checkTableExists(ActiveRecord $ar) {
		global $ilDB;

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
	public static function checkFieldExists(ActiveRecord $ar, $field_name) {
		global $ilDB;

		return $ilDB->tableColumnExists($ar::returnDbTableName(), $field_name);
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $field_name
	 *
	 * @return bool
	 * @throws arException
	 */
	public static function removeField(ActiveRecord $ar, $field_name) {
		global $ilDB;
		if (! $ilDB->tableColumnExists($ar::returnDbTableName(), $field_name)) {
			throw new arException($field_name, arException::COLUMN_DOES_NOT_EXIST);
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
	public static function renameField(ActiveRecord $ar, $old_name, $new_name) {
		global $ilDB;
		if ($ilDB->tableColumnExists($ar::returnDbTableName(), $new_name)) {
			throw new arException($new_name, arException::COLUMN_DOES_ALREADY_EXIST);
		}
		if (! $ilDB->tableColumnExists($ar::returnDbTableName(), $old_name)) {
			throw new arException($old_name, arException::COLUMN_DOES_NOT_EXIST);
		}
		$ilDB->renameTableColumn($ar::returnDbTableName(), $old_name, $new_name);

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public static function create(ActiveRecord $ar) {
		global $ilDB;
		$ilDB->insert($ar::returnDbTableName(), $ar->getArrayForDb());
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return array
	 */
	public static function read(ActiveRecord $ar) {
		global $ilDB;
		if ($ar::returnPrimaryFieldName() === 'id') {
			$query = 'SELECT * FROM ' . $ar::returnDbTableName() . ' '
				. ' WHERE id = ' . $ilDB->quote($ar->getId(), 'integer');
		} else {
			$query = 'SELECT * FROM ' . $ar::returnDbTableName() . ' ' . ' WHERE ' . $ar::returnPrimaryFieldName()
				. ' = ' . $ilDB->quote($ar->getPrimaryFieldValue(), $ar::returnPrimaryFieldType());
		}
		echo $query;

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
	public static function update(ActiveRecord $ar) {
		global $ilDB;
		if ($ar::returnPrimaryFieldName() === 'id') {
			$ilDB->update($ar::returnDbTableName(), $ar->getArrayForDb(), array(
				'id' => array(
					'integer',
					$ar->getId()
				),
			));
		} else {
			$ilDB->update($ar::returnDbTableName(), $ar->getArrayForDb(), array(
				$ar::returnPrimaryFieldName() => array(
					$ar::returnPrimaryFieldType(),
					$ar->getPrimaryFieldValue()
				),
			));
		}
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public static function delete(ActiveRecord $ar) {
		global $ilDB;
		if ($ar::returnPrimaryFieldName() === 'id') {
			$ilDB->manipulate('DELETE FROM ' . $ar::returnDbTableName() . ' WHERE id = '
				. $ilDB->quote($ar->getId(), 'integer'));
		} else { // TODO dies zur normalen Methode machen. prüfen
			$ilDB->manipulate('DELETE FROM ' . $ar::returnDbTableName() . ' WHERE ' . $ar::returnPrimaryFieldName()
				. ' = ' . $ilDB->quote($ar->getPrimaryFieldValue(), $ar::returnPrimaryFieldType()));
		}
	}


	/**
	 * @param ActiveRecordList $arl
	 *
	 * @internal param $q
	 *
	 * @return array
	 */
	public function readSet(ActiveRecordList $arl) {
		global $ilDB;
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
		global $ilDB;
		$q = self::buildQuery($arl);

		$set = $ilDB->query($q);

		return $ilDB->numRows($set);
	}


	/**
	 * @param ActiveRecordList $arl
	 *
	 * @return mixed|string
	 */
	protected static function buildQuery(ActiveRecordList $arl) {
		global $ilDB;
		$class_fields = call_user_func($arl->getClass() . '::returnDbFields');
		$table_name = call_user_func($arl->getClass() . '::returnDbTableName');
		$q = 'SELECT * FROM ' . $table_name;
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
			if (is_array($value)) {
				$q .= $ilDB->in($field, $value, false, $class_fields[$field]->db_type) . ' AND ';
			} else {
				switch ($class_fields[$field]->db_type) {
					case 'integer':
					case 'float':
					case 'timestamp':
					case 'time':
					case 'date':
						$q .= $field . $operator . $ilDB->quote($value, $class_fields[$field]->db_type) . ' AND ';
						break;
					case 'text':
					case 'clob':
					default:
						$q .= $field . $operator . $ilDB->quote($value, $class_fields[$field]->db_type) . ' AND ';
						break;
				}
			}
		}
		$q = str_ireplace('  ', ' ', $q);
		if (count($arl->getWhere()) OR count($arl->getStringWheres())) {
			$q = substr($q, 0, - 4);
		}
		if ($arl->get) {
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
