<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDataCollectionRecordListViewdefinition
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @ingroup ModulesDataCollection
*/

class ilDataCollectionRecordListViewdefinition
{
	protected $id; // [int] table il_dcl_view
	protected $table_id; // [int] table il_dcl_view
	protected $type; // [int] table il_dcl_view
	protected $formtype; // [int] table il_dcl_view
	//protected $field; // [string] il_dcl_viewdefinition: field_id or specific tabledefinition field (id, dcl_table_id, create_date, last_update, owner)
	protected $arr_fieldorder; // [int] il_dcl_viewdefinition

	
	/**
	* Constructor
	* @access public
	* @param  integer table_id
	*
	* At the moment we have one view per table. If we will have more than one view, we should work additional with the view_id
	*
	*/
	public function __construct($a_table_id)
	{
		$this->type = 1; //Type list
		$this->formtype = 1; //FieldOrder-Formular

		if($a_table_id != 0)
		{
			$this->tableId = $a_id;
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
	* Set table ID
	*
	* @param int $a_id
	*/
	function setTableId($a_id)
	{
		$this->tableId = $a_id;
	}

	/**
	* Get table ID
	*
	* @return int
	*/
	function getTableId()
	{
		return $this->tableId;
	}

	/**
	* Set type
	*
	* @param int $a_type
	*/
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* Get type
	*
	* @return int
	*/
	function getType()
	{
		return $this->type;
	}

	/**
	* Set formtype
	*
	* @param int $a_formtype
	*/
	function setFormType($a_formtype)
	{
		$this->formtype = $a_formtype;
	}

	/**
	* Get formtype
	*
	* @return int
	*/
	function getFormType()
	{
		return $this->formtype;
	}


	/**
	* Set field order
	*
	* @param string $a_order
	* @param string $a_key
	*/
	function setArrFieldOrder($a_order,$a_key)
	{
		$this->arrfieldorder[$a_key] = $a_order;
	}

	/**
	* Get field order
	*
	* @return array
	*/
	function getArrFieldOrder()
	{
		return $this->arrfieldorder;
	}

	
	/**
	* Read
	*/
	function doRead()
	{
		global $ilDB;

		$query = "SELECT 	il_dcl_viewdefinition.field field,
										il_dcl_viewdefinition.field_order fieldorder,
							FROM il_dcl_view
							LEFT JOIN il_dcl_viewdefinition viewdef ON viewdef.view_id = il_dcl_view.id 
							WHERE table_id = ".$ilDB->quote($this->getTableId(),"integer")." 
							AND type = ".$ilDB->quote($this->getType(),"integer")."
							AND formtype = ".$ilDB->quote($this->getFormType(),"integer")."
							ORDER by il_dcl_viewdefinition.field_order";
	
		$all = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$this->setArrFieldOrder($rec['fieldorder'],$rec['field']);
		}
	}



	/**
	* Create
	*/
	function DoCreate()
	{
		global $ilDB;

		$id = $ilDB->nextId("il_dcl_view");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_view (".
		"id".
		", table_id".
		", type".
		", formtype".
		" ) VALUES (".
		$ilDB->quote($this->getId(), "integer")
		.",".$ilDB->quote($this->getTableId(), "integer")
		.",".$ilDB->quote($this->getType(), "integer")
		.",".$ilDB->quote($this->getFormType(), "integer")
		.")";
		$ilDB->manipulate($query);

		foreach($this->getArrFieldOrder() as $key => $order) 
		{
			$viewdefinitionid = $ilDB->nextId("il_dcl_viewdefinition");

			$query = "INSERT INTO il_dcl_viewdefinition (".
			"id".
			", view_id".
			", field".
			", field_order".
			" ) VALUES (".
			$ilDB->quote($viewdefinitionid, "integer")
			.",".$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($key, "text")
			.",".$ilDB->quote($order, "integer")
			.")";
			$ilDB->manipulate($query);
		}
	}

	/**
	* Update field
	*/
/*
	function DoUpdate()
	{
		global $ilDB;

		$ilDB->update("il_dcl_field", array(
								"table_id" => array("integer", $this->getTableId()),
								"datatype_id" => array("text", $this->getDatatypeId()),
								"title" => array("text", $this->getTitle()),
								"description" => array("text", $this->getDescription()),
								"required" => array("integer",$this->getRequired())
								), array(
								"id" => array("integer", $this->getId())
								));
	}
*/	

}

?>