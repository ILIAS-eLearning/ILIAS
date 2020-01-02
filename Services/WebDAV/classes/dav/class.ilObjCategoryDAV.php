<?php

require_once 'Modules/Category/classes/class.ilObjCategory.php';

/**
 * Class ilObjCategoryDAV
 *
 * Implementation for ILIAS Category Objects represented as WebDAV Collections
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @extends ilObjContainerDAV
 */
class ilObjCategoryDAV extends ilObjContainerDAV
{
    /**
     * Check if given object has valid type and calls parent constructor
     *
     * @param ilObjCategory $a_obj
     * @param ilWebDAVRepositoryHelper $repo_helper
     * @param ilWebDAVObjDAVHelper $dav_helper
     */
    public function __construct(ilObjCategory $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        parent::__construct($a_obj, $repo_helper, $dav_helper);
    }

    /**
     * All children of a category will be also categories
     *
     * @return string
     */
    public function getChildCollectionType()
    {
        return 'cat';
    }
}
