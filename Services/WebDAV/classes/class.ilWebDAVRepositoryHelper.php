<?php

class ilWebDAVRepositoryHelper
{
    /** @var ilAccess $access */
    protected $access;

    /** @var ilTree $tree */
    protected $tree;

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

    public function checkAccess($a_permission, $a_ref_id)
    {
        return $this->access->checkAccess($a_permission, '', $a_ref_id);
    }

    public function objectWithRefIdExists($a_ref_id)
    {
        return ilObject::_exists($a_ref_id, true);
    }

    public function getObjectIdFromRefId($a_ref_id)
    {
        return ilObject::_lookupObjectId($a_ref_id);
    }

    public function getObjectTitleFromRefId($a_ref_id)
    {
        $obj_id = $this->getObjectIdFromRefId($a_ref_id);
        return ilObject::_lookupTitle($obj_id);
    }

    public function getObjectTypeFromRefId($a_ref_id)
    {
        return ilObject::_lookupType($a_ref_id, true);
    }

    public function getChildrenOfRefId($a_ref_id)
    {
        return $this->tree->getChildIds($a_ref_id);
    }

    public function isTitleContainingInvalidCharacters($a_name)
    {
        return false;
    }

    public function isValidFileNameWithValidFileExtension($a_title)
    {
        include_once("./Services/Utilities/classes/class.ilFileUtils.php");
        return $a_title == ilFileUtils::getValidFilename($a_title);
    }
}