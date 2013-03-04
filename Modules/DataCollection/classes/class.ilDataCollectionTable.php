<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/DataCollection/classes/class.ilDataCollectionStandardField.php';
include_once './Modules/DataCollection/classes/class.ilDataCollectionRecord.php';

/**
 * Class ilDataCollectionField
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionTable
{
	protected 	$id; 		// [int]
	protected 	$objId; 	// [int]
	protected 	$obj;
	protected 	$title; 	// [string]
	private 	$fields; 	// [array][ilDataCollectionField]
	private 	$stdFields;
	private 	$records;

	/**
	 * @var bool
	 */
	private $is_visible;

	/**
	 * @var bool
	 */
	private $add_perm;

	/**
	 * @var bool
	 */
	private $edit_perm;
	/**
	 * @var bool
	 */
	private $delete_perm;
	/**
	 * @var bool
	 */
	private $edit_by_owner;

	/**
	 * @var bool
	 */
	private $limited;
	/**
	 * @var string
	 */
	private $limit_start;
	/**
	 * @var string
	 */
	private $limit_end;

    /**
     * @var bool export_enabled
     */
    protected $export_enabled;

    /**
	 * Constructor
	 * @access public
	 * @param  integer fiel_id
	 *
	 */
	public function __construct($a_id = 0)
	{
		if($a_id != 0) 
		{
			$this->id = $a_id;
			$this->doRead();
		}	
	}

	
	/**
	 * Read table
	 */
	public function doRead()
	{
		global $ilDB;

		$query = "SELECT * FROM il_dcl_table WHERE id = ".$ilDB->quote($this->getId(),"integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setObjId($rec["obj_id"]);
		$this->setTitle($rec["title"]);
		$this->setAddPerm($rec["add_perm"]);
		$this->setEditPerm($rec["edit_perm"]);
		$this->setDeletePerm($rec["delete_perm"]);
		$this->setEditByOwner($rec["edit_by_owner"]);
		$this->setExportEnabled($rec["export_enabled"]);
		$this->setLimited($rec["limited"]);
		$this->setLimitStart($rec["limit_start"]);
		$this->setLimitEnd($rec["limit_end"]);
		$this->setIsVisible($rec["is_visible"]);
	}
	
	/**
	 * doDelete
	 * Attention this does not delete the maintable of it's the maintabla of the collection. 
	 * unlink the the maintable in the collections object to make this work.
	 * @param boolean $delete_main_table true to delete table anyway
	 */
	public function doDelete($delete_main_table = false)
	{
		global $ilDB;
		
		foreach($this->getRecords() as $record)
		{
			$record->doDelete();
		}
			
		foreach($this->getRecordFields() as $field)
		{
			$field->doDelete();
		}
			
		if($this->getCollectionObject()->getMainTableId() != $this->getId() || $delete_main_table == true)
		{
			$query = "DELETE FROM il_dcl_table WHERE id = ".$ilDB->quote($this->getId(), "integer");
			$ilDB->manipulate($query);	
		}
	}

	/**
	 * Create new table
	 */
	public function doCreate()
	{
		global $ilDB;

		$id = $ilDB->nextId("il_dcl_table");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_table (".
			"id".
			", obj_id".
			", title".
			", add_perm".
			", edit_perm".
			", delete_perm".
			", edit_by_owner".
			", limited".
			", limit_start".
			", limit_end".
			", is_visible".
			", export_enabled".
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getObjId(), "integer")
			.",".$ilDB->quote($this->getTitle(), "text")
			.",".$ilDB->quote($this->getAddPerm()?1:0, "integer")
			.",".$ilDB->quote($this->getEditPerm()?1:0, "integer")
			.",".$ilDB->quote($this->getDeletePerm()?1:0, "integer")
			.",".$ilDB->quote($this->getEditByOwner()?1:0, "integer")
			.",".$ilDB->quote($this->getLimited()?1:0, "integer")
			.",".$ilDB->quote($this->getLimitStart(), "timestamp")
			.",".$ilDB->quote($this->getLimitEnd(), "timestamp")
			.",".$ilDB->quote($this->getIsVisible()?1:0, "integer")
			.",".$ilDB->quote($this->getExportEnabled()?1:0, "integer")
			.")";
		$ilDB->manipulate($query);

		//add view definition
		$view_id = $ilDB->nextId("il_dcl_view");
		$query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (".$ilDB->quote($view_id, "integer").", ".$ilDB->quote($this->id, "integer").", ".$ilDB->quote(ilDataCollectionField::VIEW_VIEW, "integer").", ".$ilDB->quote(1, "integer").")";
		$ilDB->manipulate($query);

		//add edit definition
		$view_id = $ilDB->nextId("il_dcl_view");
		$query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (".$ilDB->quote($view_id, "integer").", ".$ilDB->quote($this->id, "integer").", ".$ilDB->quote(ilDataCollectionField::EDIT_VIEW, "integer").", ".$ilDB->quote(1, "integer").")";
		$ilDB->manipulate($query);

		//add filter definition
		$view_id = $ilDB->nextId("il_dcl_view");
		$query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (".$ilDB->quote($view_id, "integer").", ".$ilDB->quote($this->id, "integer").", ".$ilDB->quote(ilDataCollectionField::FILTER_VIEW, "integer").", ".$ilDB->quote(1, "integer").")";
		$ilDB->manipulate($query);

		$this->buildOrderFields();
	}
	
	/*
	 * doUpdate
	 */
	public function doUpdate()
	{
		global $ilDB;

		$ilDB->update("il_dcl_table", array(
			"obj_id" => array("integer", $this->getObjId()),
			"title" => array("text", $this->getTitle()),
			"add_perm" => array("integer",$this->getAddPerm()),
			"edit_perm" => array("integer",$this->getEditPerm()),
			"delete_perm" => array("integer",$this->getDeletePerm()),
			"edit_by_owner" => array("integer",$this->getEditByOwner()),
			"limited" => array("integer",$this->getLimited()),
			"limit_start" => array("timestamp",$this->getLimitStart()),
			"limit_end" => array("timestamp",$this->getLimitEnd()),
			"is_visible" => array("integer",$this->getIsVisible()?1:0),
			"export_enabled" => array("integer",$this->getExportEnabled()?1:0)
		), array(
			"id" => array("integer", $this->getId())
		));
	}
	
	/**
	 * Set table id
	 *
	 * @param int $a_id
	 */
	public function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get table id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set object id
	 *
	 * @param int $obj_id
	 */
	public function setObjId($a_id)
	{
		$this->objId = $a_id;
	}

	/**
	 * Get object id
	 *
	 * @return int
	 */
	public function getObjId()
	{
		return $this->objId;
	}

	/**
	 * Set title
	 *
	 * @param string $a_title
	 */
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * getCollectionObject
	 * @return ilObjDataCollection
	 */
	public function getCollectionObject()
	{
		$this->loadObj();
		
		return $this->obj;
	}
	
	/*
	 * loadObj
	 */
	private function loadObj()
	{
		if($this->obj == NULL)
		{
			$this->obj = new ilObjDataCollection($this->objId, false);
		}
	}

	/**
	 * @return ilDataCollectionRecord[]
	 */
	public function getRecords()
	{
		$this->loadRecords();
		
		return $this->records;
	}

	/**
	 * getRecordsByFilter
	 * @param $filter 
	 * filter is of the form array("filter_{field_id}" => filter); 
	 * For dates and integers this filter must be of the form array("from" => from, "to" => to). 
	 * In case of dates from and to have to be ilDateTime objects 
	 * in case of integers they have to be integers as well.
     * @return ilDataCollectionRecord[]
	 */
	public function getRecordsByFilter($filter)
	{
		$this->loadRecords();
		$filtered = array();
		
		foreach($this->getRecords() as $record)
		{
			if($record->passThroughFilter($filter?$filter:array()))
			{
				array_push($filtered, $record);
			}
		}
		
		return $filtered;
	}

	/*
	 * loadRecords
	 */
	private function loadRecords()
	{
		if($this->records == NULL)
		{
			global $ilDB;
			
			$records = array();			
			$query = "SELECT id FROM il_dcl_record WHERE table_id = ".$ilDB->quote($this->id, "integer");
			$set = $ilDB->query($query);

			while($rec = $ilDB->fetchAssoc($set))
			{
				$records[$rec['id']] = ilDataCollectionCache::getRecordCache($rec['id']);
			}

			$this->records = $records;
		}
	}
	
	//TODO: replace this method with DataCollection->getTables()
	/**
	 * get all tables of a Data Collection Object
	 *
	 * @param int $a_id obj_id
	 *
	 */
	public function getAll($a_id)
	{
		global $ilDB;

		//build query
		$query = "SELECT	 *
							FROM il_dcl_table
							WHERE obj_id = ".$ilDB->quote($a_id,"integer");
		$set = $ilDB->query($query);

		$all = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$all[$rec['id']] = $rec;
		}

		return $all;
	}
	
	
	/*
	 * deleteField
	 */
	public function deleteField($field_id)
	{
		$field = ilDataCollectionCache::getFieldCache($field_id);
		$records = $this->getRecords();

		foreach($records as $record)
		{
			$record->deleteField($field_id);
		}

		$field->doDelete();
	}
	
	
	/*
	 * getField
	 */
	public function getField($field_id)
	{
		$fields = $this->getFields();
		$field = NULL;
		foreach($fields as $field_1)
		{
			if($field_1->getId() == $field_id)
			{
				$field = $field_1;
			}
		}
			
		return $field;
	}
	
	/*
	 * getFieldIds
	 */
	public function getFieldIds()
	{
		return array_keys($this->getFields());
	}
	
	/*
	 * loadFields
	 */
	private function loadFields()
	{
		if($this->fields == NULL)
		{
			global $ilDB;
			
			$query = "SELECT field.id, field.table_id, field.title, field.description, field.datatype_id, field.required, field.is_unique, field.is_locked FROM il_dcl_field field INNER JOIN il_dcl_view view ON view.table_id = field.table_id INNER JOIN il_dcl_viewdefinition def ON def.view_id = view.id WHERE field.table_id =".$ilDB->quote($this->getId(), "integer")." ORDER BY def.field_order DESC";
			$fields = array();
			$set = $ilDB->query($query);
			
			while($rec = $ilDB->fetchAssoc($set))
			{
                $field = ilDataCollectionCache::buildFieldFromRecord($rec);
//				$field = new ilDataCollectionField();
//				$field->buildFromDBRecord($rec);
				$fields[$field->getId()] = $field;
			}
            $this->sortByOrder($fields);
			$this->fields = $fields;
		}
	}

	/**
	 * getNewOrder
	 * @return int returns the place where a new field should be placed.
	 */
	public function getNewOrder()
	{
		$fields = $this->getFields();
		$place = 0;
		foreach($fields as $field)
		{
			if($field->isVisible())
			{
				$place = $field->getOrder() + 1;
			}
		}
			
		return $place;
	}

	/**
	 * Returns all fields of this table including the standard fields
	 * @return ilDataCollectionField[]
	 */
	public function getFields()
	{
		$this->loadFields();
		if($this->stdFields == NULL)
		{
			$this->stdFields = ilDataCollectionStandardField::_getStandardFields($this->id);
		}
		$fields = array_merge($this->fields, $this->stdFields);
		$this->sortByOrder($fields);

		return $fields;
	}

	/**
	 * Returns all fields of this table which are NOT standard fields.
	 * @return ilDataCollectionField[]
	 */
	public function getRecordFields()
	{
		$this->loadFields();
		
		return $this->fields;
	}

	/**
	 * Returns all fields of this table who have set their visibility to true, including standard fields.
	 * @return ilDataCollectionField[]
	 */
	public function getVisibleFields()
	{
		$fields = $this->getFields();

		$visibleFields = array();
		
		foreach($fields as $field)
		{
			if($field->isVisible())
			{
				$visibleFields[] = $field;
			}
		}

		return $visibleFields;
	}
	
	/*
	 * getEditableFields
	 */
	public function getEditableFields()
	{
		$fields = $this->getRecordFields();
		$editableFields = array();

		foreach($fields as $field)
		{
			if(!$field->getLocked())
			{
				array_push($editableFields, $field);
			}
		}

		return $editableFields;
	}

	 /**
	  * getFilterableFields
	  * Returns all fields of this table who have set their filterable to true, including standard fields.
	  * @return ilDataCollectionField[]
	  */
	public function getFilterableFields()
	{
		$fields = $this->getFields();
		$filterableFields = array();
		
		foreach($fields as $field)
		{
			if($field->isFilterable())
			{
				array_push($filterableFields, $field);
			}
		}
			
		return $filterableFields;
	}

	/*
	 * hasPermissionToFields
	 */
	public function hasPermissionToFields($ref_id)
	{
		return ilObjDataCollection::_hasWriteAccess($ref_id);
	}
	
	/*
	 * hasPermissionToAddTable
	 */
	public function hasPermissionToAddTable($ref_id)
	{
		return ilObjDataCollection::_hasWriteAccess($ref_id);
	}


	public function hasPermissionToAddRecord($ref)
	{
		return ($this->getAddPerm() && ilObjDataCollection::_hasReadAccess($ref) && $this->checkLimit()) || ilObjDataCollection::_hasWriteAccess($ref);
	}

	/**
	 * @param $ref int the reference id of the current datacollection object
	 * @param $record ilDataCollectionRecord the record which will be edited
	 * @return bool
	 */
	public function hasPermissionToEditRecord($ref, $record)
	{
		return ($this->getEditPerm() && ilObjDataCollection::_hasReadAccess($ref) && $this->checkEditByOwner($record) && $this->checkLimit())  || ilObjDataCollection::_hasWriteAccess($ref);
	}

	/**
	 * @param $ref int the reference id of the current datacollection object
	 * @param $record ilDataCollectionRecord the record which will be deleted
	 * @return bool
	 */
	public function hasPermissionToDeleteRecord($ref, $record)
	{
		return ($this->getDeletePerm() && ilObjDataCollection::_hasReadAccess($ref) && $this->checkEditByOwner($record) && $this->checkLimit())  || ilObjDataCollection::_hasWriteAccess($ref);
	}
	
	/*
	 * checkEditByOwner
	 */
	private function checkEditByOwner($record)
	{
		global $ilUser;
		
		if($this->getEditByOwner() && $ilUser->getId() != $record->getOwner())
		{
			return false;
		}
			
		return true;
	}
	
	/*
	 * checkLimit
	 */
	private function checkLimit()
	{
		if($this->getLimited())
		{
			$now = new ilDateTime(time(), IL_CAL_UNIX);
			$from = new ilDateTime($this->getLimitStart(), IL_CAL_DATE);
			$to = new ilDateTime($this->getLimitEnd(), IL_CAL_DATE);
			
			if(!($from <= $now && $now <= $to))
			{
				return false;
			}
		}
		return true;
	}
	
	/*
	 * updateFields
	 */
	public function updateFields()
	{
		foreach($this->getFields() as $field)
		{
			$field->doUpdate();
		}
	}

	/**
	 * sortFields
	 * @param $fields ilDataCollectionField[]
	 */
	public function sortFields(&$fields)
	{
		$this->sortByOrder($fields);

		//After sorting the array loses it's keys respectivly their keys are set form $field->id to 1,2,3... so we reset the keys.
		$named = array();
		foreach($fields as $field)
		{
			$named[$field->getId()] = $field;
		}
		
		$fields = $named;
	}

    /**
     *
     * @param $array ilDataCollectionField[] the array to sort
     */
    private function sortByOrder(&$array)
	{
		usort($array, array($this, "compareOrder"));
	}

	/**
	 * buildOrderFields
	 * orders the fields.
	 */
	public function buildOrderFields()
	{
		$fields = $this->getFields();

		$this->sortByOrder($fields);

		$count = 10;
		$offset = 10;

		foreach($fields as $field)
		{
			if(!is_null($field->getOrder()))
			{
				$field->setOrder($count);
				$count = $count + $offset;
                $field->doUpdate();
			}
		}
	}

    /**
     * @param $name
     * @return ilDataCollectionField
     */
    public function getFieldByTitle($name){
        $return = null;
        foreach($this->getFields() as $field)
            if($field->getTitle() == $name){
                $return = $field;
                break;
            }
        return $return;
    }

	/**
	 * @param boolean $add_perm
	 */
	public function setAddPerm($add_perm)
	{
		$this->add_perm = $add_perm;
	}

	/**
	 * @return boolean
	 */
	public function getAddPerm()
	{
		return $this->add_perm;
	}

	/**
	 * @param boolean $delete_perm
	 */
	public function setDeletePerm($delete_perm)
	{
		$this->delete_perm = $delete_perm;
	}

	/**
	 * @return boolean
	 */
	public function getDeletePerm()
	{
		return $this->delete_perm;
	}

	/**
	 * @param boolean $edit_by_owner
	 */
	public function setEditByOwner($edit_by_owner)
	{
		$this->edit_by_owner = $edit_by_owner;
	}

	/**
	 * @return boolean
	 */
	public function getEditByOwner()
	{
		return $this->edit_by_owner;
	}

	/**
	 * @param boolean $edit_perm
	 */
	public function setEditPerm($edit_perm)
	{
		$this->edit_perm = $edit_perm;
	}

	/**
	 * @return boolean
	 */
	public function getEditPerm()
	{
		return $this->edit_perm;
	}

	/**
	 * @param boolean $limited
	 */
	public function setLimited($limited)
	{
		$this->limited = $limited;
	}

	/**
	 * @return boolean
	 */
	public function getLimited()
	{
		return $this->limited;
	}

	/**
	 * @param string $limit_end
	 */
	public function setLimitEnd($limit_end)
	{
		$this->limit_end = $limit_end;
	}

	/**
	 * @return string
	 */
	public function getLimitEnd()
	{
		return $this->limit_end;
	}

	/**
	 * @param string $limit_start
	 */
	public function setLimitStart($limit_start)
	{
		$this->limit_start = $limit_start;
	}

	/**
	 * @return string
	 */
	public function getLimitStart()
	{
		return $this->limit_start;
	}

	/**
	 * @param boolean $is_visible
	 */
	public function setIsVisible($is_visible)
	{
		$this->is_visible = $is_visible;
	}

	/**
	 * @return boolean
	 */
	public function getIsVisible()
	{
		return $this->is_visible;
	}
	
	/**
	 * hasCustomFields
	 * @return boolean
	 */
	public function hasCustomFields()
	{
		$this->loadFields();
		
		return (count($this->fields) > 0) ? true : false;
	}

    function compareOrder($a, $b)
    {
        if(is_null($a->getOrder() == NULL) && is_null($b->getOrder() == NULL))
        {
            return 0;
        }
        if(is_null($a->getOrder()))
        {
            return 1;
        }
        if(is_null($b->getOrder()))
        {
            return -1;
        }

        return $a->getOrder() < $b->getOrder() ? -1 : 1;
    }
	
	/*
	 * cloneStructure
	 */
	public function cloneStructure($original_id)
	{
		$original = ilDataCollectionCache::getTableCache($original_id);
		$this->setEditByOwner($original->getEditByOwner());
		$this->setAddPerm($original->getAddPerm());
		$this->setEditPerm($original->getEditPerm());
		$this->setDeletePerm($original->getDeletePerm());
		$this->setLimited($original->getLimited());
		$this->setLimitStart($original->getLimitStart());
		$this->setLimitEnd($original->getLimitEnd());
		$this->setTitle($original->getTitle());
		$this->doCreate();

		//clone fields.
		foreach($original->getRecordFields() as $field)
		{
			$new_field = new ilDataCollectionField();
			$new_field->setTableId($this->getId());
			$new_field->cloneStructure($field->getId());
			
		}
	}
	
	/**
	 * _hasRecords
	 * @return boolean
	 */
	public function _hasRecords()
	{
		return (count($this->getRecords()) > 0) ? true : false;
	}

    /**
     * @param $field ilDataCollectionField add an already created field for eg. ordering.
     */
    public function addField($field){
        $this->fields[$field->getId()] = $field;
    }

	/**
	 * @param $table_id int
	 * @return bool returns true iff there exists a table with id $table_id
	 */
	public static function _tableExists($table_id){
		global $ilDB;
		$query = "SELECT * FROM il_dcl_table WHERE id = ".$table_id;
		$result = $ilDB->query($query);
		return $result->numRows() != 0;
	}


    public function buildTableAsArray(){
        global $ilDB;
        $fields = $this->getVisibleFields();
        $table = array();
        $query = "  SELECT  stloc.value AS val rec_field AS  FROM il_dcl_stloc1_value stloc
                    INNER JOIN il_dcl_record_field rec_field ON rec_field.field_id = 2
                    WHERE stloc.record_field_id = rec_field.id";
    }

    /**
     * @param boolean $export_enabled
     */
    public function setExportEnabled($export_enabled)
    {
        $this->export_enabled = $export_enabled;
    }

    /**
     * @return boolean
     */
    public function getExportEnabled()
    {
        return $this->export_enabled;
    }
}



?>