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
 
namespace ILIAS\BackgroundTasks\Implementation\Persistence;

class ValueToTaskContainer extends \ActiveRecord
{
    public static function returnDbTableName() : string
    {
        return "il_bt_value_to_task";
    }
    
    /**
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_sequence   true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $id = null;
    /**
     * @con_fieldtype  integer
     * @con_has_field  true
     * @con_length     8
     */
    protected ?int $task_id = null;
    /**
     * @con_fieldtype  integer
     * @con_has_field  true
     * @con_length     8
     */
    protected ?int $value_id = null;
    /**
     * @con_fieldtype  integer
     * @con_has_field  true
     * @con_length     8
     */
    protected ?int $bucket_id = null;
    /**
     * @con_fieldtype  integer
     * @con_has_field  true
     * @con_length     8
     */
    protected ?int $position = null;
    
    public function getId() : int
    {
        return $this->id;
    }
    
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    
    public function getTaskId() : int
    {
        return $this->task_id;
    }
    
    public function setTaskId(int $task_id) : void
    {
        $this->task_id = $task_id;
    }
    
    public function getValueId() : int
    {
        return $this->value_id;
    }
    
    public function setValueId(int $value_id) : void
    {
        $this->value_id = $value_id;
    }
    
    public function getBucketId() : int
    {
        return $this->bucket_id;
    }
    
    public function setBucketId(int $bucket_id) : void
    {
        $this->bucket_id = $bucket_id;
    }
    
    public function getPosition() : int
    {
        return $this->position;
    }
    
    public function setPosition(int $position) : void
    {
        $this->position = $position;
    }
}
