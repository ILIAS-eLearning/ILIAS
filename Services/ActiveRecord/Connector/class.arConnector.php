<?php

/**
 * Class arConnector
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @description
 * @version 2.0.7
 */
abstract class arConnector
{

    abstract public function nextID(ActiveRecord $ar);

    abstract public function checkConnection(ActiveRecord $ar);

    /**
     * @param              $fields
     */
    abstract public function installDatabase(ActiveRecord $ar, $fields);

    abstract public function updateDatabase(ActiveRecord $ar);

    abstract public function resetDatabase(ActiveRecord $ar);

    abstract public function truncateDatabase(ActiveRecord $ar);

    abstract public function checkTableExists(ActiveRecord $ar);

    /**
     * @param              $field_name
     */
    abstract public function checkFieldExists(ActiveRecord $ar, $field_name);

    /**
     * @param              $field_name
     */
    abstract public function removeField(ActiveRecord $ar, $field_name);

    /**
     * @param              $old_name
     * @param              $new_name
     */
    abstract public function renameField(ActiveRecord $ar, $old_name, $new_name);

    abstract public function create(ActiveRecord $ar);

    abstract public function read(ActiveRecord $ar);

    abstract public function update(ActiveRecord $ar);

    abstract public function delete(ActiveRecord $ar);

    abstract public function readSet(ActiveRecordList $arl);

    /**
     * @return int
     */
    abstract public function affectedRows(ActiveRecordList $arl);

    /**
     * @param $value
     * @param $type
     * @return string
     */
    abstract public function quote($value, $type);

    abstract public function updateIndices(ActiveRecord $ar);

    /**
     * @param $value
     */
    public function fixDate($value) : string
    {
        return $value;
    }
}
