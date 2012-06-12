<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDataCollectionFieldProp
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @ingroup ModulesDataCollection
*/

class ilDataCollectionFieldProp
{
	protected $id; // [int]
	protected $datatype_property_id; //[int]
	protected $value; //[string]
	protected $field_id; // [int]


	/**
	* Constructor
	*
	* @param  int datatype_id
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
	* Set id
	*
	* @param int $a_id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get id
	*
	* @return int
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Set property id
	*
	* @param int $a_id
	*/
	function setDatatypePropertyId($a_id)
	{
		$this->datatype_property_id = $a_id;
	}

	/**
	* Get property id
	*
	* @return int
	*/
	function getDatatypePropertyId()
	{
		return $this->datatype_property_id;
	}

	/**
	* Set value
	*
	* @param string $a_value
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get value
	*
	* @return string
	*/
	function getValue()
	{
		return $this->value;
	}

	/**
	* Set field id
	*
	* @param int $a_id
	*/
	function setFieldId($a_id)
	{
		$this->field_id = $a_id;
	}

	/**
	* Get field id
	*
	* @return int
	*/
	function getFieldId()
	{
		return $this->field_id;
	}


	/**
	* Read Datatype
	*/
	function doRead()
	{
		global $ilDB;

		$query = "SELECT * FROM il_dcl_field_prop WHERE id = ".$ilDB->quote($this->getId(),"integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setDatatypePropertyId($rec["property_id"]);
		$this->setValue($rec["value"]);
		$this->setFieldId($rec["field_id"]);
		
	}


	/**
	* Create new field property
	*/
	function DoCreate()
	{
		global $ilDB;

		$id = $ilDB->nextId("il_dcl_field_prop");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_field_prop (".
			"id".
			", datatype_prop_id".
			", field_id".
			", value".
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getDatatypePropertyId(), "integer")
			.",".$ilDB->quote($this->getFieldId(), "integer")
			.",".$ilDB->quote($this->getValue(), "text")
			.")";
		$ilDB->manipulate($query);
	}
}

?>