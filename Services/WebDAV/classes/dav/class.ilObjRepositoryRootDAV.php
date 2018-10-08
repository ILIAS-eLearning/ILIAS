<?php

use Sabre\DAV\Exception\Forbidden;

include_once 'Modules/RootFolder/classes/class.ilObjRootFolder.php';

class ilObjRepositoryRootDAV extends ilObjContainerDAV implements Sabre\DAV\ICollection
{   
    /** @var $repository_root_name string */
    protected $repository_root_name;
    
    public function __construct(string $repository_root_name)
    {
        $this->repository_root_name = $repository_root_name;
        parent::__construct(new ilObjRootFolder(ROOT_FOLDER_ID, true));
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