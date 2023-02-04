<?php

/**
 * Class ilDclTableViewBaseDefaultValue
 * @author  Jannik Dolf <jd@studer-raimann.ch>
 */
abstract class ilDclTableViewBaseDefaultValue extends ActiveRecord
{
    /**
     * @throws ilDclException
     */
    public static function findSingle(
        int $data_type_id,
        int $tview_id
    ): ?ActiveRecord //?|ActiveRecord|ilDclTableViewBaseDefaultValue
    {
        $storage_location = ilDclCache::getDatatype($data_type_id)->getStorageLocation();
        if (is_null($storage_location) || $storage_location == 0) {
            return null;
        }

        try {
            /** @var ilDclTableView $class */
            $class = ilDclDefaultValueFactory::STORAGE_LOCATION_MAPPING[$storage_location];
            return $class::getCollection()->where(array("tview_set_id" => $tview_id))->first();
        } catch (Exception $ex) {
            return null;
        }
    }

    public static function findAll(int $data_type_id, int $tview_id): ?array
    {
        $storage_location = ilDclCache::getDatatype($data_type_id)->getStorageLocation();
        if (is_null($storage_location) || $storage_location == 0) {
            return null;
        }

        try {
            $class = ilDclDefaultValueFactory::STORAGE_LOCATION_MAPPING[$storage_location];
            return $class::getCollection()->where(array("tview_set_id" => $tview_id))->get();
        } catch (Exception $ex) {
            return null;
        }
    }
}
