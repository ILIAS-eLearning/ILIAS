<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

class TaskContainer extends \ActiveRecord
{

    public static function returnDbTableName()
    {
        return "il_bt_task";
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
    protected $id = 0;
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
     * @var int
     *
     * @con_fieldtype  integer
     * @con_has_field  true
     * @con_length     8
     */
    protected $bucket_id;


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
}