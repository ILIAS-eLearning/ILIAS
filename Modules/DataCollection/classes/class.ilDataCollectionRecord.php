<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordField.php');
require_once('./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php');
require_once './Services/Exceptions/classes/class.ilException.php';

/**
* Class ilDataCollectionRecord
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @author Oskar Truffer <ot@studer-raimann.ch>
* @version $Id:
*
* @ingroup ModulesDataCollection
*/
class ilDataCollectionRecord
{
	/**
	 * @var ilDataCollectionRecordField[]
	 */
	private $recordfields;
	private $id;
	private $table_id;

    /**
     * @var ilDataCollectionTable
     */
    private $table;
	private $last_edit_by;

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
	 * doUpdate
	 */
	public function doUpdate()
	{
		global $ilDB;
		
		$ilDB->update("il_dcl_record", array(
			"table_id" => array("integer", $this->getTableId()),
			"create_date" => array("date", $this->getCreateDate()),
			"last_update" => array("date", $this->getLastUpdate()),
			"owner" => array("text", $this->getOwner()),
			"last_edit_by" => array("text", $this->getLastEditBy())
		), array(
			"id" => array("integer", $this->id)
		));

		foreach($this->getRecordFields() as $recordfield)
		{
			$recordfield->doUpdate();
		}

		include_once "./Modules/DataCollection/classes/class.ilObjDataCollection.php";
		ilObjDataCollection::sendNotification("update_record", $this->getTableId(), $this->id);
	}
	
	/**
	 * Read record
	 */
	public function doRead()
	{
		global $ilDB;
		//build query
		$query = "Select * From il_dcl_record rc WHERE rc.id = ".$ilDB->quote($this->getId(),"integer")." ORDER BY rc.id";


		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTableId($rec["table_id"]);
		$this->setCreateDate($rec["create_date"]);
		$this->setLastUpdate($rec["last_update"]);
		$this->setOwner($rec["owner"]);
		$this->setLastEditBy($rec["last_edit_by"]);
	}

	/**
	 * Create new record
	 *
	 * @param array $all_fields
	 *
	 */
	public function doCreate()
	{
		global $ilDB;

		if(!ilDataCollectionTable::_tableExists($this->getTableId()))
			throw new ilException("The field does not have a related table!");

		// Record erzeugen
		$id = $ilDB->nextId("il_dcl_record");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_record (
			id,
			table_id,
			create_date,
			Last_update,
			owner,
			last_edit_by
			) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote($this->getTableId(), "integer").",".
			$ilDB->quote($this->getCreateDate(), "timestamp").",".
			$ilDB->quote($this->getLastUpdate(), "timestamp").",".
			$ilDB->quote($this->getOwner(), "integer").",".
			$ilDB->quote($this->getLastEditBy(), "integer")."
			)";
		$ilDB->manipulate($query);

