<?php

namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Types\Type;

/**
 * Interface Value
 * @package ILIAS\BackgroundTasks
 * The Value as a defined format of data passed between two tasks. IO MUST be serialisable
 * since it will bes stored in the database or somewhere else
 */
interface Value extends \Serializable
{
    
    /**
     * @return string Gets a hash for this Value. If two objects are the same the hash must be the
     *                same! if two objects are different you need to have as view collisions as
     *                possible.
     */
    public function getHash() : string;
    
    public function equals(Value $other) : bool;
    
    public function getType() : Type;
    
    public function setParentTask(Task $parentTask) : void;
    
    public function getParentTask() : Task;
    
    public function hasParentTask() : bool;
    
    public function setValue($value) : void;
}
