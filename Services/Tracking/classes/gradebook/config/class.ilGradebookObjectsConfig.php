<?php
/**
 * Class ilGradebookObjectsConfig
 *
 * @author  CPKN <itstaff@cpkn.ca>
 */

class ilGradebookObjectsConfig extends ActiveRecord {

    const TABLE_NAME        = 'gradebook_objects';
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
     * @db_has_field    FALSE
     */
    protected $recently_created;

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
     * @db_is_notnull   true
     */
    protected $gradebook_id = null;


    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $revision_id = null;


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
     * @db_length       8
     */
    protected $parent = 0;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $placement_order = null;


    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $placement_depth = null;


    /**
     * @var int
     *
     * @db_has_field    true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $object_activated = 0;


    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected $object_weight = 0;

    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    256
     */
    protected $object_colour = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected $lp_type = null;


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
    public function getRevisionId()
    {
        return $this->revision_id;
    }

    /**
     * @param int $revision_id
     */
    public function setRevisionId($revision_id)
    {
        $this->revision_id = $revision_id;
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
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param int $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return int
     */
    public function getPlacementOrder()
    {
        return $this->placement_order;
    }

    /**
     * @param int $placement_order
     */
    public function setPlacementOrder($placement_order)
    {
        $this->placement_order = $placement_order;
    }

    /**
     * @return int
     */
    public function getPlacementDepth()
    {
        return $this->placement_depth;
    }

    /**
     * @param int $placement_depth
     */
    public function setPlacementDepth($placement_depth)
    {
        $this->placement_depth = $placement_depth;
    }

    /**
     * @return int
     */
    public function getObjectActivated()
    {
        return $this->object_activated;
    }

    /**
     * @return int
     */
    public function getGradebookId()
    {
        return $this->gradebook_id;
    }

    /**
     * @param int $gradebook_id
     */
    public function setGradebookId($gradebook_id)
    {
        $this->gradebook_id = $gradebook_id;
    }

    /**
     * @param int $object_activated
     */
    public function setObjectActivated($object_activated)
    {
        $this->object_activated = $object_activated;
    }

    /**
     * @return int
     */
    public function getObjectWeight()
    {
        return $this->object_weight;
    }

    /**
     * @param int $object_weight
     */
    public function setObjectWeight($object_weight)
    {
        $this->object_weight = $object_weight;
    }

    /**
     * @return string
     */
    public function getObjectColour()
    {
        return $this->object_colour;
    }

    /**
     * @param string $object_colour
     */
    public function setObjectColour($object_colour)
    {
        $this->object_colour = $object_colour;
    }

    /**
     * @return int
     */
    public function getLpType()
    {
        return $this->lp_type;
    }

    /**
     * @param int $lp_type
     */
    public function setLpType($lp_type)
    {
        $this->lp_type = $lp_type;
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

    /**
     * @param int $last_update
     */
    public function setLastUpdate($last_update)
    {
        $this->last_update = $last_update;
    }
    /**
     * Gets an instance or a new Object based on Revision Id and Obj Id
     * @param $revision_id
     * @param $obj_id
     * @return ActiveRecord|ilGradebookObjectsConfig
     */
    public static function firstOrNew($revision_id,$obj_id)
    {
       $instance = self::where(['obj_id'=>$obj_id,'revision_id'=>$revision_id])->first();
       if(is_null($instance)){
           $instance = new self();
           $instance->setObjId($obj_id);
           $instance->setRevisionId($revision_id);
           $instance->setRecentlyCreated(TRUE);
       }
       return $instance;
    }



}
