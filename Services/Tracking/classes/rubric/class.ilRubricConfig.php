<?php
/**
 * Class rubricConfig
 *
 * @author  CPKN <itstaff@cpkn.ca>
 */
class rubricConfig extends ActiveRecord {

        const TABLE_NAME        = 'rubric';
        const DATE_FORMAT       = 'Y-m-d H:i:s';
        const EXCEPTIONS        = true;
        const TRACE             = false;
        
        /**
         * @return string
         * @description Return the Name of your Database Table
         */
        static function returnDbTableName() {
                return self::TABLE_NAME;
        }        
                
        /**
         * @var int
         *
         * @db_has_field    true
         * @db_fieldtype    integer
         * @db_length       4
         * @db_is_primary   true
         * @db_is_unique    true
         * @db_sequence     true
         * @db_is_notnull   true
         */
        protected $rubric_id = null;
        
        /**
         * @return mixed
         */
        public function getRubricId(){
            return($this->rubric_id);
        }
        
        /**
         * @param int $rubric_id
         */
        public function setRubricId($rubric_id){
            $this->rubric_id=$rubric_id;
        }
               
        /**
         * @var int
         *
         * @db_has_field    true
         * @db_fieldtype    integer
         * @db_length       4
         */
        protected $obj_id = null;
        
        /**
         * @param int $obj_id
         */
        public function setObjId($obj_id){
            $this->obj_id=$obj_id;
        }
        
        /**
         * @var int
         *
         * @db_has_field    true
         * @db_fieldtype    integer
         * @db_length       4
         */
        protected $passing_grade = null;
        
        /**
         * @var int
         *
         * @db_has_field    true
         * @db_fieldtype    integer
         * @db_length       4
         */
        protected $owner = null;
        
        /**
         * @param int $owner
         */
        public function setOwner($owner){
            $this->owner=$owner;
        }
        
        /**
         * @var int
         *
         * @db_has_field    true
         * @db_fieldtype    timestamp
         */
        protected $deleted = null;
        
        /**
         * @param timestamp $deleted
         */
        public function setDeleted($deleted){
            $this->deleted=$deleted;
        }
        
        /**
         * @var int
         *
         * @db_has_field    true
         * @db_fieldtype    timestamp
         */
        protected $create_date = null;
        
        /**
         * @param timestamp $create_date
         */
        public function setCreateDate($create_date){
            $this->create_date=$create_date;
        }
        
        /**
         * @var int
         *
         * @db_has_field    true
         * @db_fieldtype    timestamp
         */
        protected $last_update = null;
        
        /**
         * @param timestamp $last_update
         */
        public function setLastUpdate($last_update){
            $this->last_update=$last_update;
        }
        
        /**
         * @return ilRubric[]
         */
        public static function getAll() {
                return self::get();
        }

        
        
        
        


       
}
?>

