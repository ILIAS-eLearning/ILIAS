<?php

use Sabre\DAV;
use ILIAS\UI\NotImplementedException;
use Sabre\DAV\Exception;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\NotImplemented;
use Sabre\DAV\Exception\Forbidden;

abstract class ilObjContainerDAV extends ilObjectDAV implements Sabre\DAV\ICollection
{
    /**
     * @param ilContainer $a_obj
     */
    public function __construct(ilContainer $a_obj)
    {
        parent::__construct($a_obj);
    }

    
    /**
     * Creates a new file in the directory
     *
     * Data will either be supplied as a stream resource, or in certain cases
     * as a string. Keep in mind that you may have to support either.
     *
     * After successful creation of the file, you may choose to return the ETag
     * of the new file here.
     *
     * The returned ETag must be surrounded by double-quotes (The quotes should
     * be part of the actual string).
     *
     * If you cannot accurately determine the ETag, you should not return it.
     * If you don't store the file exactly as-is (you're transforming it
     * somehow) you should also not return an ETag.
     *
     * This means that if a subsequent GET to this new file does not exactly
     * return the same contents of what was submitted here, you are strongly
     * recommended to omit the ETag.
     *
     * @param string $name Name of the file
     * @param resource|string $data Initial payload
     * @return null|string
     */
    public function createFile($name, $data = null)
    {
        if($this->access->checkAccess("write", '', $this->obj->getRefId()))
        {
            // Check if file has valid extension
            include_once("./Services/Utilities/classes/class.ilFileUtils.php");
            if($name != ilFileUtils::getValidFilename($name))
            {
                // Throw forbidden if invalid exstension. As far as we know, it is sadly not
                // possible to inform the user why this is forbidden.
                //ilLoggerFactory::getLogger('WebDAV')->warning(get_class($this). ' ' . $this->obj->getTitle() ." -> invalid File-Extension for file '$name'");
                //throw new Forbidden("Invalid file extension. But you won't see this anyway...");
                $name = ilFileUtils::getValidFilename($name);
            }
            
            // Maybe getChild is more efficient. But hoping for an exception isnt that beautiful
            if($this->childExists($name))
            {
                $file_dav = $this->getChild($name);
                $file_dav->handleFileUpload($data);
            }
            else 
            {
                $file_obj = new ilObjFile();
                $file_obj->setTitle($name);
                $file_obj->setFileName($name);
                $file_obj->setVersion(1);
                $file_obj->createDirectory();
                $file_obj->create();
    
                $file_obj->createReference();
                $file_obj->putInTree($this->obj->getRefId());
                $file_obj->update();
                
                $file_dav = new ilObjFileDAV($file_obj);
                $file_dav->handleFileUpload($data);
                }
            }

        else 
        {
            throw new Forbidden("No write access");
        }

        return $file_dav->getETag();
    }
    
    /**
     * Creates a new subdirectory
     *
     * @param string $name
     * @return void
     */
    public function createDirectory($name)
    {
        global $DIC;
        
        $new_obj;
        $type = $this->getChildCollectionType();
        
        switch($type)
        {
            case 'cat':
                $new_obj = new ilObjCategory();
                break;
                
            case 'fold':
                $new_obj = new ilObjFolder();
                break;
            
            default:
                ilLoggerFactory::getLogger('WebDAV')->info(get_class($this). ' ' . $this->obj->getTitle() ." -> $type is not supported as webdav directory");
                throw new NotImplemented("Create type '$type' as collection is not implemented yet");
        }
        
        $new_obj->setType($type);
        $new_obj->setOwner($DIC->user()->getId());
        $new_obj->setTitle($name);
        $new_obj->create();
        
        $new_obj->createReference();
        $new_obj->putInTree($this->obj->getRefId());
        $new_obj->setPermissions($this->obj->getRefId());
        $new_obj->update();
    }
    
    /**
     * Returns a specific child node, referenced by its name
     *
     * This method must throw Sabre\DAV\Exception\NotFound if the node does not
     * exist.
     *
     * @param string $name
     * @return Sabre\DAV\INode
     */
    public function getChild($name)
    {
        $child_node = NULL;
        $child_exists = false;
        foreach($this->tree->getChildIds($this->obj->getRefId()) as $child_ref)
        {
            // Check if a DAV Object exists for this type
            if(self::_isDAVableObject($child_ref, true))
            {
                // Check if names matches
                $child_obj_id = ilObject::_lookupObjectId($child_ref);
                $child_title = ilObject::_lookupTitle($child_obj_id);
                if($child_title == $name)
                {
                    $child_exists = true;
                    
                    // Check if user has permission to read this object
                    if($this->access->checkAccess("read", "", $child_ref))
                    {
                        $child_node = self::_createDAVObjectForRefId($child_ref);
                    }
                }
            }
        }
        
        // There exists 1 or more nodes with this name. Return last found node.
        if(!is_null($child_node))
        {
            return $child_node;
        }
        
        // There is no davable object with the same name. Sorry for you...
        throw new Sabre\DAV\Exception\NotFound("$name not found");
    }
    
    /**
     * Returns an array with all the child nodes
     *
     * @return ilObject[]
     */
    public function getChildren()
    {
        
        $child_nodes = array();
        foreach($this->tree->getChildIds($this->obj->getRefId()) as $child_ref)
        {
            // Check if is davable object types
            if(self::_isDAVableObject($child_ref, true))
            {
                // Check if read permission is given
                if($this->access->checkAccess("read", "", $child_ref))
                {
                    // Create DAV-object out of ILIAS-object
                    $child_obj_dav = self::_createDAVObjectForRefId($child_ref);
                    if($child_obj_dav != null)
                    {
                        $child_nodes[$child_ref] = $child_obj_dav;
                    }
                }
            }
        }
        return $child_nodes;
    }
    
    /**
     * Checks if a child-node with the specified name exists
     *
     * @param string $name
     * @return bool
     */
    public function childExists($name)
    {
        foreach($this->tree->getChildIds($this->obj->getRefId()) as $child_ref)
        {
            // Only davable object types
            if(self::_isDAVableObject($child_ref, true))
            {
                // Check if names are the same
                $obj_id = ilObject::_lookupObjId($child_ref);
                if(ilObject::_lookupTitle($obj_id) == $name)
                {
                    // Check if read permission is given
                    if($this->access->checkAccess("read", '', $child_ref))
                    {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Return the type for child collections of this collection
     * For courses, groups and folders the type is 'fold'
     * For categories the type is 'cat'
     * 
     * @return string $type
     */
    public abstract function getChildCollectionType();
}