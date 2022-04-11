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
 
namespace ILIAS\BackgroundTasks\Implementation\Bucket;

use ILIAS\BackgroundTasks\BucketMeta;
use ILIAS\BackgroundTasks\Exceptions\BucketNotFoundException;
use ILIAS\BackgroundTasks\Task;

/**
 * Class BasicBucketMeta
 * @package ILIAS\BackgroundTasks\Implementation\Bucket
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * If you don't want to load the whole task structure of a bucket you will get an empty bucket. You
 * get meta-info about the bucket but cannot access its tasks etc. because they are not loaded yet.
 */
class BasicBucketMeta implements BucketMeta
{
    protected int $user_id;
    protected int $state;
    protected string $title = "";
    protected string $description = "";
    protected int $percentage = 0;
    
    public function getUserId() : int
    {
        return $this->user_id;
    }
    
    public function setUserId(int $user_id) : void
    {
        $this->user_id = $user_id;
    }
    
    public function getState() : int
    {
        return $this->state;
    }
    
    public function setState(int $state) : void
    {
        $this->state = $state;
    }
    
    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }
    
    public function getDescription() : string
    {
        return $this->description;
    }
    
    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }
    
    public function getPercentage() : int
    {
        return $this->percentage;
    }
    
    public function setPercentage(Task $task, int $percentage) : void
    {
        throw new BucketNotFoundException("You cannot set the percentage on an empty bucket.");
    }
    
    public function getOverallPercentage() : int
    {
        return $this->percentage;
    }
    
    public function setOverallPercentage(int $percentage) : void
    {
        $this->percentage = $percentage;
    }
}
