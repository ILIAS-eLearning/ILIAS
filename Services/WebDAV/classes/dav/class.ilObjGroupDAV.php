<?php

require_once 'Modules/Group/classes/class.ilObjGroup.php';

class ilObjGroupDAV extends ilObjContainerDAV
{
    public function __construct(ilObjGroup $a_obj)
    {
        parent::__construct($a_obj);
    }
    
    public function getChildCollectionType()
    {
        return 'fold';
    }
}