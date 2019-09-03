<?php

namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Types\Type;

/**
 * Interface Value
 *
 * @package ILIAS\BackgroundTasks
 *
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
    public function getHash();


    /**
     * @param \ILIAS\BackgroundTasks\Value $other
     *
     * @return bool
     */
    public function equals(Value $other);


    /**
     * @return Type get the Type of the
     */
    public function getType();


    /**
     * @param Task $parentTask
     *
     * @return mixed
     */
    public function setParentTask(Task $parentTask);


    /**
     * @return Task
     */
    public function getParentTask();


    /**
     * @return boolean
     */
    public function hasParentTask();


    /**
     * @param $value
     *
     * @return
     */
    function setValue($value);
}
