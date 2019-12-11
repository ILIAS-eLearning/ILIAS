<?php

/**
 * Class arConnector
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @description
 *
 * @version 2.0.7
 */
abstract class arConnector
{

    /**
     * @param ActiveRecord $ar
     */
    abstract public function nextID(ActiveRecord $ar);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function checkConnection(ActiveRecord $ar);


    /**
     * @param ActiveRecord $ar
     * @param              $fields
     */
    abstract public function installDatabase(ActiveRecord $ar, $fields);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function updateDatabase(ActiveRecord $ar);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function resetDatabase(ActiveRecord $ar);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function truncateDatabase(ActiveRecord $ar);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function checkTableExists(ActiveRecord $ar);


    /**
     * @param ActiveRecord $ar
     * @param              $field_name
     */
    abstract public function checkFieldExists(ActiveRecord $ar, $field_name);


    /**
     * @param ActiveRecord $ar
     * @param              $field_name
     */
    abstract public function removeField(ActiveRecord $ar, $field_name);


    /**
     * @param ActiveRecord $ar
     * @param              $old_name
     * @param              $new_name
     */
    abstract public function renameField(ActiveRecord $ar, $old_name, $new_name);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function create(ActiveRecord $ar);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function read(ActiveRecord $ar);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function update(ActiveRecord $ar);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function delete(ActiveRecord $ar);


    /**
     * @param ActiveRecordList $arl
     */
    abstract public function readSet(ActiveRecordList $arl);


    /**
     * @param ActiveRecordList $arl
     *
     * @return int
     */
    abstract public function affectedRows(ActiveRecordList $arl);


    /**
     * @param $value
     * @param $type
     *
     * @return string
     */
    abstract public function quote($value, $type);


    /**
     * @param ActiveRecord $ar
     */
    abstract public function updateIndices(ActiveRecord $ar);


    /**
     * @param $value
     * @return string
     */
    public function fixDate($value)
    {
        return $value;
    }
}
