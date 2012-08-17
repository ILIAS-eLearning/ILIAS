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

	public function getLastEditBy(){
		return $this->last_edit_by;
	}

	public function setLastEditBy($last_edit_by){
		global $ilLog;
		$this->last_edit_by = $last_edit_by;
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
            return $this->getStandardFieldHTML($field_id);
        else
            return $this->recordfields[$field_id]->getHTML();
    }
    
    /*
     * getRecordFieldFormInput
     */
    function getRecordFieldFormInput($field_id)
    {
        $this->loadRecordFields();
        if(ilDataCollectionStandardField::_isStandardField($field_id))
            return $this->getStandardField($field_id);
        else
            return $this->recordfields[$field_id]->getFormInput();
    }


    // TODO: Bad style, fix with switch statement
    private function setStandardField($field_id, $value){
		switch($field_id){
			case $field_id = "last_edit_by":
				$this->setLastEditBy($value);
				return;
		}
        $this->$field_id = $value;
    }

    // TODO: Bad style, fix with switch statement
    private function getStandardField($field_id){
		switch($field_id){
			case $field_id = "last_edit_by":
				return $this->getLastEditBy();
		}
        return $this->$field_id;
    }

	// TODO: Bad style, fix with switch statement
	private function getStandardFieldHTML($field_id){
		switch($field_id){
			case 'owner':
				$owner = new ilObjUser($this->getOwner());
				return $owner->getFullname();
			case 'last_edit_by':
				$last_edit_by = new ilObjUser($this->getLastEditBy());
				return $last_edit_by->getFullname();
		}
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
		$this->setLastEditBy($rec["last_edit_by"]);
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
			if($recordfield->getField()->getDatatype() == ilDataCollectionDatatype::INPUTFORMAT_FILE)
				$this->deleteFile($recordfield->getValue());
             $recordfield->delete();
        }
        
        $query = "DELETE FROM il_dcl_record WHERE id = ".$this->getId();
        $ilDB->manipulate($query);
    }

	public function deleteFile($obj_id){
		$file = new ilObjDataCollectionFile($obj_id, false);
		$file->delete();
	}
    
    /*
     * hasEditPermission
     */
	function hasEditPermission($ref_id)
	{
		global $ilAccess;
		
		$table = new ilDataCollectionTable($this->getTableId());
		$dcObj = $table->getCollectionObject();
		
		$perm = false;
		
		$ref = $ref_id;

		if($ilAccess->checkAccess("add_entry", "", $ref))
		{
			global $ilUser;
			// checks if at this time records can be edited and if setting "only editable by owner" is set check if the owner is the current user.
			if(!$this->getTable()->isBlocked() && $dcObj->isRecordsEditable() && ($this->getOwner() == $ilUser->getId() || !$dcObj->getEditByOwner()))
				$perm = true;
		}

		// admin of this object
		if($ilAccess->checkAccess("write", "",  $ref))
			$perm = true;

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
            "owner" => array("text", $this->getOwner()),
            "last_edit_by" => array("text", $this->getLastEditBy())
        ), array(
            "id" => array("integer", $this->id)
        ));

        foreach($this->recordfields as $recordfield)
        {
            $recordfield->doUpdate();
        }
    }

	public function getTable(){
		$this->loadTable();
		return $this->table;
	}
}
?>