		include_once "./Modules/DataCollection/classes/class.ilObjDataCollection.php";
		ilObjDataCollection::sendNotification("new_record", $this->getTableId(), $id);
	}
	
	/*
	 * deleteField
	 */
	public function deleteField($field_id)
	{
		$this->loadRecordFields();
		$this->recordfields[$field_id]->delete();
        if(count($this->recordfields) == 1)
            $this->doDelete();
	}
	
	/**
	 * Set field id
	 *
	 * @param int $a_id
	 */
	public function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get field id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set Table ID
	 *
	 * @param int $a_id
	 */
	public function setTableId($a_id)
	{
		$this->table_id = $a_id;
	}

	/**
	 * Get Table ID
	 *
	 * @return int
	 */
	public function getTableId()
	{
		return $this->table_id;
	}

	/**
	 * Set Creation Date
	 *
	 * @param ilDateTime $a_datetime
	 */
	public function setCreateDate($a_datetime)
	{
		$this->create_date = $a_datetime;
	}

	/**
	 * Get Creation Date
	 *
	 * @return ilDateTime
	 */
	public function getCreateDate()
	{
		return $this->create_date;
	}

	/**
	 * Set Last Update Date
	 *
	 * @param ilDateTime $a_datetime
	 */
	public function setLastUpdate($a_datetime)
	{
		$this->last_update = $a_datetime;
	}

	/**
	 * Get Last Update Date
	 *
	 * @return ilDateTime
	 */
	public function getLastUpdate()
	{
		return $this->last_update;
	}

	/**
	 * Set Owner
	 *
	 * @param int $a_id
	 */
	public function setOwner($a_id)
	{
		$this->owner = $a_id;
	}

	/**
	 * Get Owner
	 *
	 * @return int
	 */
	public function getOwner()
	{
		return $this->owner;
	}
	
	/*
	 * getLastEditBy
	 */
	public function getLastEditBy()
	{
		return $this->last_edit_by;
	}
	
	/*
	 * setLastEditBy
	 */
	public function setLastEditBy($last_edit_by)
	{
		$this->last_edit_by = $last_edit_by;
	}


	/**
	 * Set Field Value
	 *
	 * @param string $a_value
	 * @param int $a_id
	 */
	public function setRecordFieldValue($field_id, $value)
	{
	   	$this->loadRecordFields();
	   	
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			$this->setStandardField($field_id, $value);
		}
		else
		{
			$this->loadTable();
			$this->recordfields[$field_id]->setValue($value);
		}
	}
	
	/**
	 * @depricated
	 * getRecordFieldValues
	 * @return array
	 */
	public function getRecordFieldValues()
	{
		$this->loadRecordFields();
		
		foreach($this->recordfields as $id => $record_field)
		{
			$return[$id] = $record_field->getValue();
		}
		
		return (array) $return;
	}
	
	/**
	 * Get Field Value
	 *
	 * @param int $field_id
	 * @return array
	 */
	public function getRecordFieldValue($field_id)
	{
		$this->loadRecordFields();
		
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			return $this->getStandardField($field_id);
		}
		else
		{
			return $this->recordfields[$field_id]->getValue();
		}
	}
	
	
	/**
	 * Get Field Export Value
	 *
	 * @param int $field_id
	 * @return array
	 */
	public function getRecordFieldExportValue($field_id)
	{
		$this->loadRecordFields();
		
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			return $this->getStandardFieldHTML($field_id);
		}
		else
		{
			return $this->recordfields[$field_id]->getExportValue();
		}
	}

	
		
	/*
	 * getRecordFieldHTML
	 *
	 * @param int $field_id
	 * @param array $options
	 * @return array
	 */
	public function getRecordFieldHTML($field_id,array $options = array())
	{
        global $lng;
		$this->loadRecordFields();
		
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			return $this->getStandardFieldHTML($field_id);
		}
		else
		{
            if(!$this->recordfields[$field_id]){
                ilUtil::sendInfo($lng->txt("dcl_inconsistent"), true);
                return "-";
            }

			return $this->recordfields[$field_id]->getHTML($options);
		}
	}



	/*
	 * getRecordFieldFormInput
	 *
	 * @param int $field_id
	 * @return array
	 */
	public function getRecordFieldFormInput($field_id)
	{
		$this->loadRecordFields();
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			return $this->getStandardField($field_id);
		}
		else
		{
			return $this->recordfields[$field_id]->getFormInput();
        }
	}


	/*
	 * setStandardField
	 *
	 * @param int $field_id
	 * @param mixed $value
	 */
	 
	private function setStandardField($field_id, $value)
	{
		switch($field_id)
		{
			case "last_edit_by":
				$this->setLastEditBy($value);
				return;
		}
		$this->$field_id = $value;
	}
	
	
	/*
	 * getStandardField
	 *
	 * @param int $field_id
	 * @return mixed
	 */
	private function getStandardField($field_id)
	{
		switch($field_id)
		{
			case "last_edit_by":
				return $this->getLastEditBy();
		}
		
		return $this->$field_id;
	}
	
	/*
	 * getStandardFieldHTML
	 *
	 * @param int $field_id
	 * @return mixed
	 */
	private function getStandardFieldHTML($field_id)
	{
		switch($field_id)
		{
			case 'owner':
				global $ilCtrl;
				$owner = new ilObjUser($this->getOwner());
				//$ilCtrl->setParameterByClass("ilObjUserGUI", "obj_id", $owner->getId());
				//$link = $ilCtrl->getLinkTargetByClass("ilObjUserGUI", "view");
				//return "<a class='dcl_usr_link' href='".$link."'>".$owner->getFullname()."</a>";
				return $owner->getFullname();
				
			case 'last_edit_by':
				$last_edit_by = new ilObjUser($this->getLastEditBy());
				return $last_edit_by->getFullname();
		}
		
		return $this->$field_id;
	}
	
	
	/*
	 * loadRecordFields
	 */
	private function loadRecordFields()
	{
		if($this->recordfields == NULL)
		{
			$this->loadTable();
			$recordfields = array();
			foreach($this->table->getRecordFields() as $field)
			{
				if($recordfields[$field->getId()] == NULL)
				{
                    $recordfields[$field->getId()] = ilDataCollectionCache::getRecordFieldCache($this, $field);
				}
			}
			
			$this->recordfields = $recordfields;
		}
	}
	
	/*
	 * loadTable
	 */
	private function loadTable()
	{
		include_once("class.ilDataCollectionTable.php");
		
		if($this->table == NULL)
		{
			$this->table = ilDataCollectionCache::getTableCache($this->getTableId());
		}
	}

	/*
	 * getRecordField
	 */
	public function getRecordField($field_id)
	{
		$this->loadRecordFields();
		
		return $this->recordfields[$field_id];
	}

	/*
	 * doDelete
	 */
	public function doDelete()
	{
		global $ilDB;
		
		$this->loadRecordFields();
		
		foreach($this->recordfields as $recordfield)
		{
			if($recordfield->getField()->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_FILE)
				$this->deleteFile($recordfield->getValue());

            if($recordfield->getField()->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_MOB)
                $this->deleteMob($recordfield->getValue());


            $recordfield->delete();
		}


		$query = "DELETE FROM il_dcl_record WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);

		include_once "./Modules/DataCollection/classes/class.ilObjDataCollection.php";
		ilObjDataCollection::sendNotification("delete_record", $this->getTableId(), $this->getId());
	}
	
	/*
	 * deleteFile
	 */
	public function deleteFile($obj_id)
	{
        if(ilObject2::_lookupObjId($obj_id)){
		    $file = new ilObjFile($obj_id, false);
		    $file->delete();
        }
	}


    /*
      * deleteMob
      */
    public function deleteMob($obj_id)
    {
        if(ilObject2::_lookupObjId($obj_id)){
            $mob = new ilObjMediaObject($obj_id);
            $mob->delete();
        }
    }
	
	/*
	 * passThroughFilter
	 */
	public function passThroughFilter(array $filter)
	{
		$pass = true;
		$this->loadTable();
		foreach($this->table->getFields() as $field)
		{
			if(!ilDataCollectionDatatype::passThroughFilter($this, $field, $filter["filter_".$field->getId()]))
			{
				$pass = false;
			}
		}
		
		return $pass;
	}
	
	/*
	 * hasPermissionToEdit
	 */
	public function hasPermissionToEdit($ref)
	{
		return $this->getTable()->hasPermissionToEditRecord($ref, $this);
	}
	
	/*
	 * hasPermissionToDelete
	 */
	public function hasPermissionToDelete($ref)
	{
		return $this->getTable()->hasPermissionToDeleteRecord($ref, $this);
	}
	
	/*
	 * getRecordFields
	 */
	public function getRecordFields()
	{
		$this->loadRecordFields();
		
		return $this->recordfields;
	}

	/**
	 * @return ilDataCollectionTable
	 */
	public function getTable()
	{
		$this->loadTable();
		
		return $this->table;
	}
}
?>