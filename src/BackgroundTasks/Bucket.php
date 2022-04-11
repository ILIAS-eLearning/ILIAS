<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;

/**
 * Interface Observer
 * @package ILIAS\BackgroundTasks
 * Contains several chained tasks and infos about them.
 */
interface Bucket extends BucketMeta
{
    public function getUserId() : int;
    
    public function setUserId(int $user_id) : void;
    
    /**
     * Used by a job to notify his percentage.
     */
    public function setPercentage(Task $task, int $percentage) : void;
    
    public function getOverallPercentage() : int;
    
    public function setOverallPercentage(int $percentage) : void;
    
    public function setCurrentTask(Task $task) : void;
    
    public function getCurrentTask() : Task;
    
    public function hasCurrentTask() : bool;
    
    public function setTask(Task $task) : void;
    
    public function getTask() : Task;
    
    public function setState(int $state) : void;
    
    public function getState() : int;
    
    /**
     * @return bool      Returns true if everything's alright. Throws an exception otherwise.
     * @throws Exception
     */
    public function checkIntegrity() : bool;
    
    /**
     * Let the user interact with the bucket task queue.
     */
    public function userInteraction(Option $option) : void;
    
    public function getDescription() : string;
    
    public function getTitle() : string;
    
    /**
     * There was something going on in the bucket, it's still working.
     */
    public function heartbeat() : void;
    
    public function setLastHeartbeat(int $timestamp) : void;
    
    /**
     * When was the last time that something happened on this bucket?
     */
    public function getLastHeartbeat() : int;
}
