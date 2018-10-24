<?php

require_once 'Modules/Folder/classes/class.ilObjFolder.php';

class ilObjFolderDAV extends ilObjContainerDAV
{
    public function __construct(ilObjFolder $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        parent::__construct($a_obj, $repo_helper, $dav_helper);
    }
    
    public function getChildCollectionType()
    {
        return 'fold';
    }
    
}