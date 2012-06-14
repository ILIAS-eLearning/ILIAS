<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDataCollectionRecordViewViewdefinition
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @ingroup ModulesDataCollection
*/

class ilDataCollectionRecordViewViewdefinition
{
	protected $id; // [int]
	protected $tableId; // [int]
	protected $type; // [int]  0 = recordview 
	protected $formtype; // [int] 0 = copage


	/**
	* Constructor
	* @access public
	* @param  int $a_table_id Table ID
	*
	*/
	public function __construct($a_table_id = 0)
	{
		//In the moment we have only one View-Viewdefinition per Table
		if ($a_table_id != 0)
		{
			$this->tableId = $a_table_id;
			$this->doRead();
		}
		
		//Default-Values
		$this->type = 0; 		// recordview
		$this->formtype = 0; 	// copage
	}

	/**
	* Set Viewdefinition id
	*
	* @param int $a_id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Viewdefinition id
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
	* Get type
	*
	* @return int
	*/
	function getType()
	{
		return $this->type;
	}

	/**
	* Get Formtype
	*
	* @return int
	*/
	function getFormtype()
	{
		return $this->formtype;
	}

	
	/**
	* Read Viewdefinition
	*/
	function doRead()
	{
		global $ilDB;

		$query = "SELECT * FROM il_dcl_view WHERE table_id = ".$ilDB->quote($this->getTableId(),"integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setId($rec["id"]);
	}


	/**
	* Create new Viewdefinition
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
			.",".$ilDB->quote($this->getFormtype(), "integer")
			.")";
		$ilDB->manipulate($query);

		//TODO
		//Page-Object anlegen
		//parent_id: $this->getId()
        //parent_typ: dclf
	}

	/**
	* Update Viewdefinition
	*/
	public function doUpdate()
	{
		//TODO
		//Page-Object updaten
		//Es wäre auch möglich direkt in der GUI-Klasse ilPageObject aufzurufen. Falls wir aber bei DoCreate, 
		//das Page-Object anlegen, fänd ich es sinnvoll, wenn wir auch hier das PageObject updaten würden.
       //Andernfalls sämtliche Page-Object-Methoden in der GUI-Klasse aufrufen.

		return true;
	}	 
}

?>