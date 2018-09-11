<?php

require_once 'Modules/Folder/classes/class.ilObjFolder.php';

class ilObjFolderDAV extends ilObjContainerDAV
{
    public function __construct(ilObjFolder $a_obj)
    {
        parent::__construct($a_obj);
    }
    
    public function getChildCollectionType()
    {
        return 'fold';
    }
    
}