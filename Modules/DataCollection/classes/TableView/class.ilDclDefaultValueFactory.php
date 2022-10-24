<?php

class ilDclDefaultValueFactory
{
    public const STORAGE_LOCATION_MAPPING = [
        1 => ilDclTableViewTextDefaultValue::class,
        2 => ilDclTableViewNumberDefaultValue::class,
        3 => ilDclTableViewDateDefaultValue::class
    ];

    public function create(int $data_type_id): ilDclTableViewBaseDefaultValue
    {
        $storage_location = ilDclCache::getDatatype($data_type_id)->getStorageLocation();
        $class = self::STORAGE_LOCATION_MAPPING[$storage_location];
        return new $class();
    }
}
