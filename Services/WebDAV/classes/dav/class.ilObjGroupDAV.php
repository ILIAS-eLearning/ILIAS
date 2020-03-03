<?php

require_once 'Modules/Group/classes/class.ilObjGroup.php';

/**
 * Class ilObjGroupDAV
 *
 * Implementation for ILIAS Group Objects represented as WebDAV Collections
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @extends ilObjContainerDAV
 */
class ilObjGroupDAV extends ilObjContainerDAV
{
    /**
     * Check if given object has valid type and calls parent constructor
     *
     * @param ilObjGroup $a_obj
     * @param ilWebDAVRepositoryHelper $repo_helper
     * @param ilWebDAVObjDAVHelper $dav_helper
     */
    public function __construct(ilObjGroup $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        parent::__construct($a_obj, $repo_helper, $dav_helper);
    }

    /**
     * All children of a groups will be also folders
     *
     * @return string
     */
    public function getChildCollectionType()
    {
        return 'fold';
    }
}
