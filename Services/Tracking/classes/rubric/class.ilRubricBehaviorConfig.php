<?php
/**
 * Class rubricBehaviorConfig
 *
 * @author  CPKN <itstaff@cpkn.ca>
 */
class rubricBehaviorConfig extends ActiveRecord {

        const TABLE_NAME        = 'rubric_behavior';
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
        protected $rubric_behavior_id = 0;
        
        /**
         * @var int
         *
         * @db_has_field    true
         * @db_fieldtype    integer
         * @db_length       4
         */
        protected $rubric_criteria_id = null;
        
        /**
         * @var int
         *
         * @db_has_field    true
         * @db_fieldtype    integer
         * @db_length       4
         */
        protected $rubric_label_id = null;
               
        /**
         * @var string
         *
         * @db_has_field    true
         * @db_fieldtype    text
         * @db_length       256
         */
        protected $description = null;
        
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

