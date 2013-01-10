<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

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
class ilDataCollectionRecordViewViewdefinition extends ilPageObject
{
	protected $table_id; 	// [int]
	protected $type; 		// [int]  0 = recordview 
	protected $formtype; 	// [int]  0 = copage
	
	/*
	 * __construct
	 */
	public function __construct($a_view_id = 0, $a_table_id = 0)
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
	public function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get Viewdefinition id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set Table ID
	 *
	 * @param int $a_id
	 */
	public function setTableId($a_id)
	{
		$this->table_id = $a_id;
	}

	/**
	 * Get Table ID
	 *
	 * @return int
	 */
	public function getTableId()
	{
		return $this->table_id;
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
	 * Get Formtype
	 *
	 * @return int
	 */
	public function getFormtype()
	{
		return $this->formtype;
	}

	
	/**
	 * Read Viewdefinition
	 */
	public function doRead()
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
	public function create()
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
	public function update($a_validate = true, $a_no_history = false)
	{		
		//TODO
		//Page-Object updaten
		//Es wäre auch möglich direkt in der GUI-Klasse ilPageObject aufzurufen. Falls wir aber bei doCreate, 
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
		
		//FIXME die werte bei type und formtype sollten vom constructor genommen werden
		$set = $ilDB->query("SELECT id FROM il_dcl_view".
			" WHERE table_id = ".$ilDB->quote($a_table_id, "integer")." AND type = ".$ilDB->quote(0, "integer")." and formtype = ".$ilDB->quote(0, "integer"));
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

		require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
		$objTable = ilDataCollectionCache::getTableCache($a_table_id);
		$fields = $objTable->getFields($a_table_id);

		foreach($fields as $field)
		{

			if(!$a_verbose)
			{
				$all[] = "[".$field->getTitle()."]";

                if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE) {
                    $all[] = '[dclrefln field="'.$field->getTitle().'"][/dclrefln]';
                }

                if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF) {
                    $all[] = '[dcliln field="'.$field->getTitle().'"][/dcliln]';
                }
			}
			else
			{
				$all["[".$field->getTitle()."]"] = $field;

                if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE) {
                    $all['[dclrefln field="'.$field->getTitle().'"][/dclrefln]'] = $field;
                }

                if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF) {
                    $all['[dcliln field="'.$field->getTitle().'"][/dcliln]'] = $field;
                }
			}
		}

		return $all;
	}
}

?>