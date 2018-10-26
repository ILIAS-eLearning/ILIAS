<?php

require_once 'Modules/Course/classes/class.ilObjCourse.php';

class ilObjCourseDAV extends ilObjContainerDAV
{
    public function __construct(ilObjCourse $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        parent::__construct($a_obj, $repo_helper, $dav_helper);
    }
    
    public function getChildCollectionType()
    {
        return 'fold';
    }
}