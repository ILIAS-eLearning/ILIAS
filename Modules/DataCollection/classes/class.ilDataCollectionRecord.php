<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDataCollectionRecord
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @ingroup ModulesDataCollection
*/

include_once './Modules/DataCollection/classes/class.ilDataCollectionRecordField.php';

class ilDataCollectionRecord
{
    private $recordfields;
    private $id;
    private $table_id;
    private $table;

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


	/**
	* Set Field Value
	*
	* @param string $a_value
	* @param int $a_id
	*/
	function setRecordFieldValue($field_id, $value)
	{
        $this->loadRecordFields();
        if(ilDataCollectionStandardField::_isStandardField($field_id))
            $this->setStandardField($field_id, $value);
        else
		    $this->recordfields[$field_id]->setValue($value);
	}
	
	/**
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
	 * getRecordFieldValuesAsObject
	 * @param int $a_val
	 * @return boolean
	 */
	public function getRecordFieldValuesAsObject()
	{
		$this->loadRecordFields();
		
		return $this->recordfields;
		
	}
	
	/**
	* Get Field Value
	*
	* @param int $a_id
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

    function getRecordFieldHTML($field_id){
        $this->loadRecordFields();
        if(ilDataCollectionStandardField::_isStandardField($field_id))
            return $this->getStandardField($field_id);
        else
            return $this->recordfields[$field_id]->getHTML();
    }

    function getRecordFieldFormInput($field_id){
        $this->loadRecordFields();
        if(ilDataCollectionStandardField::_isStandardField($field_id))
            return $this->getStandardField($field_id);
        else
            return $this->recordfields[$field_id]->getFormInput();
    }


    // TODO: Bad style, fix with switch statement
    private function setStandardField($field_id, $value){
        $this->$field_id = $value;
    }

    // TODO: Bad style, fix with switch statement
    private function getStandardField($field_id){
        return $this->$field_id;
    }

    private function loadRecordFields(){
        if($this->recordfields == NULL){
            $this->loadTable();
            $recordfields = array();
            foreach($this->table->getRecordFields() as $field){
                $recordfields[$field->getId()] = new ilDataCollectionRecordField($this, $field);
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
            $this->table = new ilDataCollectionTable($this->getTableId());
        }
    }

	/**
	* Read record
	*/
	function doRead()
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
	}

	/**
	* Create new record
	*
	* @param array $all_fields
	*
	*/
	function DoCreate()
	{
		global $ilDB;

		// Record erzeugen
		$id = $ilDB->nextId("il_dcl_record");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_record (
							id,
							table_id,
							create_date,
							Last_update,
							owner
						) VALUES (".
							$ilDB->quote($this->getId(), "integer").",".
							$ilDB->quote($this->getTableId(), "integer").",".
							$ilDB->quote($this->getCreateDate(), "timestamp").",".
							$ilDB->quote($this->getLastUpdate(), "timestamp").",".
							$ilDB->quote($this->getOwner(), "integer")."
						)";
		$ilDB->manipulate($query);
    }

    public function deleteField($field_id){
        $this->loadRecordFields();
        $this->recordfields[$field_id]->delete();
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
             $recordfield->delete();
        }
        
        $query = "DELETE FROM il_dcl_record WHERE id = ".$this->getId();
        $ilDB->manipulate($query);
    }
    
    /*
     * hasEditPermission
     */
	function hasEditPermission()
	{
		global $ilAccess;
		
		$table = new ilDataCollectionTable($this->getTableId());
		$dcObj = $table->getCollectionObject();
		
		$perm = false;
		
		$references = $dcObj->_getAllReferences($dcObj->getId());

		//if($ilAccess->checkAccess("add_entry", "", array_shift($references)))
		//{
			global $rbacreview, $ilUser;

			// always allow sysad to access records.
			if($ilUser->getId() == 6){
				$perm = true;
			}

			//TODO: Check for local admin

			if($dcObj->isRecordsEditable() && $this->getOwner() == $ilUser->getId() && $dcObj->getEditByOwner())
				$perm = true;
		//}

		return $perm;
	}
	
	
	/*
	 * doUpdate
	 */
    function doUpdate()
    {
        global $ilDB;
        
        $ilDB->update("il_dcl_record", array(
            "table_id" => array("integer", $this->getTableId()),
            "create_date" => array("date", $this->getCreateDate()),
            "last_update" => array("date", $this->getLastUpdate()),
            "owner" => array("text", $this->getOwner())
        ), array(
            "id" => array("integer", $this->id)
        ));

        foreach($this->recordfields as $recordfield)
        {
            $recordfield->doUpdate();
        }
    }
}
?>