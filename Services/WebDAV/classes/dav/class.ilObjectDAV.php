<?php

use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotImplemented;

abstract class ilObjectDAV extends Sabre\DAV\Node
{
    /**
     * Refid to the object.
     * 
     * @var $ref_id integer
     */
    protected $ref_id;
    
    /**
     * Application layer object.
     * 
     * @var $obj ilObject
     */
    protected $obj;
    
    /**
     * 
     * @var $tree ilTree
     */
    protected $tree;
    
    /**
     * 
     * @var $access ilAccessHandler
     */
    protected $access;
    
    /**
     * Constructor for DAV Object
     * 
     * Note: There is a good reason why I want an ILIAS-Object in the constructor and not a ref_id.
     * This is because every instance of ilObjectDAV and its inherited children
     * represent an ILIAS-object for WebDAV. If there isnt an ILIAS-object there is
     * no object to represent for WebDAV.
     *
     * @param ilObject $a_obj
     */
    function __construct(ilObject $a_obj)
    {
        global $DIC;
        
        $this->obj =& $a_obj;
        $this->ref_id = $a_obj->getRefId();
        
        
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
    }
    
    /**
     * Returns the ref id of this object.
     * @return int.
     */
    function getRefId()
    {
        return $this->ref_id;
    }
    
    /**
     * Returns the object id of this object.
     * @return int.
     */
    function getObjectId()
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
    function getLastModified() {
        
        return ($this->obj == null) ? null : strtotime($this->obj->getLastUpdateDate());
    }
    
    /**
     * Deletes the current node
     *
     * @throws Sabre\DAV\Exception\Forbidden
     * @return void
     */
    public function delete()
    {
        if($this->access->checkAccess('delete', '', $this->obj->getRefId()))
        {
            $this->tree->moveToTrash($this->obj->getRefId());
        }
        else 
        {
            throw new Forbidden("No delete permission for $this->getName()");
        }
    }
    
    /**
     * Renames the node
     *
     * @param string $a_name The new name
     * @throws Sabre\DAV\Exception\Forbidden
     * @return void
     */
    function setName($a_name)
    {
        if($this->access->checkAccess("write", '', $this->obj->getRefId()))
        {
            $this->obj->setTitle($a_name);
            $this->obj->update();
        }
        else 
        {
            throw new Forbidden('Permission denied');
        }
    }
    
    /**
     * SabreDAV interface function
     * {@inheritDoc}
     * @see \Sabre\DAV\INode::getName()
     */
    function getName()
    {
        return $this->obj->getTitle();
    }
    
    /**
     * Returns ILIAS Object
     * 
     * @return ilObject
     */
    function getObject()
    {
        return $this->obj;
    }
    
    /**
     * Creates a DAV Object for the given ref id
     *
     * @param integer $ref_id
     * @param string $type
     */
    public static function _createDAVObjectForRefId($ref_id, $type = '')
    {
        if($type == '')
        {
            $type = ilObject::_lookupType($ref_id, true);
        }
        
        if(ilObject::_exists($ref_id, true, $type))
        {
            switch($type)
            {
                case 'cat':
                    return new ilObjCategoryDAV(new ilObjCategory($ref_id, true));
                    break;
                    
                case 'crs':
                    return new ilObjCourseDAV(new ilObjCourse($ref_id, true));
                    break;
                    
                case 'grp':
                    return new ilObjGroupDAV(new ilObjGroup($ref_id, true));
                    break;
                    
                case 'fold':
                    return new ilObjFolderDAV(new ilObjFolder($ref_id, true));
                    break;
                    
                case 'file':
                    return new ilObjFileDAV(new ilObjFile($ref_id, true));
                    break;
            }

            throw new BadRequest();
        }
        return null;
    }
    
    /**
     * Checks if there is a DAV-Object for the given type
     *
     * @param string $type
     * @return boolean
     */
    public static function _isDAVableObjectType($type)
    {
        switch($type)
        {
            case 'cat':
            case 'crs':
            case 'grp':
            case 'fold':
            case 'file':
                return true;
                
            default:
                return false;
        }
    }
    
    /**
     * 
     * @param int $id
     * @param boolean $is_reference
     * 
     * @return boolean
     */
    public static function _isDAVableObject($id, $is_reference = false)
    {
        return self::_isDAVableObjectType(ilObject::_lookupType($id, $is_reference));
    }
    
}
