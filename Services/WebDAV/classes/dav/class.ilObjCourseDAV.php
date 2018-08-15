<?php

require_once 'Modules/Course/classes/class.ilObjCourse.php';

class ilObjCourseDAV extends ilObjContainerDAV
{
    public function __construct(ilObjCourse $a_obj)
    {
        parent::__construct($a_obj);
    }
    
    public function getChildCollectionType()
    {
        return 'fold';
    }
}