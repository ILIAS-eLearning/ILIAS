<?php
/**
 * Class rubricGradeLockConfig
 *
 * @author  CPKN <itstaff@cpkn.ca>
 */

require_once('./Services/ActiveRecord/class.ActiveRecord.php');

class rubricGradeLockConfig extends ActiveRecord {

    const TABLE_NAME        = 'rubric_grade_lock';
    const DATE_FORMAT       = 'Y-m-d H:i:s';
    const EXCEPTIONS        = true;
    const TRACE             = false;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     * @db_is_primary   true
     * @db_sequence     true
     * @db_is_notnull   true
     */
    protected $grade_lock_id = 0;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $rubric_id = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $user_id = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $owner = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    timestamp
     */
    protected $create_date = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    timestamp
     */
    protected $last_update = null;

    /**
     * @var array
     */
    protected static $cache = array();
    /**
     * @var array
     */
    protected static $cacheLoaded = array();

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    static function returnDbTableName() {
        return self::TABLE_NAME;
    }



}
?>

