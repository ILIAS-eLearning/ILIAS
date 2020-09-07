<?php

use Sabre\DAV;
use ILIAS\UI\NotImplementedException;
use Sabre\DAV\Exception;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\NotImplemented;
use Sabre\DAV\Exception\Forbidden;

/**
 * Class ilObjContainerDAV
 *
 * Base implementation for container objects to be represented as WebDAV collection.
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * @extends ilObjectDAV
 * @implements Sabre\DAV\ICollection
 */
abstract class ilObjContainerDAV extends ilObjectDAV implements Sabre\DAV\ICollection
{
    /**
     * Check if given object has valid type and calls parent constructor
     *
     * @param ilContainer $a_obj
     */
    public function __construct(ilContainer $a_obj, ilWebDAVRepositoryHelper $repo_helper, ilWebDAVObjDAVHelper $dav_helper)
    {
        parent::__construct($a_obj, $repo_helper, $dav_helper);
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
     * @throws Exception\BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function createFile($name, $data = null)
    {
        if ($this->repo_helper->checkCreateAccessForType($this->obj->getRefId(), 'file')) {
            // Check if file has valid extension
            if ($this->dav_helper->isValidFileNameWithValidFileExtension($name)) {
                if ($this->childExists($name)) {
                    $file_dav = $this->getChild($name);
                    $file_dav->handleFileUpload($data);
                } else {
                    $file_obj = new ilObjFile();
                    $file_obj->setTitle($name);
                    $file_obj->setFileName($name);
                    $file_obj->setVersion(1);
                    $file_obj->setMaxVersion(1);
                    $file_obj->createDirectory();
                    $file_obj->create();

                    $file_obj->createReference();
                    $file_obj->putInTree($this->obj->getRefId());
                    $file_obj->setPermissions($this->ref_id);
                    $file_obj->update();

                    $file_dav = new ilObjFileDAV($file_obj, $this->repo_helper, $this->dav_helper);
                    $file_dav->handleFileUpload($data);
                }
            } else {
                // Throw forbidden if invalid extension or filename. As far as we know, it is sadly not
                // possible to inform the user why his upload was "forbidden".
                throw new Forbidden('Invalid file name or file extension');
            }
        } else {
            throw new Forbidden('No write access');
        }

        return $file_dav->getETag();
    }

    /**
     * Creates a new subdirectory
     *
     * @param string $name
     * @return void
     * @throws NotImplemented
     */
    public function createDirectory($name)
    {
        global $DIC;

        $type = $this->getChildCollectionType();
        if ($this->repo_helper->checkCreateAccessForType($this->getRefId(), $type) && $this->dav_helper->isDAVableObjTitle($name)) {
            switch ($type) {
                case 'cat':
                    $new_obj = new ilObjCategory();
                    break;

                case 'fold':
                    $new_obj = new ilObjFolder();
                    break;

                default:
                    ilLoggerFactory::getLogger('WebDAV')->info(get_class($this) . ' ' . $this->obj->getTitle() . " -> $type is not supported as webdav directory");
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
        } else {
            throw new Forbidden();
        }
    }
    
    /**
     * Returns a specific child node, referenced by its name
     *
     * This method must throw Sabre\DAV\Exception\NotFound if the node does not
     * exist.
     *
     * @throws Exception\NotFound Exception\BadRequest
     * @param string $name
     * @return Sabre\DAV\INode
     */
    public function getChild($name)
    {
        $child_node = null;
        $child_exists = false;
        foreach ($this->repo_helper->getChildrenOfRefId($this->obj->getRefId()) as $child_ref) {
            // Check if a DAV Object exists for this type
            if ($this->dav_helper->isDAVableObject($child_ref, true)) {
                // Check if names matches
                if ($this->repo_helper->getObjectTitleFromRefId($child_ref, true) == $name) {
                    $child_exists = true;
                    
                    // Check if user has permission to read this object
                    if ($this->repo_helper->checkAccess("read", $child_ref)) {
                        $child_node = $this->dav_helper->createDAVObjectForRefId($child_ref);
                    }
                }
            }
        }
        
        // There exists 1 or more nodes with this name. Return last found node.
        if (!is_null($child_node)) {
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
        foreach ($this->repo_helper->getChildrenOfRefId($this->obj->getRefId()) as $child_ref) {
            // Check if is davable object types
            if ($this->dav_helper->isDAVableObject($child_ref, true)) {
                // Check if read permission is given
                if ($this->repo_helper->checkAccess("read", $child_ref)) {
                    // Create DAV-object out of ILIAS-object
                    $child_nodes[$child_ref] = $this->dav_helper->createDAVObjectForRefId($child_ref);
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
        foreach ($this->repo_helper->getChildrenOfRefId($this->obj->getRefId()) as $child_ref) {
            // Only davable object types
            if ($this->dav_helper->isDAVableObject($child_ref, true)) {
                // Check if names are the same
                if ($this->repo_helper->getObjectTitleFromRefId($child_ref, true) == $name) {
                    // Check if read permission is given
                    if ($this->repo_helper->checkAccess("read", $child_ref)) {
                        return true;
                    } else {
                        /*
                         * This is an interesting edge case. What happens if there are 2 objects with the same name
                         * but User1 only has access to the first and user2 has only access to the second?
                         */
                        return false;
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
    abstract public function getChildCollectionType();
}
