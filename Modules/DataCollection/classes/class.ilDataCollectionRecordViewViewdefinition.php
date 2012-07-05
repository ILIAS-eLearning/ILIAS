<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

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

class ilDataCollectionRecordViewViewdefinition extends ilPageObject
{
	protected $tableId; // [int]
	protected $type; // [int]  0 = recordview 
	protected $formtype; // [int] 0 = copage

	function __construct($a_view_id = 0, $a_table_id = 0)
	{
		parent::__construct("dclf", $a_view_id, 0, true);
				
		if ($a_table_id != 0)
		{
			$this->setTableId($a_table_id);	
		}
		if($a_view_id != 0)
		{
			$this->setId($a_view_id);
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

		$query = "SELECT * FROM il_dcl_view WHERE table_id = ".$ilDB->quote($this->getId(),"integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTableId($rec["table_id"]);
		$this->type = $rec["type"];
		$this->formtype = $rec["formtype"];
	}


	/**
	* Create new Viewdefinition
	*/
	function create()
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
		
		parent::create();
	}

	/**
	 * Update Viewdefinition
	 * @param bool $a_validate
	 * @param bool $a_no_history
	 * @return boolean
	 */
	function update($a_validate = true, $a_no_history = false)
	{		
		//TODO
		//Page-Object updaten
		//Es wäre auch möglich direkt in der GUI-Klasse ilPageObject aufzurufen. Falls wir aber bei DoCreate, 
		//das Page-Object anlegen, fänd ich es sinnvoll, wenn wir auch hier das PageObject updaten würden.
       //Andernfalls sämtliche Page-Object-Methoden in der GUI-Klasse aufrufen.
		
		parent::update($a_validate, $a_no_history);
		
		return true;
	}
	
	/**
	 * Get view definition id by table id
	 * 
	 * In the moment we have only one View-Viewdefinition per Table
	 * 
	 * @param int $a_table_id
	 * @return inte 
	 */
	public static function getIdByTableId($a_table_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT id FROM il_dcl_view".
			" WHERE table_id = ".$ilDB->quote($a_table_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		return $row["id"];
	}
	
	/**
	 * Get all placeholders for table id
	 * @param int $a_table_id
	 * @param bool $a_verbose
	 * @return array 
	 */
	public static function getAvailablePlaceholders($a_table_id, $a_verbose = false)
	{
		$all = array();
			
		require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
		$fields = ilDataCollectionField::getAll($a_table_id);
		foreach($fields as $field)
		{
			if(!$a_verbose)
			{
				$all[] = "[".$field["title"]."]";
			}
			else
			{
				$all["[".$field["title"]."]"] = $field;
			}
		}
		
		return $all;
	}
}

?>