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
    ): ?ActiveRecord { //?|ActiveRecord|ilDclTableViewBaseDefaultValue
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
