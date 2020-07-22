<?php

use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotImplemented;

/**
 * Class ilObjectDAV
 *
 * Base implementation for all ILIAS objects to be represented as a WebDAV object
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @extends Sabre\DAV\Node
 */
abstract class ilObjectDAV extends Sabre\DAV\Node
{
    /** @var $ref_id integer */
    protected $ref_id;
    
    /** @var $obj ilObject */
    protected $obj;
    
    /** @var ilWebDAVRepositoryHelper $repo_helper */
    protected $repo_helper;

    /** @var ilWebDAVObjDAVHelper */
    protected $dav_helper;

    /**
     * Constructor for DAV Object
     *
     * Note: There is a good reason why I want an ILIAS-Object in the constructor and not a ref_id.
     * This is because every instance of ilObjectDAV and its inherited children
     * represent an ILIAS-object for WebDAV. If there isn't an ILIAS-object there is
     * no object to represent for WebDAV.
     *
     * @param ilObject $a_obj
     */
    public function __construct(ilObject $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        global $DIC;
        
        $this->obj = &$a_obj;
        $this->ref_id = $a_obj->getRefId();

        $this->dav_helper = $dav_helper;
        $this->repo_helper = $repo_helper;
    }
    
    /**
     * Returns the ref id of this object.
     * @return int.
     */
    public function getRefId()
    {
        return $this->ref_id;
    }
    
    /**
     * Returns the object id of this object.
     * @return int.
     */
    public function getObjectId()
    {
        return ($this->obj == null) ? null : $this->obj->getId();
    }
    
    /**
     * Returns the last modification time as a unix timestamp.
     *
     * If the information is not available, return null.
     *
     * @return int
     */
    public function getLastModified()
    {
        return ($this->obj == null) ? null : strtotime($this->obj->getLastUpdateDate());
    }

    /**
     * Deletes the current node
     *
     * @throws Sabre\DAV\Exception\Forbidden
     * @throws ilRepositoryException
     * @return void
     */
    public function delete()
    {
        if ($this->repo_helper->checkAccess('delete', $this->ref_id)) {
            $this->repo_helper->deleteObject($this->ref_id);
        } else {
            throw new Forbidden("Permission denied");
        }
    }
    
    /**
     * Renames the node
     *
     * @param string $a_name The new name
     * @throws Sabre\DAV\Exception\Forbidden
     * @return void
     */
    public function setName($a_name)
    {
        if ($this->repo_helper->checkAccess("write", $this->obj->getRefId())) {
            if ($this->dav_helper->isDAVableObjTitle($a_name)) {
                $this->obj->setTitle($a_name);
                $this->obj->update();
            } else {
                throw new Forbidden('Forbidden characters in title');
            }
        } else {
            throw new Forbidden('Permission denied');
        }
    }
    
    /**
     * SabreDAV interface function
     * {@inheritDoc}
     * @see \Sabre\DAV\INode::getName()
     */
    public function getName()
    {
        return $this->obj->getTitle();
    }
    
    /**
     * Returns ILIAS Object
     *
     * @return ilObject
     */
    public function getObject()
    {
        return $this->obj;
    }
}
