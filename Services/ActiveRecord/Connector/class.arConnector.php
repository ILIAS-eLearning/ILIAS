<?php

/**
 * Class arConnector
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
abstract class arConnector
{
    /**
     * @return mixed
     */
    abstract public function nextID(ActiveRecord $ar);

    abstract public function checkConnection(ActiveRecord $ar) : bool;

    abstract public function installDatabase(ActiveRecord $ar, array $fields) : bool;

    abstract public function updateDatabase(ActiveRecord $ar) : bool;

    abstract public function resetDatabase(ActiveRecord $ar) : bool;

    abstract public function truncateDatabase(ActiveRecord $ar) : bool;

    abstract public function checkTableExists(ActiveRecord $ar) : bool;

    abstract public function checkFieldExists(ActiveRecord $ar, string $field_name) : bool;

    abstract public function removeField(ActiveRecord $ar, string $field_name) : bool;

    abstract public function renameField(ActiveRecord $ar, string $old_name, string $new_name) : bool;

    abstract public function create(ActiveRecord $ar) : void;

    abstract public function read(ActiveRecord $ar) : array;

    abstract public function update(ActiveRecord $ar) : void;

    abstract public function delete(ActiveRecord $ar) : void;

    abstract public function readSet(ActiveRecordList $arl) : array;

    abstract public function affectedRows(ActiveRecordList $arl) : int;

    /**
     * @param mixed $value
     */
    abstract public function quote($value, string $type) : string;

    abstract public function updateIndices(ActiveRecord $ar) : void;

    public function fixDate(string $value) : string
    {
        return $value;
    }
}
