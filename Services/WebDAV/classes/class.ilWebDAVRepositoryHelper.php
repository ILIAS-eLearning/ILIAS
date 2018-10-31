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
     * @param ilAccess $access
     * @param ilTree $tree
     */
    public function __construct(ilAccess $access, ilTree $tree)
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
    public function deleteObject($a_ref_id)
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
    public function checkAccess($a_permission, $a_ref_id)
    {
        return $this->access->checkAccess($a_permission, '', $a_ref_id);
    }

    /**
     * Just a redirect to the ilObject::_exists
     *
     * @param $a_ref_id
     * @return bool
     */
    public function objectWithRefIdExists($a_ref_id)
    {
        return ilObject::_exists($a_ref_id, true);
    }

    /**
     * Just a redirect to the ilObject::_lookupObjectId
     *
     * @param $a_ref_id
     * @return int+
     */
    public function getObjectIdFromRefId($a_ref_id)
    {
        return ilObject::_lookupObjectId($a_ref_id);
    }

    /**
     * Just a shortcut and redirect to get a title from a given ref_id
     *
     * @param $a_ref_id
     * @return mixed
     */
    public function getObjectTitleFromRefId($a_ref_id)
    {
        $obj_id = $this->getObjectIdFromRefId($a_ref_id);
        return ilObject::_lookupTitle($obj_id);
    }

    /**
     * Just a redirect to the ilObject::_lookupType
     *
     * @param $a_ref_id
     * @return mixed
     */
    public function getObjectTypeFromRefId($a_ref_id)
    {
        return ilObject::_lookupType($a_ref_id, true);
    }

    /**
     * Just a redirect to getChildIds of ilTree
     *
     * @param $a_ref_id
     * @return mixed
     */
    public function getChildrenOfRefId($a_ref_id)
    {
        return $this->tree->getChildIds($a_ref_id);
    }

    /**
     * @param $a_title
     * @return bool
     * @throws ilFileUtilsException
     */
    public function isValidFileNameWithValidFileExtension($a_title)
    {
        include_once("./Services/Utilities/classes/class.ilFileUtils.php");
        return $a_title == ilFileUtils::getValidFilename($a_title);
    }
}