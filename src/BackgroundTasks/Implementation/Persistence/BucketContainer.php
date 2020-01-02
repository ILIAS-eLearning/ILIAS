<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

require_once("./Services/ActiveRecord/class.ActiveRecord.php");

class BucketContainer extends \ActiveRecord
{
    public static function returnDbTableName()
    {
        return "il_bt_bucket";
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
     * @con_length     8
     */
    protected $user_id;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $root_task_id = 0;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $current_task_id = 0;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     2
     */
    protected $state;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected $total_number_of_tasks;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     2
     */
    protected $percentage = 0;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     255
     */
    protected $title;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     255
     */
    protected $description;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  timestamp
     */
    protected $last_heartbeat;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param  $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }


    /**
     * @param  $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }


    /**
     * @return int
     */
    public function getRootTaskid()
    {
        return $this->root_task_id;
    }


    /**
     * @param  $root_task_id
     */
    public function setRootTaskid($root_task_id)
    {
        $this->root_task_id = $root_task_id;
    }


    /**
     * @return int
     */
    public function getCurrentTaskid()
    {
        return $this->current_task_id;
    }


    /**
     * @param  $current_task_id
     */
    public function setCurrentTaskid($current_task_id)
    {
        $this->current_task_id = $current_task_id;
    }


    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }


    /**
     * @param  $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }


    /**
     * @return int
     */
    public function getTotalNumberoftasks()
    {
        return $this->total_number_of_tasks;
    }


    /**
     * @param  $total_number_of_tasks
     */
    public function setTotalNumberoftasks($total_number_of_tasks)
    {
        $this->total_number_of_tasks = $total_number_of_tasks;
    }


    /**
     * @return int
     */
    public function getPercentage()
    {
        return $this->percentage;
    }


    /**
     * @param  $percentage
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * @return int
     */
    public function getLastHeartbeat()
    {
        return $this->last_heartbeat;
    }


    /**
     * @param int $last_heartbeat
     */
    public function setLastHeartbeat($last_heartbeat)
    {
        $this->last_heartbeat = $last_heartbeat;
    }
}
