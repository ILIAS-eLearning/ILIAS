<?php
/**
 * Class ilGradebookConfig
 *
 * @author  CPKN <itstaff@cpkn.ca>
 */

class ilGradebookConfig extends ActiveRecord {

    const TABLE_NAME        = 'gradebook';
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
    protected $id = null;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * @param int $obj_id
     */
    public function setObjId($obj_id)
    {
        $this->obj_id = $obj_id;
    }

    /**
     * @return int
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param int $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param int $create_date
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
    }

    /**
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }

    /**
     * @param int $last_update
     */
    public function setLastUpdate($last_update)
    {
        $this->last_update = $last_update;
    }

    /**
     * @db_has_field    FALSE
     */
    protected $recently_created;

    /**
     * @return mixed
     */
    public function getRecentlyCreated()
    {
        return $this->recently_created;
    }

    /**
     * @param mixed $recently_created
     */
    public function setRecentlyCreated($recently_created)
    {
        $this->recently_created = $recently_created;
    }



    public static function firstOrCreate($obj_id)
    {
        global $ilUser;
        $instance = self::where([
            'obj_id'=>$obj_id
        ])->first();
        if(is_null($instance)){
            $instance = new self();
            $instance->setObjId($obj_id);
            $instance->setRecentlyCreated(TRUE);
            $instance->setOwner($ilUser->getId());
            $instance->setLastUpdate(date("Y-m-d H:i:s"));
            $instance->setCreateDate(date("Y-m-d H:i:s"));
            $instance->save();
        }
        return $instance;
    }

}
