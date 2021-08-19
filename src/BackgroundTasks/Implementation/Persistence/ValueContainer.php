<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

class ValueContainer extends \ActiveRecord
{
    public static function returnDbTableName()
    {
        return "il_bt_value";
    }


    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_sequence   true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $id;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $has_parent_task;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $parent_task_id;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $hash;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $type;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $class_path;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $class_name;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     */
    protected $serialized;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $bucket_id;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $position;


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
    public function getHasParenttask()
    {
        return $this->has_parent_task;
    }


    /**
     * @param int $has_parent_task
     */
    public function setHasParenttask($has_parent_task)
    {
        $this->has_parent_task = $has_parent_task;
    }


    /**
     * @return int
     */
    public function getParentTaskid()
    {
        return $this->parent_task_id;
    }


    /**
     * @param int $parent_task_id
     */
    public function setParentTaskid($parent_task_id)
    {
        $this->parent_task_id = $parent_task_id;
    }


    /**
     * @return string|null
     */
    public function getHash()
    {
        return $this->hash;
    }


    /**
     * @param string|null $hash may be null for thunk values.
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getClassPath()
    {
        return $this->class_path;
    }


    /**
     * @param string $class_path
     */
    public function setClassPath($class_path)
    {
        $this->class_path = $class_path;
    }


    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->class_name;
    }


    /**
     * @param string $class_name
     */
    public function setClassName($class_name)
    {
        $this->class_name = $class_name;
    }


    /**
     * @return string|null
     */
    public function getSerialized()
    {
        return $this->serialized;
    }


    /**
     * @param string|null $serialized May be null for thunk values.
     */
    public function setSerialized($serialized)
    {
        $this->serialized = $serialized;
    }


    /**
     * @return int
     */
    public function getBucketId()
    {
        return $this->bucket_id;
    }


    /**
     * @param int $bucket_id
     */
    public function setBucketId($bucket_id)
    {
        $this->bucket_id = $bucket_id;
    }
    
    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
    
    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }
    
}
