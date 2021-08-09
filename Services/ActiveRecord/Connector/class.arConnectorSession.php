<?php

/**
 * Class arConnectorSession
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arConnectorSession extends arConnector
{
    const AR_CONNECTOR_SESSION = 'arConnectorSession';

    public static function resetSession() : void
    {
        $_SESSION[self::AR_CONNECTOR_SESSION] = array();
    }

    /**
     * @return mixed[]
     */
    public static function getSession() : array
    {
        if (!$_SESSION[self::AR_CONNECTOR_SESSION]) {
            self::resetSession();
        }

        return $_SESSION[self::AR_CONNECTOR_SESSION];
    }

    /**
     * @return mixed[]
     */
    public static function getSessionForActiveRecord(ActiveRecord $ar) : array
    {
        $session = self::getSession();
        $ar_session = $session[$ar::returnDbTableName()];
        if (!is_array($ar_session)) {
            $ar_session = array();
        }

        return $ar_session;
    }

    public function checkConnection(ActiveRecord $ar) : bool
    {
        return is_array(self::getSession());
    }

    /**
     * @return mixed
     */
    public function nextID(ActiveRecord $ar) : int
    {
        return count(self::getSessionForActiveRecord($ar)) + 1;
    }

    /**
     * @param              $fields
     */
    public function installDatabase(ActiveRecord $ar, $fields) : bool
    {
        return $this->resetDatabase($ar);
    }

    public function updateDatabase(ActiveRecord $ar) : bool
    {
        return true;
    }

    public function resetDatabase(ActiveRecord $ar) : bool
    {
        $_SESSION[self::AR_CONNECTOR_SESSION][$ar::returnDbTableName()] = array();

        return true;
    }

    public function truncateDatabase(ActiveRecord $ar) : bool
    {
        return $this->resetDatabase($ar);
    }

    /**
     * @return mixed
     */
    public function checkTableExists(ActiveRecord $ar) : bool
    {
        return is_array(self::getSessionForActiveRecord($ar));
    }

    /**
     * @param              $field_name
     * @return mixed
     */
    public function checkFieldExists(ActiveRecord $ar, $field_name) : bool
    {
        $session = self::getSessionForActiveRecord($ar);

        return array_key_exists($field_name, $session[0]);
    }

    /**
     * @param              $field_name
     * @throws arException
     */
    public function removeField(ActiveRecord $ar, $field_name) : bool
    {
        return true;
    }

    /**
     * @param              $old_name
     * @param              $new_name
     * @throws arException
     */
    public function renameField(ActiveRecord $ar, $old_name, $new_name) : bool
    {
        return true;
    }

    public function create(ActiveRecord $ar) : void
    {
        $_SESSION[self::AR_CONNECTOR_SESSION][$ar::returnDbTableName()][$ar->getPrimaryFieldValue()] = $ar->asStdClass();
    }

    /**
     * @return mixed[]
     */
    public function read(ActiveRecord $ar) : array
    {
        $session = self::getSessionForActiveRecord($ar);

        return array($session[$ar->getPrimaryFieldValue()]);
    }

    public function update(ActiveRecord $ar) : void
    {
        $this->create($ar);
    }

    public function delete(ActiveRecord $ar) : void
    {
        unset($_SESSION[self::AR_CONNECTOR_SESSION][$ar::returnDbTableName()][$ar->getPrimaryFieldValue()]);
    }

    /**
     * @return mixed[]
     * @internal param $q
     */
    public function readSet(ActiveRecordList $arl) : array
    {
        $session = self::getSessionForActiveRecord($arl->getAR());
        foreach ($session as $i => $s) {
            $session[$i] = (array) $s;
        }
        foreach ($arl->getArWhereCollection()->getWheres() as $w) {
            $fieldname = $w->getFieldname();
            $v = $w->getValue();
            $operator = $w->getOperator();

            foreach ($session as $i => $s) {
                $session[$i] = (array) $s;
                if (($operator === '=') && $s[$fieldname] !== $v) {
                    unset($session[$i]);
                }
            }
        }

        return $session;
    }

    public function affectedRows(ActiveRecordList $arl) : int
    {
        return count($this->readSet($arl));
    }

    /**
     * @param $value
     * @param $type
     */
    public function quote($value, $type) : string
    {
        return $value;
    }

    public function updateIndices(ActiveRecord $ar) : void
    {
        // TODO: Implement updateIndices() method.
    }
}
