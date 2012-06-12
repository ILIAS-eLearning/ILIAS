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
	/**
	* Constructor
	* @access public
	* @param  integer fiel_id
	*
	*/
	public function __construct($a_id = 0)
	{
	if ($a_id != 0) 
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
	function setFieldvalue($a_value, $a_id)
	{
		$this->field[$a_id] = $a_value;
	}

	/**
	* Get Field Value
	*
	* @param int $a_id
	* @return array
	*/
	function getFieldvalues($a_id)
	{
		return $this->field;
	}



	/**
	* Read record
	*/
	function doRead()
	{
		global $ilDB;

		//Get all fields of a record
		$recordfields = $this->getRecordFields();

		//build query
		$query = "Select  rc.id, rc.table_id , rc.create_date, rc.last_update, rc.owner";

		foreach($recordfields as $recordfield)
		{
			$query .= ", (SELECT val.value 
									FROM il_dcl_record record 
									LEFT JOIN il_dcl_record_field rcfield ON rcfield.record_id = record.id AND rcfield.field_id = ".$recordfield["id"]."
									LEFT JOIN il_dcl_field field ON field.id = rcfield.field_id
									LEFT JOIN il_dcl_stloc".$recordfield["storage_location"]."_value val ON val.record_field_id = rcfield.id
								WHERE record.id = rc.id
								) record_field_".$recordfield["id"];
		}

		$query .= " From il_dcl_record rc WHERE rc.id = ".$ilDB->quote($this->getId(),"integer")." ORDER BY rc.id";



		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);



		$this->setTableId($rec["table_id"]);
		$this->setCreateDate($rec["create_date"]);
		$this->setLastUpdate($rec["last_update"]);
		$this->setOwner($rec["owner"]);


		foreach($recordfields as $recordfield)
		{
			$this->setFieldvalue($rec["record_field_".$recordfield["id"]],$recordfield["id"]);
		}
	}


	/**
	* get All records
	*
	* @param int $a_id
	* @param array $recordfields
	*/
	function getAll($a_id,$recordfields = array())
	{
		global $ilDB;

		//build query
		$query = "Select  rc.id, rc.table_id , rc.create_date, rc.last_update, rc.owner";

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
		}

		$query .= " From il_dcl_record rc WHERE rc.table_id = ".$ilDB->quote($a_id,"integer").
					" ORDER BY rc.id";

		$set = $ilDB->query($query);

		$all = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$all[$rec['id']] = $rec;
		}

		return $all; 
	}


	/**
	* Create new record
	*
	* @param array $all_fields
	*
	*/
	function DoCreate($all_fields)
	{
		global $ilDB;

		//Record erzeugen
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


		//zugehörige Felder speichern
		foreach($this->getFieldvalues() AS $key => $fieldvalue) {
			$record_field_id = $ilDB->nextId("il_dcl_record_field");
			$query = "INSERT INTO il_dcl_record_field (
								id,
								record_id,
								field_id
							) VALUES (".
								$ilDB->quote($record_field_id, "integer").",".
								$ilDB->quote($this->getId(), "integer").",".
								$ilDB->quote($key, "integer")."
							)";
			$ilDB->manipulate($query);



			//Werte speichern
			$record_value_id = $ilDB->nextId("il_dcl_stloc".$all_fields[$key]['storage_location']."_value");
			$query = "INSERT INTO il_dcl_stloc".$all_fields[$key]['storage_location']."_value (
								id, 
								record_field_id,
								value
							) VALUES (".
								$ilDB->quote($record_value_id, "integer").",".
								$ilDB->quote($record_field_id, "integer").",".
								$ilDB->quote($fieldvalue, $all_fields[$record_field_id]['ildb_type'])."
							)";
			$ilDB->manipulate($query);
		}
    }


	/**
	* Get all fields of a record
	*
	* @return array
	*/
	function getRecordFields()
	{  
		global $ilDB;

		$query = "SELECT rcfield.id id, field.title title, field.description description,".
					" field.datatype_id datatype_id, dtype.title datatype,". 
					" dtype.storage_location storage_location FROM `il_dcl_record_field` rcfield". 
					" LEFT JOIN il_dcl_field field ON field.id = rcfield.field_id". 
					" LEFT JOIN il_dcl_datatype dtype ON dtype.id = field.datatype_id". 
					" WHERE rcfield.record_id = ".$ilDB->quote($this->getId(),"integer");
		$set = $ilDB->query($query);

		$all = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$all[] = $rec;
		}

		return $all;
	}
}
?>