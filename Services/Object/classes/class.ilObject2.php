<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Alex Killing <alex.killing@gmx.de>
 */
abstract class ilObject2 extends ilObject
{
    /**
     * Constructor
     * @param	int	reference_id or object_id
     * @param	bool	treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_reference = true)
    {
        $this->initType();
        parent::__construct($a_id, $a_reference);
    }

    abstract protected function initType();

    /**
    * Read data from db
    */
    final public function read() : void
    {
        parent::read();
        $this->doRead();
    }

    protected function doRead()
    {
    }
    

    final public function create($a_clone_mode = false) : int
    {
        if ($this->beforeCreate()) {
            $id = parent::create();
            if ($id) {
                $this->doCreate($a_clone_mode);
                return $id;
            }
        }
        return 0;
    }

    protected function doCreate()
    {
    }

    /**
     * If overwritten this method should return true,
     * there is currently no "abort" handling for cases where "false" is returned.
     * @return bool
     */
    protected function beforeCreate()
    {
        return true;
    }
    
    final public function update() : bool
    {
        if ($this->beforeUpdate()) {
            if (!parent::update()) {
                return false;
            }
            $this->doUpdate();
            
            return true;
        }
        
        return false;
    }

    protected function doUpdate()
    {
    }
    
    protected function beforeUpdate()
    {
        return true;
    }

    final public function delete() : bool
    {
        if ($this->beforeDelete()) {
            if (parent::delete()) {
                $this->doDelete();
                $this->id = 0;
                return true;
            }
        }
        return false;
    }

    protected function doDelete()
    {
    }
    
    protected function beforeDelete()
    {
        return true;
    }

    final public function cloneMetaData(ilObject $target_obj) : bool
    {
        return parent::cloneMetaData($target_obj);
    }
    
    final public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false) : ?ilObject
    {
        if ($this->beforeCloneObject()) {
            $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
            if ($new_obj) {
                $this->doCloneObject($new_obj, $target_id, $copy_id);
                return $new_obj;
            }
        }
        return null;
    }
    
    protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
    }
    
    protected function beforeCloneObject()
    {
        return true;
    }
}
