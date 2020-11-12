<?php

class ilDclDefaultValueFactory
{
    const STORAGE_LOCATION_MAPPING = [
        1 => ilDclTableViewTextDefaultValue::class,
        2 => ilDclTableViewNumberDefaultValue::class,
        3 => ilDclTableViewDateDefaultValue::class
    ];

    /**
     * @return ilDclTableViewBaseDefaultValue
     */
    public function create($data_type_id) {
        $storage_location = ilDclCache::getDatatype($data_type_id)->getStorageLocation();
        $class = self::STORAGE_LOCATION_MAPPING[$storage_location];
        return new $class();

        // switch ($storage_location) {
        //     case 1:
        //         return new ilDclTableViewTextDefaultValue();
        //         break;
        //     case 2:
        //         return new ilDclTableViewNumberDefaultValue();
        //         break;
        //     case 3:
        //         return new ilDclTableViewDateDefaultValue();
        //         break;
        // }
    }

}