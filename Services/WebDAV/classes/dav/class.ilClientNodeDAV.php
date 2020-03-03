<?php

use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;

/**
 * Class ilClientNodeDAV
 *
 * This class represents the used ilias client. For example if your clients
 * name is "my_ilias" and you are currently in the directory with the ref_id=123,
 * the path would look like this: ilias.mysite.com/webdav.php/my_ilias/ref_123/
 *
 * The call would look like this:
 * -> webdav.php <- creates the request handler and initialize ilias
 * -> ilWebDAVRequestHandler <- setup the webdav server
 * -> ilObjMountPointDAV <- This represents the "root" node and is needed for sabreDAV
 * -> ilMountPointDAV <- This class represents the used client (for example here it is my_ilias)
 * -> child of ilContainerDAV
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @implements Sabre\DAV\ICollection
 *
 */
class ilClientNodeDAV implements Sabre\DAV\ICollection
{
    /** @var ilWebDAVRepositoryHelper */
    protected $repo_helper;

    /** @var string */
    protected $name_of_repository_root;
    
    /**
     * @param string $client_name
     */
    public function __construct(string $client_name, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        global $DIC;
        
        $this->repo_helper = $repo_helper;
        $this->dav_helper = $dav_helper;
        $this->client_name = $client_name;
        $this->name_of_repository_root = 'ILIAS';
    }

    /**
     * Overwrite parent function to throw an exception if called. It is not forbidden to rename the client over WebDAV
     *
     * @param string $name
     * @throws Forbidden
     */
    public function setName($name)
    {
        throw new Forbidden("You cant change the client name");
    }

    /**
     * Returns Repository Root Object. Array is needed since a list of all children is expected.
     *
     * @return array|\Sabre\DAV\INode[]
     * @throws Forbidden
     */
    public function getChildren()
    {
        return array($this->getRepositoryRootPoint());
    }

    /**
     * Return name of client
     *
     * @return string
     */
    public function getName()
    {
        return $this->client_name;
    }

    /**
     * Returns some date as return for last modified
     *
     * @return false|int|null
     */
    public function getLastModified()
    {
        return strtotime('2000-01-01');
    }

    /**
     * If the "ILIAS" is given as parameter, the repository root will be returned. Such an URL would look like this:
     * https://ilias.de/webdav.php/client/ILIAS/
     *
     * Otherwise, the given name will be inspected if it is a reference ID of a collection/container. If call is valid
     * and permissions are granted, the collection/container will be returned. Such an URL would look like this:
     * https://ilias.de/webdav.php/client/ref_12345/
     *
     * @param string $name
     * @return ilObjCategoryDAV|ilObjCourseDAV|ilObjFileDAV|ilObjFolderDAV|ilObjGroupDAV|ilObjRepositoryRootDAV|\Sabre\DAV\INode
     * @throws BadRequest
     * @throws Forbidden
     */
    public function getChild($name)
    {
        if ($name == $this->name_of_repository_root) {
            return $this->getRepositoryRootPoint();
        } else {
            return $this->getMountPointByReference($name);
        }
    }

    /**
     * Create DAV-Object from ref_id
     *
     * @param string $name
     * @throws Forbidden
     * @throws BadRequest
     * @return ilObjCategoryDAV|ilObjCourseDAV|ilObjGroupDAV|ilObjFolderDAV|ilObjFileDAV
     */
    protected function getMountPointByReference($name)
    {
        $ref_id = $this->getRefIdFromName($name);
        
        if ($ref_id > 0) {
            if ($this->repo_helper->checkAccess('read', $ref_id)) {
                return $this->dav_helper->createDAVObjectForRefId($ref_id);
            }

            throw new Forbidden("No read permission for object with reference ID $ref_id ");
        }
        
        throw new BadRequest("Invalid parameter $name");
    }

    /**
     * Creates and returns Repository Root Object
     * @return ilObjRepositoryRootDAV
     * @throws Forbidden
     */
    protected function getRepositoryRootPoint()
    {
        if ($this->repo_helper->checkAccess('read', ROOT_FOLDER_ID)) {
            return new ilObjRepositoryRootDAV($this->name_of_repository_root, $this->repo_helper, $this->dav_helper);
        }
        throw new Forbidden("No read permission for ilias repository root");
    }
    
    /**
     * Either the given name is the name of the repository root of ILIAS
     * or it is a reference to a node in the ILIAS-repo
     *
     * Returns true if name=name of repository root or if given reference
     * exists and user has read permissions to this reference
     *
     */
    public function childExists($name)
    {
        if ($name == $this->name_of_repository_root) {
            return true;
        }
        
        $ref_id = $this->getRefIdFromName($name);
        if ($ref_id > 0) {
            return $this->repo_helper->objectWithRefIdExists($ref_id) && $this->repo_helper->checkAccess('read', $ref_id);
        }
        return false;
    }

    /**
     * Gets ref_id from name. Name should look like this: ref_<ref_id>
     *
     * @param string $name
     * @return int
     */
    public function getRefIdFromName($name)
    {
        $ref_parts = explode('_', $name);
        if (count($ref_parts) == 2) {
            $ref_id = (int) $ref_parts[1];
            return $this->checkIfRefIdIsValid($ref_id);
        }
        
        return 0;
    }

    /**
     * Check if object with ref_id exists and if is DAVable object
     *
     * @param $ref_id
     * @return mixed
     */
    protected function checkIfRefIdIsValid($ref_id)
    {
        if ($ref_id > 0 && $this->repo_helper->objectWithRefIdExists($ref_id) && $this->dav_helper->isDAVableObject($ref_id, true)) {
            return $ref_id;
        }
    }

    /**
     * It is not allowed to create a directory here
     *
     * @param string $name
     * @throws Forbidden
     */
    public function createDirectory($name)
    {
        throw new Forbidden();
    }

    /**
     * It is not allowed to delete anything here
     *
     * @throws Forbidden
     */
    public function delete()
    {
        throw new Forbidden();
    }

    /**
     * It is not allowed (and not even possible) to create a file here
     *
     * @param string $name
     * @param null $data
     * @return null|string|void
     * @throws Forbidden
     */
    public function createFile($name, $data = null)
    {
        throw new Forbidden();
    }
}
