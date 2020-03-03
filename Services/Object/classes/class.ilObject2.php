<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject.php");

/**
* Class ilObject2
* This is an intermediate progress of ilObject class. Please do not ust it yet.
*
* @author Stefan Meyer <meyer@leifos.com>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
abstract class ilObject2 extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_reference = true)
    {
        $this->initType();
        parent::__construct($a_id, $a_reference);
    }

    abstract protected function initType();
    
    final public function withReferences()
    {
        return parent::withReferences();
    }

    /**
    * Read data from db
    */
    final public function read()
    {
        parent::read();
        $this->doRead();
    }
    protected function doRead()
    {
    }
    
    public function getId()
    {
        return parent::getId();
    }
    public function setId($a_id)
    {
        return parent::setId($a_id);
    }
    final public function setRefId($a_id)
    {
        return parent::setRefId($a_id);
    }
    final public function getRefId()
    {
        return parent::getRefId();
    }
    final public function getType()
    {
        return parent::getType();
    }
    final public function setType($a_type)
    {
        return parent::setType($a_type);
    }
    final public function getPresentationTitle()
    {
        return parent::getPresentationTitle();
    }
    final public function getTitle()
    {
        return parent::getTitle();
    }
    final public function getUntranslatedTitle()
    {
        return parent::getUntranslatedTitle();
    }
    final public function setTitle($a_title)
    {
        return parent::setTitle($a_title);
    }
    final public function getDescription()
    {
        return parent::getDescription();
    }
    final public function setDescription($a_desc)
    {
        return parent::setDescription($a_desc);
    }
    final public function getLongDescription()
    {
        return parent::getLongDescription();
    }
    final public function getImportId()
    {
        return parent::getImportId();
    }
    final public function setImportId($a_import_id)
    {
        return parent::setImportId($a_import_id);
    }
    final public static function _lookupObjIdByImportId($a_import_id)
    {
        return parent::_lookupObjIdByImportId($a_import_id);
    }
    final public function getOwner()
    {
        return parent::getOwner();
    }
    final public function getOwnerName()
    {
        return parent::getOwnerName();
    }
    final public static function _lookupOwnerName($a_owner_id)
    {
        return parent::_lookupOwnerName($a_owner_id);
    }
    final public function setOwner($a_owner)
    {
        return parent::setOwner($a_owner);
    }
    final public function getCreateDate()
    {
        return parent::getCreateDate();
    }
    final public function getLastUpdateDate()
    {
        return parent::getLastUpdateDate();
    }

    final public function create($a_clone_mode = false)
    {
        if ($this->beforeCreate()) {
            $id = parent::create();
            if ($id) {
                $this->doCreate($a_clone_mode);
                return $id;
            }
        }
    }

    protected function doCreate()
    {
    }
    
    protected function beforeCreate()
    {
        return true;
    }
    
    final public function update()
    {
        if ($this->beforeUpdate()) {
            if (!parent::update()) {
                return false;
            }
            $this->doUpdate();
            
            return true;
        }
        
        return false;
    }

    protected function doUpdate()
    {
    }
    
    protected function beforeUpdate()
    {
        return true;
    }

    final public function MDUpdateListener($a_element)
    {
        if ($this->beforeMDUpdateListener($a_element)) {
            if (parent::MDUpdateListener($a_element)) {
                $this->doMDUpdateListener($a_element);
                return true;
            }
        }
        return false;
    }

    protected function doMDUpdateListener($a_element)
    {
    }

    protected function beforeMDUpdateListener($a_element)
    {
        return true;
    }

    final public function createMetaData()
    {
        if ($this->beforeCreateMetaData()) {
            if (parent::createMetaData()) {
                $this->doCreateMetaData();
                return true;
            }
        }
        return false;
    }

    protected function doCreateMetaData()
    {
    }

    protected function beforeCreateMetaData()
    {
        return true;
    }

    final public function updateMetaData()
    {
        if ($this->beforeUpdateMetaData()) {
            if (parent::updateMetaData()) {
                $this->doUpdateMetaData();
                return true;
            }
        }
        return false;
    }

    protected function doUpdateMetaData()
    {
    }

    protected function beforeUpdateMetaData()
    {
        return true;
    }
    
    final public function deleteMetaData()
    {
        return parent::deleteMetaData();
    }
    final public function updateOwner()
    {
        return parent::updateOwner();
    }
    final public static function _getIdForImportId($a_import_id)
    {
        return parent::_getIdForImportId($a_import_id);
    }
    final public static function _getAllReferences($a_id)
    {
        return parent::_getAllReferences($a_id);
    }
    final public static function _lookupTitle($a_id)
    {
        return parent::_lookupTitle($a_id);
    }
    final public static function _lookupOwner($a_id)
    {
        return parent::_lookupOwner($a_id);
    }
    final public static function _getIdsForTitle($title, $type = '', $partialmatch = false)
    {
        return parent::_getIdsForTitle($title, $type, $partialmatch);
    }
    final public static function _lookupDescription($a_id)
    {
        return parent::_lookupDescription($a_id);
    }
    final public static function _lookupLastUpdate($a_id, $a_as_string = false)
    {
        return parent::_lookupLastUpdate($a_id, $a_as_string);
    }
    final public static function _getLastUpdateOfObjects($a_objs)
    {
        return parent::_getLastUpdateOfObjects($a_objs);
    }
    final public static function _lookupObjId($a_id)
    {
        return parent::_lookupObjId($a_id);
    }
    final public static function _setDeletedDate($a_ref_id)
    {
        return parent::_setDeletedDate($a_ref_id);
    }
    final public static function _resetDeletedDate($a_ref_id)
    {
        return parent::_resetDeletedDate($a_ref_id);
    }
    final public static function _lookupDeletedDate($a_ref_id)
    {
        return parent::_lookupDeletedDate($a_ref_id);
    }
    final public static function _writeTitle($a_obj_id, $a_title)
    {
        return parent::_writeTitle($a_obj_id, $a_title);
    }
    final public static function _writeDescription($a_obj_id, $a_desc)
    {
        return parent::_writeDescription($a_obj_id, $a_desc);
    }
    final public static function _writeImportId($a_obj_id, $a_import_id)
    {
        return parent::_writeImportId($a_obj_id, $a_import_id);
    }
    final public static function _lookupType($a_id, $a_reference = false)
    {
        return parent::_lookupType($a_id, $a_reference);
    }
    final public static function _isInTrash($a_ref_id)
    {
        return parent::_isInTrash($a_ref_id);
    }
    final public static function _hasUntrashedReference($a_obj_id)
    {
        return parent::_hasUntrashedReference($a_obj_id);
    }
    final public static function _lookupObjectId($a_ref_id)
    {
        return parent::_lookupObjectId($a_ref_id);
    }
    final public static function _getObjectsDataForType($a_type, $a_omit_trash = false)
    {
        return parent::_getObjectsDataForType($a_type, $a_omit_trash);
    }
    final public function putInTree($a_parent_ref)
    {
        return parent::putInTree($a_parent_ref);
    }
    final public function setPermissions($a_parent_ref)
    {
        return parent::setPermissions($a_parent_ref);
    }
    final public function createReference()
    {
        return parent::createReference();
    }
    final public function countReferences()
    {
        return parent::countReferences();
    }

    final public function delete()
    {
        if ($this->beforeDelete()) {
            if (parent::delete()) {
                $this->doDelete();
                $this->id = null;
                return true;
            }
        }
        return false;
    }

    protected function doDelete()
    {
    }
    
    protected function beforeDelete()
    {
        return true;
    }

    public function initDefaultRoles()
    {
        return array();
    }
    
    final public static function _exists($a_id, $a_reference = false, $a_type = null)
    {
        return parent::_exists($a_id, $a_reference, $a_type);
    }
    final public function setRegisterMode($a_bool)
    {
        return parent::setRegisterMode($a_bool);
    }
    final public function isUserRegistered($a_user_id = 0)
    {
        return parent::isUserRegistered($a_user_id);
    }
    final public function requireRegistration()
    {
        return parent::requireRegistration();
    }
    //final function getXMLZip() { return parent::getXMLZip(); }
    //final function getHTMLDirectory() { return parent::getHTMLDirectory(); }
    final public static function _getObjectsByType($a_obj_type = "", $a_owner = "")
    {
        return parent::_getObjectsByType($a_obj_type, $a_owner);
    }
    
    final public static function _prepareCloneSelection($a_ref_ids, $new_type, $a_show_path = true)
    {
        return parent::_prepareCloneSelection($a_ref_ids, $new_type, $a_show_path);
    }
    final public function appendCopyInfo($a_target_id, $a_copy_id)
    {
        return parent::appendCopyInfo($a_target_id, $a_copy_id);
    }
    final public function cloneMetaData($target_obj)
    {
        return parent::cloneMetaData($target_obj);
    }
    
    final public function cloneObject($a_target_id, $a_copy_id = null, $a_omit_tree = false)
    {
        if ($this->beforeCloneObject()) {
            $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
            if ($new_obj) {
                $this->doCloneObject($new_obj, $a_target_id, $a_copy_id);
                return $new_obj;
            }
        }
    }
    
    protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
    }
    
    protected function beforeCloneObject()
    {
        return true;
    }

    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        return parent::cloneDependencies($a_target_id, $a_copy_id);
    }
    
    final public static function _getIcon($a_obj_id = "", $a_size = "big", $a_type = "", $a_offline = false)
    {
        return parent::_getIcon($a_obj_id, $a_size, $a_type, $a_offline);
    }
}
