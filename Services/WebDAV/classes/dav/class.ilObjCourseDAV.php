<?php

require_once 'Modules/Course/classes/class.ilObjCourse.php';

/**
 * Class ilObjCourseDAV
 *
 * Implementation for ILIAS Course Objects represented as WebDAV Collections
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @extends ilObjContainerDAV
 */
class ilObjCourseDAV extends ilObjContainerDAV
{
    /**
     * Check if given object has valid type and calls parent constructor
     *
     * @param ilObjCourse $a_obj
     * @param ilWebDAVRepositoryHelper $repo_helper
     * @param ilWebDAVObjDAVHelper $dav_helper
     */
    public function __construct(ilObjCourse $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        parent::__construct($a_obj, $repo_helper, $dav_helper);
    }

    /**
     * All children of a courses will be also folders
     *
     * @return string
     */
    public function getChildCollectionType()
    {
        return 'fold';
    }
}
