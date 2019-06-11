<?php

/**
 * Class ilWebDAVRepositoryHelper
 *
 * This is a helper class and mostly also a wrapper class for repository actions. It is used by ilObj*DAV objects and
 * makes them more unit testable. This is really helpful since static calls like ilObject::_exists() are not mockable
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVRepositoryHelper
{
    /** @var ilAccess $access */
    protected $access;

    /** @var ilTree $tree */
    protected $tree;

    /**
     * ilWebDAVRepositoryHelper constructor.
     *
     * @param ilAccessHandler $access
     * @param ilTree $tree
     */
    public function __construct(ilAccessHandler $access, ilTree $tree)
    {
        $this->access = $access;
        $this->tree = $tree;
    }

    /**
     * I stole this method of deleting objects from ilObjectGUI->confirmedDeleteObject()
     *
     * @param $a_ref_id ref_id of object to delete
     * @throws ilRepositoryException
     */
    public function deleteObject(int $a_ref_id)
    {
        include_once("./Services/Repository/classes/class.ilRepUtil.php");
        $repository_util = new ilRepUtil($this);
        $parent = $this->tree->getParentId($a_ref_id);
        $repository_util->deleteObjects($parent, array($a_ref_id));
    }

    /**
     * Just a redirect to the checkAccess method of ilAccess
     *
     * @param $a_permission
     * @param $a_ref_id
     * @return bool
     */
    public function checkAccess(string $a_permission, int $a_ref_id) : bool
    {
        return $this->access->checkAccess($a_permission, '', $a_ref_id);
    }

    /**
     * Just a redirect to the checkAccess method of ilAccess to check for creation of certain obj types
     *
     * @param int $a_ref_id
     * @param string $a_type
     * @return bool
     */
    public function checkCreateAccessForType(int $a_ref_id, string $a_type) : bool
    {
        return $this->access->checkAccess('create', '', $a_ref_id, $a_type);
    }

    /**
     * Just a redirect to the ilObject::_exists
     *
     * @param $a_ref_id
     * @return bool
     */
    public function objectWithRefIdExists(int $a_ref_id) : int
    {
        return ilObject::_exists($a_ref_id, true);
    }

    /**
     * Just a redirect to the ilObject::_lookupObjectId function
     *
     * @param $a_ref_id
     * @return int+
     */
    public function getObjectIdFromRefId(int $a_ref_id) : int
    {
        return ilObject::_lookupObjectId($a_ref_id);
    }

    /**
     * Just a redirect to the ilObject::_lookupTitle function
     *
     * @param $a_obj_id
     * @return
     */
    public function getObjectTitleFromObjId(int $a_obj_id, bool $escape_forbidden_fileextension = false) : string
    {
        if($escape_forbidden_fileextension && ilObject::_lookupType($a_obj_id))
        {
            $title = ilFileUtils::getValidFilename(ilObject::_lookupTitle($a_obj_id));
        }
        else
        {
            $title = ilObject::_lookupTitle($a_obj_id);
        }

        return $title === NULL ? '' : $title;
    }

    /**
     * Just a redirect to the ilObject::_lookupType function
     *
     * @param $a_ref_id
     * @return mixed
     */
    public function getObjectTypeFromObjId(int $a_obj_id) : string
    {
    	$type = ilObject::_lookupType($a_obj_id, false);
        return $type === NULL ? '' : $type;
    }


    /**
     * Just a shortcut and redirect to get a title from a given ref_id
     *
     * @param $a_ref_id
     * @return mixed
     */
    public function getObjectTitleFromRefId(int $a_ref_id, bool $escape_forbidden_fileextension = false) : string
    {
        $obj_id = $this->getObjectIdFromRefId($a_ref_id);

        return $this->getObjectTitleFromObjId($obj_id, $escape_forbidden_fileextension);
    }

    /**
     * Just a redirect to the ilObject::_lookupType function
     *
     * @param $a_ref_id
     * @return mixed
     */
    public function getObjectTypeFromRefId(int $a_ref_id) : string
    {
    	$type = ilObject::_lookupType($a_ref_id, true);
        return $type === NULL ? '' : $type;
    }

    /**
     * Just a redirect to getChildIds of ilTree
     *
     * @param $a_ref_id
     * @return mixed
     */
    public function getChildrenOfRefId(int $a_ref_id)
    {
        return $this->tree->getChildIds($a_ref_id);
    }
}