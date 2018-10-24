<?php

require_once 'Modules/Group/classes/class.ilObjGroup.php';

class ilObjGroupDAV extends ilObjContainerDAV
{
    public function __construct(ilObjGroup $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        parent::__construct($a_obj, $repo_helper, $dav_helper);
    }
    
    public function getChildCollectionType()
    {
        return 'fold';
    }
}