<?php

require_once 'Modules/Folder/classes/class.ilObjFolder.php';

/**
 * Class ilObjFolderDAV
 *
 * Implementation for ILIAS Folder Objects represented as WebDAV Collections
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @extends ilObjContainerDAV
 */
class ilObjFolderDAV extends ilObjContainerDAV
{
    /**
     * Check if given object has valid type and calls parent constructor
     *
     * @param ilObjFolder $a_obj
     * @param ilWebDAVRepositoryHelper $repo_helper
     * @param ilWebDAVObjDAVHelper $dav_helper
     */
    public function __construct(ilObjFolder $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        parent::__construct($a_obj, $repo_helper, $dav_helper);
    }

    /**
     * All children of a folder will be also folders
     *
     * @return string
     */
    public function getChildCollectionType()
    {
        return 'fold';
    }
}
