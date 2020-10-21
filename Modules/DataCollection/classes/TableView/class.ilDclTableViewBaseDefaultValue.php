<?php

/**
 * Class ilDclTableViewBaseDefaultValue
 *
 * @author  Jannik Dolf <jd@studer-raimann.ch>
 */
abstract class ilDclTableViewBaseDefaultValue extends ActiveRecord
{
    /**
     * @param $data_type_id
     * @param $tview_id
     *
     * @return ilDclTableViewBaseDefaultValue
     * @throws ilDclException
     */
    public static function findSingle($data_type_id, $tview_id) {
        $storage_location = ilDclCache::getDatatype($data_type_id)->getStorageLocation();
        $class = ilDclDefaultValueFactory::STORAGE_LOCATION_MAPPING[$storage_location];
        return $class::getCollection()->where(array("tview_set_id" => $tview_id))->first();
    }


    public static function findAll($data_type_id, $tview_id) {
        $storage_location = ilDclCache::getDatatype($data_type_id)->getStorageLocation();
        $class = ilDclDefaultValueFactory::STORAGE_LOCATION_MAPPING[$storage_location];
        return $class::getCollection()->where(array("tview_set_id" => $tview_id))->get();
    }
}