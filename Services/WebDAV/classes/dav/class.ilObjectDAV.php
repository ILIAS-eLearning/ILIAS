<?php

use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotImplemented;

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
    function __construct(ilObject $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        global $DIC;
        
        $this->obj =& $a_obj;
        $this->ref_id = $a_obj->getRefId();

        $this->dav_helper = $dav_helper;
        $this->repo_helper = $repo_helper;
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
        if($this->repo_helper->checkAccess('delete', $this->obj->getRefId()))
        {
            $this->repo_helper->deleteObject($this->ref_id);
            /* use same functionality as in ilObjectGUI
            include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
            $ru = new ilRepUtilGUI($this);
            $ru->deleteObjects($_GET["ref_id"], ilSession::get("saved_post"));
            //*/
        }
        else 
        {
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
    function setName($a_name)
    {
        if($this->repo_helper->checkAccess("write", $this->obj->getRefId()))
        {
            if(!$this->repo_helper->isTitleContainingInvalidCharacters($a_name))
            {
                $this->obj->setTitle($a_name);
                $this->obj->update();
            }
            else
            {
                throw new Forbidden('Forbidden characters in title');
            }
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
    
    public static function _containsInvalidCharacters($a_name)
    {
        foreach(self::$forbidden_characters as $forbidden_char)
        {
            if(strpos($forbidden_char, $a_name) !== false)
            {
                return true;
            }
        }
        return false;
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
