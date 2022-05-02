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

class TaskContainer extends \ActiveRecord
{
    public static function returnDbTableName() : string
    {
        return "il_bt_task";
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
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected ?string $type = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected ?string $class_path = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected ?string $class_name = null;
    /**
     * @con_fieldtype  integer
     * @con_has_field  true
     * @con_length     8
     */
    protected ?int $bucket_id = null;
    
    public function getId() : int
    {
        return $this->id;
    }
    
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    
    public function getType() : string
    {
        return $this->type;
    }
    
    public function setType(string $type) : void
    {
        $this->type = $type;
    }
    
    public function getClassPath() : string
    {
        return $this->class_path;
    }
    
    public function setClassPath(string $class_path) : void
    {
        $this->class_path = $class_path;
    }
    
    public function getClassName() : string
    {
        return $this->class_name;
    }
    
    public function setClassName(string $class_name) : void
    {
        $this->class_name = $class_name;
    }
    
    public function getBucketId() : int
    {
        return $this->bucket_id;
    }
    
    public function setBucketId(int $bucket_id) : void
    {
        $this->bucket_id = $bucket_id;
    }
}
