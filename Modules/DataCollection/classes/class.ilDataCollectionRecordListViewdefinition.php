<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
			$this->table_id = $a_table_id;
			$this->doRead();
		}
	}

	/**
	 * Set id
	 *
	 * @param int $a_id
	 */
	public function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set table ID
	 *
	 * @param int $a_id
	 */
	public function setTableId($a_id)
	{
		$this->table_id = $a_id;
	}

	/**
	 * Get table ID
	 *
	 * @return int
	 */
	public function getTableId()
	{
		return $this->table_id;
	}

	/**
	 * Set type
	 *
	 * @param int $a_type
	 */
	public function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	 * Get type
	 *
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set formtype
	 *
	 * @param int $a_formtype
	 */
	public function setFormType($a_formtype)
	{
		$this->formtype = $a_formtype;
	}

	/**
	 * Get formtype
	 *
	 * @return int
	 */
	public function getFormType()
	{
		return $this->formtype;
	}


	/**
	 * Set field order
	 *
	 * @param string $a_order
	 * @param string $a_key
	 */
	public function setArrFieldOrder($a_order,$a_key)
	{
		$this->arrfieldorder[$a_key] = $a_order;
	}

	/**
	 * Get field order
	 *
	 * @return array
	 */
	public function getArrFieldOrder()
	{
		return $this->arrfieldorder;
	}


	/**
	 * Set table definition
	 *
	 * @param string $title
	 * @param string $a_key
	 */
	public function setTabledefinition($title,$a_key)
	{
		$this->arrtabledefinition[$a_key]['title'] = $title;
	}

	/**
	 * Get table definition
	 *
	 * @return array
	 */
	public function getArrTabledefinition()
	{
		return $this->arrtabledefinition;
	}

	/**
	 * Set record fields
	 *
	 * @param string $storage_location
	 * @param string $a_key
	 */
	public function setRecordfield($storage_location,$a_key)
	{
		$this->arrrecordfield[$a_key]['id'] = $a_key;
		$this->arrrecordfield[$a_key]['storage_location'] = $storage_location;
	}

	/**
	 * Get table definition
	 *
	 * @return array
	 */
	public function getArrRecordfield()
	{
		return $this->arrrecordfield;
	}
	
	/**
	 * Read
	 */
	public function doRead()
	{
		global $ilDB;

		$query = "SELECT 	il_dcl_viewdefinition.field field,
										il_dcl_viewdefinition.field_order fieldorder,
										CASE il_dcl_viewdefinition.field
											WHEN il_dcl_field.id THEN  il_dcl_field.title
											ELSE il_dcl_viewdefinition.field
										END title,
										il_dcl_datatype.storage_location storage_location
							FROM il_dcl_view
							LEFT JOIN il_dcl_viewdefinition ON il_dcl_viewdefinition.view_id = il_dcl_view.id 
							LEFT JOIN il_dcl_field ON il_dcl_viewdefinition.field = il_dcl_field.id 
							LEFT JOIN il_dcl_datatype ON  il_dcl_field.datatype_id = il_dcl_datatype.id
							WHERE il_dcl_view.table_id = ".$ilDB->quote($this->getTableId(),"integer")." 
							AND il_dcl_view.type = ".$ilDB->quote($this->getType(),"integer")."
							AND il_dcl_view.formtype = ".$ilDB->quote($this->getFormType(),"integer")."
							ORDER by il_dcl_viewdefinition.field_order";


		$set = $ilDB->query($query);
	
		$all = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$this->setArrFieldOrder($rec['fieldorder'], $rec['field']);

			$this->setTabledefinition($rec['title'], $rec['field']);
			if($rec['storage_location']) 
			{
				$this->setRecordfield($rec['storage_location'],$rec['field']);
			}
		}
	}



	/**
	 * Create
	 */
	public function doCreate()
	{
		global $ilDB;

		$ilDB->manipulate('DELETE FROM il_dcl_view
			WHERE table_id = '.$ilDB->quote($this->getTableId(), "integer").' 
			AND type = '.$ilDB->quote($this->getType(), "integer").' 
			AND formtype = '.$ilDB->quote($this->getFormType(), "integer"));

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
}

?>