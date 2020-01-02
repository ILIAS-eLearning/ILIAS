<?php
require_once('../../class.ActiveRecord.php');

/**
 * Class arMixedKey
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arMixedKey extends ActiveRecord
{

    /**
     * @return string
     * @description Return the Name of your Database Table
     * @deprecated
     */
    public static function returnDbTableName()
    {
        return 'ar_mixed_key';
    }


    public function getConnectorContainerName()
    {
        return 'ar_mixed_key';
    }


    /**
     * @var int
     *
     * @con_is_primary true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $ext_id;
    /**
     * @var int
     *
     * @con_is_primary true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $parent_id;
}
