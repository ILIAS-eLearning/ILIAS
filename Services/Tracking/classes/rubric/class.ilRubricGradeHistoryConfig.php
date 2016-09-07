<?php
/**
 * Class rubricGradeHistoryConfig
 *
 * @author  CPKN <itstaff@cpkn.ca>
 */

require_once('./Services/ActiveRecord/class.ActiveRecord.php');

class rubricGradeHistoryConfig extends ActiveRecord {

    const TABLE_NAME        = 'rubric_grade_hist';
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
    protected $rubric_history_id = 0;

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
    protected $obj_id = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $usr_id = null;


    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $status = null;


    /**
     * @var float
     *
     * @con_has_field  true
     * @db_length       8
     * @con_fieldtype  float
     */
    protected $mark = 0.00;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $completed = null;

    /**
     * @var string
     *
     * @db_has_field    true
     * @db_fieldtype    text
     * @db_length       256
     */
    protected $comments = null;

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
    protected $deleted = null;

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
     * @return string
     * @description Return the Name of your Database Table
     */
    static function returnDbTableName() {
        return self::TABLE_NAME;
    }



}
?>

