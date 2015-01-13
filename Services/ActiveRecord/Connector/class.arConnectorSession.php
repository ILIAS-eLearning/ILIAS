<?php
require_once('class.arConnector.php');
require_once(dirname(__FILE__) . '/../Exception/class.arException.php');

/**
 * Class arConnectorSession
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arConnectorSession extends arConnector {

	const AR_CONNECTOR_SESSION = 'arConnectorSession';


	public static function resetSession() {
		$_SESSION[self::AR_CONNECTOR_SESSION] = array();
	}


	/**
	 * @return array
	 */
	public static function getSession() {
		if (!$_SESSION[self::AR_CONNECTOR_SESSION]) {
			self::resetSession();
		}

		return $_SESSION[self::AR_CONNECTOR_SESSION];
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return array
	 */
	public static function getSessionForActiveRecord(ActiveRecord $ar) {
		$session = self::getSession();
		$ar_session = $session[$ar::returnDbTableName()];
		if (!is_array($ar_session)) {
			$ar_session = array();
		}

		return $ar_session;
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public function checkConnection(ActiveRecord $ar) {
		return is_array(self::getSession());
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return mixed
	 */
	public function nextID(ActiveRecord $ar) {
		return count(self::getSessionForActiveRecord($ar)) + 1;
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $fields
	 *
	 * @return bool
	 */
	public function installDatabase(ActiveRecord $ar, $fields) {
		return self::resetDatabase($ar);
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public function updateDatabase(ActiveRecord $ar) {
		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public function resetDatabase(ActiveRecord $ar) {
		$_SESSION[self::AR_CONNECTOR_SESSION][$ar::returnDbTableName()] = array();

		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return bool
	 */
	public function truncateDatabase(ActiveRecord $ar) {
		return self::resetDatabase($ar);
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return mixed
	 */
	public function checkTableExists(ActiveRecord $ar) {
		return is_array(self::getSessionForActiveRecord($ar));
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $field_name
	 *
	 * @return mixed
	 */
	public function checkFieldExists(ActiveRecord $ar, $field_name) {
		$session = self::getSessionForActiveRecord($ar);

		return array_key_exists($field_name, $session[0]);
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $field_name
	 *
	 * @return bool
	 * @throws arException
	 */
	public function removeField(ActiveRecord $ar, $field_name) {
		return true;
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
		return true;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function create(ActiveRecord $ar) {
		$_SESSION[self::AR_CONNECTOR_SESSION][$ar::returnDbTableName()][$ar->getPrimaryFieldValue()] = $ar->__asStdClass();
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return array
	 */
	public function read(ActiveRecord $ar) {
		$session = self::getSessionForActiveRecord($ar);

		return array( $session[$ar->getPrimaryFieldValue()] );
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function update(ActiveRecord $ar) {
		self::create($ar);
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function delete(ActiveRecord $ar) {
		unset($_SESSION[self::AR_CONNECTOR_SESSION][$ar::returnDbTableName()][$ar->getPrimaryFieldValue()]);
	}


	/**
	 * @param ActiveRecordList $arl
	 *
	 * @internal param $q
	 *
	 * @return array
	 */
	public function readSet(ActiveRecordList $arl) {
		$session = self::getSessionForActiveRecord($arl->getAR());
		foreach ($session as $i => $s) {
			$session[$i] = (array)$s;
		}
		foreach ($arl->getArWhereCollection()->getWheres() as $w) {
			$fieldname = $w->getFieldname();
			$v = $w->getValue();
			$operator = $w->getOperator();

			foreach ($session as $i => $s) {
				$session[$i] = (array)$s;
				switch ($operator) {
					case '=':
						if ($s[$fieldname] != $v) {
							unset($session[$i]);
						}
						break;
				}
			}
		}

		return $session;
	}


	/**
	 * @param ActiveRecordList $arl
	 *
	 * @return int
	 */
	public function affectedRows(ActiveRecordList $arl) {
		return count($this->readSet($arl));
	}


	/**
	 * @param $value
	 * @param $type
	 *
	 * @return string
	 */
	public function quote($value, $type) {
		return $value;
	}


	/**
	 * @param ActiveRecord $ar
	 */
	public function updateIndices(ActiveRecord $ar) {
		// TODO: Implement updateIndices() method.
	}
}

?>
