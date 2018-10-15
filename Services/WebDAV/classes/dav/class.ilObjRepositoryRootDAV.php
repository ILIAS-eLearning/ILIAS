<?php

use Sabre\DAV\Exception\Forbidden;

include_once 'Modules/RootFolder/classes/class.ilObjRootFolder.php';

class ilObjRepositoryRootDAV extends ilObjContainerDAV implements Sabre\DAV\ICollection
{   
    /** @var $repository_root_name string */
    protected $repository_root_name;
    
    public function __construct(string $repository_root_name, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        $this->repository_root_name = $repository_root_name;
        parent::__construct(new ilObjRootFolder(ROOT_FOLDER_ID, true), $repo_helper, $dav_helper);
    }
    
    public function setName($name)
    {
        throw new Forbidden("It's not allowed to rename the repository root");
    }

    public function getName()
    {
        return $this->repository_root_name;
    }

    public function getChildCollectionType()
    { 
        return 'cat';
    }
}