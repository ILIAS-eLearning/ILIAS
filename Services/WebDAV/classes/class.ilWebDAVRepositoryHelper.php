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

    public function deleteObject($a_ref_id)
    {

        global $DIC;
        $tree = $DIC['tree'];
        $rbacadmin = $DIC['rbacadmin'];

        $subnodes = $this->tree->getSubTree($this->tree->getNodeData($a_ref_id));
        foreach ($subnodes as $node)
        {
            $rbacadmin->revokePermission($node["child"]);
            $affectedUsers = ilUtil::removeItemFromDesktops($node["child"]);
        }
        $this->tree->saveSubTree($a_ref_id);
        $this->tree->deleteTree($this->tree->getNodeData($a_ref_id));





        // This are the same steps as in ilObjectGUI
        try {
            include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
            $ru = new ilRepUtilGUI($this);
            $ru->deleteObjects($a_ref_id, ilSession::get("saved_post"));
        } catch (Exception $e)
        {
            file_put_contents('webdav.log', $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine(), FILE_APPEND);
        }
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