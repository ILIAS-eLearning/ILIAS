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
	function __construct($a_id = 0, $a_reference = true)
	{
		$this->initType();
		parent::ilObject($a_id, $a_reference);
	}

	abstract protected function initType();
	
	final function withReferences() { return parent::withReferences(); }

	/**
	* Read data from db
	*/
	final function read($a_force_db = false)
	{
		parent::read($a_force_db);
		$this->doRead();
	}
	protected function doRead()
	{
		
	}
	
	final function getId() { return parent::getId(); }
	final function setId($a_id) { return parent::setId($a_id); }
	final function setRefId($a_id) { return parent::setRefId($a_id); }
	final function getRefId() { return parent::getRefId(); }
	final function getType() { return parent::getType(); }
	final function setType($a_type) { return parent::setType($a_type); }
	final function getPresentationTitle() { return parent::getPresentationTitle(); }
	final function getTitle() { return parent::getTitle(); }
	final function getUntranslatedTitle() { return parent::getUntranslatedTitle(); }
	final function setTitle($a_title) { return parent::setTitle($a_title); }
	final function getDescription() { return parent::getDescription(); }
	final function setDescription($a_desc) { return parent::setDescription($a_desc); }
	final function getLongDescription() { return parent::getLongDescription(); }
	final function getImportId() { return parent::getImportId(); }
	final function setImportId($a_import_id) { return parent::setImportId($a_import_id); }
	final static function _lookupObjIdByImportId($a_import_id) { return parent::_lookupObjIdByImportId($a_import_id); }
	final function getOwner() { return parent::getOwner(); }
	final function getOwnerName() { return parent::getOwnerName(); }
	final function _lookupOwnerName($a_owner_id) { return parent::_lookupOwnerName($a_owner_id); }
	final function setOwner($a_owner) { return parent::setOwner($a_owner); }
	final function getCreateDate() { return parent::getCreateDate(); }
	final function getLastUpdateDate() { return parent::getLastUpdateDate(); }
	final function setObjDataRecord($a_record) { return parent::setObjDataRecord($a_record); }

	final function create($a_clone_mode = false)
	{
		if($this->beforeCreate())
		{
			$id = parent::create();
			if($id)
			{
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
	
	final function update()
	{
		if($this->beforeUpdate())
		{
			if (!parent::update())
			{
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

	final function MDUpdateListener($a_element) 
	{
		if($this->beforeMDUpdateListener($a_element))
		{
			if(parent::MDUpdateListener($a_element))
			{
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

	final function createMetaData()
	{
		if($this->beforeCreateMetaData())
		{
			if(parent::createMetaData())
			{
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

	final function updateMetaData()
	{
		if($this->beforeUpdateMetaData())
		{
			if(parent::updateMetaData())
			{
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
	
	final function deleteMetaData() { return parent::deleteMetaData(); }
	final function updateOwner() { return parent::updateOwner(); }
	final function _getIdForImportId($a_import_id) { return parent::_getIdForImportId($a_import_id); }
	static final function _getAllReferences($a_id) { return parent::_getAllReferences($a_id); }
	final static function _lookupTitle($a_id) { return parent::_lookupTitle($a_id); }
	final function _lookupOwner($a_id) { return parent::_lookupOwner($a_id); }
	final static function _getIdsForTitle($title, $type = '', $partialmatch = false) { return parent::_getIdsForTitle($title, $type, $partialmatch); }
	final static function _lookupDescription($a_id) { return parent::_lookupDescription($a_id); }
	final function _lookupLastUpdate($a_id, $a_as_string = false) { return parent::_lookupLastUpdate($a_id, $a_as_string); }
	final function _getLastUpdateOfObjects($a_objs) { return parent::_getLastUpdateOfObjects($a_objs); }
	final static function _lookupObjId($a_id) { return parent::_lookupObjId($a_id); }
	final function _setDeletedDate($a_ref_id) { return parent::_setDeletedDate($a_ref_id); }
	final function _resetDeletedDate($a_ref_id) { return parent::_resetDeletedDate($a_ref_id); }
	final function _lookupDeletedDate($a_ref_id) { return parent::_lookupDeletedDate($a_ref_id); }
	final function _writeTitle($a_obj_id, $a_title) { return parent::_writeTitle($a_obj_id, $a_title); }
	final function _writeDescription($a_obj_id, $a_desc) { return parent::_writeDescription($a_obj_id, $a_desc); }
	final function _writeImportId($a_obj_id, $a_import_id) { return parent::_writeImportId($a_obj_id, $a_import_id); }
	final static function _lookupType($a_id,$a_reference = false) { return parent::_lookupType($a_id,$a_reference); }
	final function _isInTrash($a_ref_id) { return parent::_isInTrash($a_ref_id); }
	final function _hasUntrashedReference($a_obj_id) { return parent::_hasUntrashedReference($a_obj_id); }
	final static function _lookupObjectId($a_ref_id) { return parent::_lookupObjectId($a_ref_id); }
	final function _getObjectsDataForType($a_type, $a_omit_trash = false) { return parent::_getObjectsDataForType($a_type, $a_omit_trash); }
	final function putInTree($a_parent_ref) { return parent::putInTree($a_parent_ref); }
	final function setPermissions($a_parent_ref) { return parent::setPermissions($a_parent_ref); }
	final function createReference() { return parent::createReference(); }
	final function countReferences() { return parent::countReferences(); }

	final function delete()
	{
		if($this->beforeDelete())
		{
			if(parent::delete())
			{
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

	function initDefaultRoles() { return array(); }
	
	final public static function _exists($a_id, $a_reference = false) { return parent::_exists($a_id, $a_reference); }
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0) { return parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params); }
	final function setRegisterMode($a_bool) { return parent::setRegisterMode($a_bool); }
	final function isUserRegistered($a_user_id = 0) { return parent::isUserRegistered($a_user_id); }
	final function requireRegistration() { return parent::requireRegistration(); }
	//final function getXMLZip() { return parent::getXMLZip(); }
	//final function getHTMLDirectory() { return parent::getHTMLDirectory(); }
	final static function _getObjectsByType($a_obj_type = "", $a_owner = "") { return parent::_getObjectsByType($a_obj_type, $a_owner); }
	
	final static function _prepareCloneSelection($a_ref_ids,$new_type) { return parent::_prepareCloneSelection($a_ref_ids,$new_type); }
	final function appendCopyInfo($a_target_id,$a_copy_id) { return parent::appendCopyInfo($a_target_id,$a_copy_id); }
	final function cloneMetaData($target_obj)  { return parent::cloneMetaData($target_obj); }
	
	final function cloneObject($a_target_id, $a_copy_id = null, $a_omit_tree = false)
	{
		if($this->beforeCloneObject())
		{
			$new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
			if($new_obj)
			{
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

	function cloneDependencies($a_target_id,$a_copy_id) { return parent::cloneDependencies($a_target_id,$a_copy_id); }
	
	final public static function _getIcon($a_obj_id = "", $a_size = "big", $a_type = "", $a_offline = false)  { return parent::_getIcon($a_obj_id, $a_size, $a_type, $a_offline); }

}
?>
