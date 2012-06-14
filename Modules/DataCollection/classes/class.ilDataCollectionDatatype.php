<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDataCollectionDatatype
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @ingroup ModulesDataCollection
*/

class ilDataCollectionDatatype
{
	protected $id; // [int]
	protected $title; // [string]
	protected $storageLocation; // [int]
	
	// TEXT
	const INPUTFORMAT_TEXT 			= 2;
	// NUMBER
	const INPUTFORMAT_NUMBER 		= 1;
	// REFERENCE
	const INPUTFORMAT_REFERENCE 	= 3;
	// DATETIME
	const INPUTFORMAT_BOOLEAN 		= 4;
	// REFERENCE
	const INPUTFORMAT_DATETIME 		= 5;
	// FILE
	const INPUTFORMAT_FILE 			= 6;


	/**
	* Constructor
	* @access public
	* @param  integer datatype_id
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
	* Get field id
	*
	* @return int
	*/
	function getId()
	{
		return $this->id;
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
	* Set Storage Location
	*
	* @param int $a_id
	*/
	function setStorageLocation($a_id)
	{
		$this->storageLocation = $a_id;
	}

	/**
	* Get Storage Location
	*
	* @return int
	*/
	function getStorageLocation()
	{
		return $this->storageLocation;
	}


	/**
	* Read Datatype
	*/
	function doRead()
	{
		global $ilDB;

		$query = "SELECT * FROM il_dcl_datatype WHERE id = ".$ilDB->quote($this->getId(),"integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTitle($rec["title"]);
		$this->setStorageLocation($rec["storage_location"]);
	}


	/**
	* Get all possible Datatypes
	*
	* @return array
	*/
	static function getAllDatatypes()
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_dcl_datatype";
		$set = $ilDB->query($query);
		
		$all = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$all[$rec[id]] = $rec; 
		}	

		return $all;
	}


	/**
	* Get all properties of a Datatype
	*
	* @param int $a_id datatype_id
	* @return array
	*/
	function getProperties($a_id)
	{  
		global $ilDB;

		$query = "SELECT * FROM il_dcl_datatype_prop
					WHERE datatype_id = ".$ilDB->quote($a_id,"integer");
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