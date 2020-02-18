<?php

use Sabre\CalDAV\Notifications\ICollection;
use ILIAS\UI\NotImplementedException;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\Forbidden;

/**
 * Class ilMountPointDAV
 *
 * This class represents the absolut Root-Node on a WebDAV request. If for example following URL is called:
 * https://ilias.de/webdav.php/client/ref_1234/folder
 * this class represents the very first '/' slash after "webdav.php".
 *
 * This kind of procedure is needed for the way how sabreDAV works
 *
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @implements Sabre\DAV\ICollection
 */
class ilMountPointDAV implements Sabre\DAV\ICollection
{
    /** @var ilAccessHandler */
    protected $access;
    
    /** @var string */
    protected $client_id;

    /** @var int */
    protected $user_id;

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
        $this->user_id = $DIC->user()->getId();
    }

    /**
     * Return MountPoint as name. This method won't be called anyway
     *
     * @return string
     */
    public function getName()
    {
        return 'MountPoint';
    }

    /**
     * Returns client node if user exists and is not anonymous
     *
     * There is no object permission to check
     *
     * @return array|\Sabre\DAV\INode[]
     * @throws Forbidden
     */
    public function getChildren()
    {
        if ($this->user_id != null && $this->user_id != ANONYMOUS_USER_ID) {
            return array(new ilClientNodeDAV($this->client_id, $this->repo_helper, $this->dav_helper));
        } else {
            throw new Forbidden('Only for logged in users');
        }
    }

    /**
     * Returns Client Node if Client ID is correct
     *
     * No permissions to check here since Client node is not an object
     *
     * @param string $name
     * @return ilClientNodeDAV|\Sabre\DAV\INode
     * @throws NotFound
     */
    public function getChild($name)
    {
        if ($name == $this->client_id) {
            return new ilClientNodeDAV($this->client_id, $this->repo_helper, $this->dav_helper);
        }
        throw new NotFound();
    }

    /**
     * Check if given name matches the used Client ID
     *
     * @param string $name
     * @return bool
     */
    public function childExists($name)
    {
        if ($name == $this->client_id) {
            return true;
        }
        return false;
    }

    /**
     * Return a default date as LastModified
     *
     * @return false|int|null
     */
    public function getLastModified()
    {
        return strtotime('2000-01-01');
    }

    /**
     * It is not allowed (not even possible) to create a directory here
     *
     * @param string $name
     * @throws Forbidden
     */
    public function createDirectory($name)
    {
        throw new Forbidden("It is not possible to create a directory here");
    }

    /**
     * It is not allowed (not even possible) to create a file here
     *
     * @param string $name
     * @param null $data
     * @return null|string|void
     * @throws Forbidden
     */
    public function createFile($name, $data = null)
    {
        throw new Forbidden("It is not possible to create a file here");
    }

    /**
     * It is not possible to set the name for the MountPoint
     *
     * @param string $name
     * @throws Forbidden
     */
    public function setName($name)
    {
        throw new Forbidden("It is not possible to change the name of the root");
    }

    /**
     * It is not possible to delete the MountPoint
     *
     * @throws Forbidden
     */
    public function delete()
    {
        throw new Forbidden("It is not possible to delete the root");
    }
}
