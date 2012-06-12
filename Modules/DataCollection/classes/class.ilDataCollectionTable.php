<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDataCollectionTable
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @ingroup ModulesDataCollection
*/

class ilDataCollectionTable
{
	protected $id; // [int]
	protected $objId; // [int]
	protected $title; // [string]
	

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
	* Set table id
	*
	* @param int $a_id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get table id
	*
	* @return int
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Set object id
	*
	* @param int $obj_id
	*/
	function setObjId($a_id)
	{
		$this->objId = $a_id;
	}

	/**
	* Get object id
	*
	* @return int
	*/
	function getObjId()
	{
		return $this->objId;
	}

	/**
	* Set title
	*
	* @param string $a_title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get title
	*
	* @return string
	*/
	function getTitle()
	{
		return $this->title;
	}

	
	/**
	* Read table
	*/
	function doRead()
	{
		global $ilDB;

		$query = "SELECT * FROM il_dcl_table WHERE id = ".$ilDB->quote($this->getId(),"integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setObjId($rec["obj_id"]);
		$this->setTitle($rec["title"]);		
	}


	/**
	* get all tables of a Data Collection Object
	*
	* @param int $a_id obj_id
	*
	*/
	function getAll($a_id)
	{
		global $ilDB;

		//build query
		$query = "SELECT	*
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

	/**
	* Create new table
	*/
	function DoCreate()
	{
		global $ilDB;

		$id = $ilDB->nextId("il_dcl_table");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_table (".
		"id".
		", obj_id".
		", title".
		" ) VALUES (".
		$ilDB->quote($this->getId(), "integer")
		.",".$ilDB->quote($this->getObjId(), "integer")
		.",".$ilDB->quote($this->getTitle(), "text")
		.")";
		$ilDB->manipulate($query);

	}
}

?>