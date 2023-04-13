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

    public function createByTableName(string $table_name): ilDclTableViewBaseDefaultValue
    {
        switch ($table_name) {
            case ilDclTableViewTextDefaultValue::returnDbTableName():
                return new ilDclTableViewTextDefaultValue();
            case ilDclTableViewNumberDefaultValue::returnDbTableName():
                return new ilDclTableViewNumberDefaultValue();
            case ilDclTableViewDateDefaultValue::returnDbTableName():
            default:
                return new ilDclTableViewDateDefaultValue();
        }
    }
}
