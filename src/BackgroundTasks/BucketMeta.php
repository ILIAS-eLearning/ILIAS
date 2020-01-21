<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface Observer
 *
 * @package ILIAS\BackgroundTasks
 *
 * A meta bucket contains infos about a bucket like its percentage, name etc.
 */
interface BucketMeta
{

    /**
     * @return int
     */
    public function getUserId();


    /**
     * @param int $user_id
     */
    public function setUserId($user_id);


    /**
     * @return integer
     */
    public function getOverallPercentage();


    /**
     * @param $percentage int
     *
     */
    public function setOverallPercentage($percentage);


    /**
     * @param $state int From Observer\State
     *
     * @return void
     */
    public function setState($state);


    /**
     * @return int
     */
    public function getState();


    /**
     * @return string
     */
    public function getDescription();


    /**
     * @return string
     */
    public function getTitle();
}
