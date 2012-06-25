<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDataCollectionField
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @ingroup ModulesDataCollection
*/

class ilDataCollectionField
{
	protected $id; // [int]
	protected $tableId; // [int]
	protected $title; // [string]
	protected $description; // [string]
	protected $datatypeId; // [int]
	protected $length; // [int]
	protected $regex; // [text]
	protected $required; // [bool]

	const PROPERTYID_LENGTH = 1;
	const PROPERTYID_REGEX = 2;

	
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
	* Set table id
	*
	* @param int $a_id
	*/
	function setTableId($a_id)
	{
		$this->tableId = $a_id;
	}

	/**
	* Get table id
	*
	* @return int
	*/
	function getTableId()
	{
		return $this->tableId;
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
	* Set description
	*
	* @param string $a_desc
	*/
	function setDescription($a_desc)
	{
		$this->desc = $a_desc;
	}

	/**
	* Get description
	*
	* @return string
	*/
	function getDescription()
	{
		return $this->desc;
	}

	/**
	* Set datatype id
	*
	* @param int $a_id
	*/
	function setDatatypeId($a_id)
	{
		$this->datatypeId = $a_id;
	}

	/**
	* Get datatype_id
	*
	* @return int
	*/
	function getDatatypeId()
	{
		return $this->datatypeId;
	}

	/**
	* Set length
	*
	* @param int $a_id
	*/
	function setLength($a_id)
	{
		$this->length = $a_id;
	}

	/**
	* Get length
	*
	* @return text
	*/
	function getLength()
	{
		return $this->length;
	}

	/**
	* Set Regex
	*
	* @param string $a_regex
	*/
	function setRegex($a_regex)
	{
		$this->regex = $a_regex;
	}

	/**
	* Get Required
	*
	* @return string
	*/
	function getRegex()
	{
		return $this->regex;
	}

	/**
	* Set Required
	*
	* @param boolean $a_required Required
	*/
	function setRequired($a_required)
	{
		$this->required = $a_required;
	}

	/**
	* Get Required Required
	*
	* @return boolean
	*/
	function getRequired()
	{
		return $this->required;
	}

	/**
	* Set Property Value
	*
	* @param string $a_value
	* @param int $a_id
	*/
	function setPropertyvalue($a_value, $a_id)
	{
		$this->property[$a_id] = $a_value;
	}

	/**
	* Get Property Values
	*
	* @param int $a_id
	* @return array
	*/
	function getPropertyvalues()
	{
		return $this->property;
	}

	/**
	* Set has properties
	*
	* @param boolean $has_options hasOptions
	*/
	/*
	function setHasProperties($has_properties)
	{
		$this->hasProperties = $a_id;
	}
	*/
	/**
	* Get has_properties
	*
	* @return boolean  hasProperties
	*/
	/*
	function getHasProperties()
	{
		return $this->hasProperties;
	}
	*/
	
	/**
	* Read field
	*/
	function doRead()
	{
		global $ilDB;

		//$query = "SELECT f.*, CASE WHEN (SELECT COUNT(*) FROM il_dcl_field_prop fo WHERE fo.field_id = f.id) > 0 THEN 1 ELSE 0 END AS has_options FROM il_dcl_field f WHERE id = ".$ilDB->quote($this->getId(),"integer");
		$query = "SELECT * FROM il_dcl_field WHERE id = ".$ilDB->quote($this->getId(),"integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTableId($rec["table_id"]);
		$this->setTitle($rec["title"]);
		$this->setDescription($rec["description"]);
		$this->setDatatypeId($rec["datatype_id"]);
		$this->setRequired($rec["required"]);

		//Set the additional properties 
		$this->setProperties();

		
	}


	/**
	* get All records
	*
	* @param int $a_id Table Id
	*
	*/
	function getAll($a_id)
	{
		global $ilDB;

		//build query
		$query = "SELECT	field.id, 
										field.table_id, 
										field.title, 
										field.description, 
										field.datatype_id, 
										field.required, 
										datatype.ildb_type,
										datatype.title datatype_title, 
										datatype.storage_location
							FROM il_dcl_field field LEFT JOIN il_dcl_datatype datatype ON datatype.id = field.datatype_id
							WHERE table_id = ".$ilDB->quote($a_id,"integer");
		$set = $ilDB->query($query);
	
		$all = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$all[$rec['id']] = $rec;
		}

		return $all; 
	}


	/**
	* Create new field
	*/
	function DoCreate()
	{
		global $ilDB;

		$id = $ilDB->nextId("il_dcl_field");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_field (".
		"id".
		", table_id".
		", datatype_id".
		", title".
		", description".
		", required".
		" ) VALUES (".
		$ilDB->quote($this->getId(), "integer")
		.",".$ilDB->quote($this->getTableId(), "integer")
		.",".$ilDB->quote($this->getDatatypeId(), "integer")
		.",".$ilDB->quote($this->getTitle(), "text")
		.",".$ilDB->quote($this->getDescription(), "text")
		.",".$ilDB->quote($this->getRequired(), "integer")
		.")";
		$ilDB->manipulate($query);
	}

	/**
	* Update field
	*/
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


		/**
		* Get all properties of a field
		*
		* @return array
		*/
		function setProperties()
		{  
			global $ilDB;
			
			$query = "SELECT	datatype_prop_id, 
											title, 
											value 
							FROM il_dcl_field_prop fp 
							LEFT JOIN il_dcl_datatype_prop AS p ON p.id = fp.datatype_prop_id
							WHERE fp.field_id = ".$ilDB->quote($this->getId(),"integer");
			$set = $ilDB->query($query);
			
			while($rec = $ilDB->fetchAssoc($set))
			{
				$this->setPropertyvalue($rec['value'],$rec['datatype_prop_id']);
			}
		}
		
		/**
		* Get a property of a field
		*
		* @param int $id Field Id
		* @param int $prop_id Property_Id
		*
		* @return array
		*/
/*
		function getProperty($id, $prop_id)
		{  
			global $ilDB;
			
			$query = "SELECT datatype_prop_id, title, value FROM il_dcl_field_prop fp 
			LEFT JOIN il_dcl_datatype_prop p ON p.id = fp.datatype_prop_id AND il_dcl_datatype_prop.id =".$ilDB->quote($prop_id, "integer")."
			WHERE fp.field_id = ".$ilDB->quote($id, "integer");
			$set = $ilDB->query($query);
			
			while($rec = $ilDB->fetchObject($set))
			{
				$data[] = $rec;
			}
		}
		*/

		/**
		* Get all properties of a field
		*
		* @return array
		*/
		function getProperties($id)
		{  
			global $ilDB;
			
			$query = "SELECT datatype_prop_id, title, value FROM il_dcl_field_prop fp 
			LEFT JOIN il_dcl_datatype_prop p ON p.id = fp.datatype_prop_id
			WHERE fp.field_id = ".$ilDB->quote($id, "integer");
			$set = $ilDB->query($query);
			
			while($rec = $ilDB->fetchObject($set))
			{
				$data[] = $rec;
			}
		}
	

}

?>