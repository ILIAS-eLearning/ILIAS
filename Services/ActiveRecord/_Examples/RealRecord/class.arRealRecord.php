<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class arRealRecord
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arRealRecord extends ActiveRecord
{

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName()
    {
        return 'ar_demo_real_record';
    }


    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           4
     */
    protected $id = 0;
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           200
     */
    protected $title = '';
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           200
     */
    public $description = '';
    /**
     * @var array
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           200
     */
    protected $usr_ids = array();


    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param array $usr_ids
     */
    public function setUsrIds($usr_ids)
    {
        $this->usr_ids = $usr_ids;
    }


    /**
     * @return array
     */
    public function getUsrIds()
    {
        return $this->usr_ids;
    }
}
