<?php

/**
 * This is just a dummy class for unit testing
 *
 * If it is necessary to identify this object, an ID can be set with the constructor
 */
class ilObjDummyDAV extends ilObjectDAV
{
    protected $id;
    public function __construct($id = 0)
    {
    }

    public function getId()
    {
        return $this->id;
    }
}
