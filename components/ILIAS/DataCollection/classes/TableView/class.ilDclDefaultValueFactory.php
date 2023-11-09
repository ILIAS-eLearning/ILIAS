<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

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
