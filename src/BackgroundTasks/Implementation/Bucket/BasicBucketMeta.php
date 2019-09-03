<?php

namespace ILIAS\BackgroundTasks\Implementation\Bucket;

use ILIAS\BackgroundTasks\BucketMeta;
use ILIAS\BackgroundTasks\Task;

/**
 * Class BasicBucketMeta
 *
 * @package ILIAS\BackgroundTasks\Implementation\Bucket
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 *
 * If you don't want to load the whole task structure of a bucket you will get an empty bucket. You
 * get meta-info about the bucket but cannot access its tasks etc. because they are not loaded yet.
 */
class BasicBucketMeta implements BucketMeta
{

    /**
     * @var int
     */
    protected $userId;
    /**
     * @var int
     */
    protected $state;
    /**
     * @var string
     */
    protected $title = "";
    /**
     * @var string
     */
    protected $description = "";
    /**
     * @var int
     */
    protected $percentage = 0;


    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }


    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }


    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }


    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
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
    public function getPercentage()
    {
        return $this->percentage;
    }


    /**
     * @param Task $task
     * @param int  $percentage
     *
     * @throws \EmptyBucketException
     */
    public function setPercentage(Task $task, $percentage)
    {
        throw new \EmptyBucketException("You cannot set the percentage on an empty bucket.");
    }


    /**
     * @return integer
     */
    public function getOverallPercentage()
    {
        return $this->percentage;
    }


    /**
     * @param $percentage int
     *
     */
    public function setOverallPercentage($percentage)
    {
        $this->percentage = $percentage;
    }
}