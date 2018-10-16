<?php

require_once 'Modules/Category/classes/class.ilObjCategory.php';

class ilObjCategoryDAV extends ilObjContainerDAV
{
    public function __construct(ilObjCategory $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        parent::__construct($a_obj, $repo_helper, $dav_helper);
    }
    
    public function getChildCollectionType()
    {
        return 'cat';
    }
}