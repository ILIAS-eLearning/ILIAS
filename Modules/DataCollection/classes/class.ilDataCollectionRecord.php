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


class ilDataCollectionRecord
{
    private $recordfields;
    private $id;
    private $tableId;
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
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get field id
	*
	* @return int
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Set Table ID
	*
	* @param int $a_id
	*/
	function setTableId($a_id)
	{
		$this->tableId = $a_id;
	}

	/**
	* Get Table ID
	*
	* @return int
	*/
	function getTableId()
	{
		return $this->tableId;
	}

	/**
	* Set Creation Date
	*
	* @param ilDateTime $a_datetime
	*/
	function setCreateDate($a_datetime)
	{
		$this->createdate = $a_datetime;
	}

	/**
	* Get Creation Date
	*
	* @return ilDateTime
	*/
	function getCreateDate()
	{
		return $this->createdate;
	}

	/**
	* Set Last Update Date
	*
	* @param ilDateTime $a_datetime
	*/
	function setLastUpdate($a_datetime)
	{
		$this->lastupdate = $a_datetime;
	}

	/**
	* Get Last Update Date
	*
	* @return ilDateTime
	*/
	function getLastUpdate()
	{
		return $this->lastupdate;
	}

	/**
	* Set Owner
	*
	* @param int $a_id
	*/
	function setOwner($a_id)
	{
		$this->owner = $a_id;
	}

	/**
	* Get Owner
	*
	* @return int
	*/
	function getOwner()
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
		$this->recordfields[$field_id]->setValue($value);
	}

	/**
	* Get Field Value
	*
	* @param int $a_id
	* @return array
	*/
	function getRecordFieldValue($field_id)
	{
        $this->loadRecordFields();
		return $this->recordfields[$field_id];
	}

    private function loadRecordFields(){
        if($this->recordfields == NULL){
            $this->loadTable();
            $recordfields = array();
            foreach($this->table->getFields() as $field){
                $recordfields[$field->getId()] = new ilDataCollectionRecordField($this, $field);
            }
            $this->recordfields = $recordfields;
        }
    }

    private function loadTable(){
        if($this->table == Null){
            $this->table = new ilDataCollectionTable($this->tableId);
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

    function doUpdate(){
        global $ilDB;
        $ilDB->update("il_dcl_record", array(
            "table_id" => array("integer", $this->tableId),
            "create_date" => array("date", $this->getCreateDate()),
            "last_update" => array("date", $this->getLastUpdate()),
            "owner" => array("text", $this->getOwner())
        ), array(
            "id" => array("integer", $this->id)
        ));

        foreach($this->recordfields as $recordfield){
            $recordfield->doUpdate();
        }
    }

    //TODO: this method should be replaced by a method in table class getRecords.
    /**
     * get All records
     *
     * @param int $a_id
     * @param array $recordfields
     */
    static function getAll($a_id,array $recordfields, $tabledefinition)
    {
        global $ilDB, $ilUser;

        $query= "Select ";

        if(is_array($tabledefinition) && count($tabledefinition) > 0 && !$tabledefinition[0])
        {
            foreach($tabledefinition as $key => $value)
            {
                if(in_array($value, self::getStandardFields()))
                {
                    $query .= "rc.".$value.",";
                }
            }
        }
        else
        {
            foreach(self::getStandardFields() as $key)
            {
                $query .= "rc.".$key.",";
            }
        }

        $query = substr($query, 0, -1);

        foreach($recordfields as $recordfield)
        {
            $query .= ", (SELECT val.value FROM il_dcl_record record".
                " LEFT JOIN il_dcl_record_field rcfield ON rcfield.record_id = record.id AND".
                " rcfield.field_id = ".$recordfield["id"].
                " LEFT JOIN il_dcl_field field ON field.id = rcfield.field_id".
                " LEFT JOIN il_dcl_stloc".$recordfield["storage_location"]."_value val ON".
                " val.record_field_id = rcfield.id".
                " WHERE record.id = rc.id".
                " ) record_field_".$recordfield["id"];

            //$query .= ",".$recordfield['datatype_id']." datatype_id";
        }

        $query .= " From il_dcl_record rc WHERE rc.table_id = ".$ilDB->quote($a_id,"integer").
            " ORDER BY rc.id";

        $set = $ilDB->query($query);

        $all = array();
        while($rec = $ilDB->fetchAssoc($set))
        {
            $rec['owner'] = $ilUser->_lookupLogin($rec['owner']); // Benutzername anstelle der ID
            $all[] = $rec; //$rec['id']
        }

        return $all;
    }



}
?>