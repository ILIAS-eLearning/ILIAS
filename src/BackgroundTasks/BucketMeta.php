<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface BucketMeta
 * @package ILIAS\BackgroundTasks
 * A meta bucket contains infos about a bucket like its percentage, name etc.
 */
interface BucketMeta
{
    
    public function getUserId() : int;
    
    public function setUserId(int $user_id) : void;
    
    public function getOverallPercentage() : int;
    
    public function setOverallPercentage(int $percentage) : void;
    
    public function setState(int $state) : void;
    
    public function getState() : int;
    
    public function getDescription() : string;
    
    public function getTitle() : string;
}
