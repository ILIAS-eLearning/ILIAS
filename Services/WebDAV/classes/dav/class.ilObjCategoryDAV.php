<?php

require_once 'Modules/Category/classes/class.ilObjCategory.php';

class ilObjCategoryDAV extends ilObjContainerDAV
{
    public function __construct(ilObjCategory $a_obj)
    {
        parent::__construct($a_obj);
    }
    
    public function getChildCollectionType()
    {
        return 'cat';
    }
}