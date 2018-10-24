<?php

use Sabre\CalDAV\Notifications\ICollection;
use ILIAS\UI\NotImplementedException;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\Forbidden;

class ilMountPointDAV implements Sabre\DAV\ICollection
{
    /** @var ilAccessHandler */
    protected $access;
    
    /** @var string */
    protected $client_id;

    /** @var ilWebDAVRepositoryHelper */
    protected $repo_helper;

    /** @var ilWebDAVObjDAVHelper */
    protected $dav_helper;

    public function __construct(ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        global $DIC;

        $this->repo_helper = $repo_helper;
        $this->dav_helper = $dav_helper;
        $this->client_id = $DIC['ilias']->getClientId();
        $this->username = $DIC->user()->getFullname();
        
    }
    
    public function getName()
    {
        return 'MountPoint';
    }
    
    public function getChildren()
    {
        // TODO: Check for permissions
        if($this->user->getId() != ANONYMOUS_USER_ID)
        {
            return array(new ilClientNodeDAV($this->client_id, $this->repo_helper, $this->dav_helper));
        }
        else
        {
            throw new Forbidden('Only for logged in users');    
        }
    }

    public function getChild($name)
    {
        // TODO: Check for permissions AND correct client
        if($name == $this->client_id)
            return new ilClientNodeDAV($this->client_id, $this->repo_helper, $this->dav_helper);
        throw new NotFound();
    }

    public function childExists($name)
    {
        // TODO: Check for correct client
        if($name == $this->client_id)
            return true;
        return false;
    }

    public function getLastModified()
    {
        return strtotime('2000-01-01');
    }
    
    public function createDirectory($name)
    {
        throw new Forbidden("It is not possible to create a directory here");
    }

    public function createFile($name, $data = null)
    {
        throw new Forbidden("It is not possible to create a file here");
    }
    public function setName($name)
    {
        throw new Forbidden("It is not possible to change the name of the root");
    }

    public function delete()
    {
        throw new Forbidden("It is not possible to delete the root");
    }
}