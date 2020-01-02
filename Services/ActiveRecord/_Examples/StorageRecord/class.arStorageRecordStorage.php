<?php

require_once('./Services/ActiveRecord/Storage/class.arStorage.php');

/**
 * Class arTestRecordStorage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
class arStorageRecordStorage extends arStorage
{

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName()
    {
        return 'ar_demo_storage_record';
    }
}
