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
	protected $id; // [mixed] (int for custom fields string for stdfields)
	protected $table_id; // [int]
	protected $title; // [string]
	protected $description; // [string]
	protected $datatypeId; // [int]
	protected $required; // [bool]
	protected $order; // [int]
	protected $unique; //[bool]
	/**
	 * @var bool whether this field is visible for everyone.
	 */
	protected $visible;
	protected $editable;
	protected $filterable;

	/**
	 * @var bool
	 */
	protected $locked;

	/**
	 * @var ilDataCollectionDatatype This fields Datatype.
	 */
	protected $datatype;

	const PROPERTYID_LENGTH = 1;
	const PROPERTYID_REGEX = 2;
	const PROPERTYID_REFERENCE = 3;
	const PROPERTYID_URL = 4;
	const PROPERTYID_TEXTAREA = 5;



	// type of table il_dcl_view
	const VIEW_VIEW 		= 1;
	const EDIT_VIEW 		= 2;
	const FILTER_VIEW 	= 3;

	/**
	 * Constructor
	 * @access public
	 * @param  integer fiel_id
	 *
	 */
	public function __construct($a_id = 0)
	{
		if($a_id != 0) 
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
	public function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get field id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set table id
	 *
	 * @param int $a_id
	 */
	public function setTableId($a_id)
	{
		$this->table_id = $a_id;
	}

	/**
	 * Get table id
	 *
	 * @return int
	 */
	public function getTableId()
	{
		return $this->table_id;
	}

	/**
	 * Set title
	 *
	 * @param string $a_title
	 */
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set description
	 *
	 * @param string $a_desc
	 */
	public function setDescription($a_desc)
	{
		$this->desc = $a_desc;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->desc;
	}

	/**
	 * Set datatype id
	 *
	 * @param int $a_id
	 */
	public function setDatatypeId($a_id)
	{
		//unset the cached datatype.
		$this->datatype = NULL;
		$this->datatypeId = $a_id;
	}

	/**
	 * Get datatype_id
	 *
	 * @return int
	 */
	public function getDatatypeId()
	{
		if($this->isStandardField())
		{
			return ilDataCollectionStandardField::_getDatatypeForId($this->getId());
		}
		return $this->datatypeId;
	}

	/**
	 * Set Required
	 *
	 * @param boolean $a_required Required
	 */
	public function setRequired($a_required)
	{
		$this->required = $a_required;
	}

	/**
	 * Get Required Required
	 *
	 * @return boolean
	 */
	public function getRequired()
	{
		return $this->required;
	}

	/**
	 * Set Property Value
	 *
	 * @param string $a_value
	 * @param int $a_id
	 */
	public function setPropertyvalue($a_value, $a_id)
	{
		$this->property[$a_id] = $a_value;
	}
	
	/*
	 * isUnique
	 */
	public function isUnique()
	{
		return $this->unique;
	}
	
	/*
	 * setUnique
	 */
	public function setUnique($unique)
	{
		$this->unique = $unique ? 1 : 0;
	}

	/**
	 * Get Property Values
	 *
	 * @param int $a_id
	 * @return array
	 */
	public function getPropertyvalues()
	{
		return $this->property;
	}

	/**
	 * setVisible
	 * @param $visible bool
	 */
	public function setVisible($visible)
	{
		if($visible == true && $this->order === NULL)
		{
			$this->setOrder(0);
		}
		
		$this->visible = $visible;
	}

	/**
	 * setFilterable
	 * @param $filterable bool
	 */
	public function setFilterable($filterable)
	{
		if($filterable == true && $this->order === NULL)
		{
			$this->setOrder(0);
		}
		
		$this->filterable = $filterable;
	}
	
	/*
	 * getDatatype
	 */
	public function getDatatype()
	{
		$this->loadDatatype();
		
		return $this->datatype;
	}
	
	/*
	 * getLength
	 */
	public function getLength()
	{
		$props = $this->getPropertyvalues();
		$l = self::PROPERTYID_LENGTH;
		
		return $props[$l];
	}

	/**
	 * @return bool
	 */
	public function getTextArea(){
		$props = $this->getProperties();
		$t = self::PROPERTYID_TEXTAREA;

		return $props[$t]->value;
	}
	
	/*
	 * getDatatypeTitle
	 */
	public function getDatatypeTitle()
	{
		$this->loadDatatype();
		
		return $this->datatype->getTitle();
	}
	
	/*
	 * getStorageLocation
	 */
	public function getStorageLocation()
	{
		$this->loadDatatype();
		
		return $this->datatype->getStorageLocation();
	}
	
	/*
	 * loadDatatype
	 */
	private function loadDatatype()
	{
		if($this->datatype == NULL)
		{
			$this->datatype = new ilDataCollectionDatatype($this->datatypeId);
		}
	}
	
	/*
	 * isVisible
	 */
	public function isVisible()
	{
		if(!isset($this->visible))
		{
			$this->loadVisibility();
		}
			
		return $this->visible;
	}

	/*
	 * loadVisibility
	 */
	private function loadVisibility()
	{
		if($this->visible == NULL)
		{
			$this->loadViewDefinition(self::VIEW_VIEW);
		}
	}

	/*
	 * isFilterable
	 */
	public function isFilterable()
	{
		if(!isset($this->filterable))
		{
			$this->loadFilterability();
		}
			
		return $this->filterable;
	}

	/*
	 * loadFilterable
	 */
	private function loadFilterability()
	{
		if($this->filterable == NULL)
		{
			$this->loadViewDefinition(self::FILTER_VIEW);
		}
	}


	/**
	 * loadViewDefinition
	 * @param $view use VIEW_VIEW or EDIT_VIEW
	 */
	private function loadViewDefinition($view)
	{
		global $ilDB;
		$query = "  SELECT view.table_id, def.field_order, def.is_set FROM il_dcl_viewdefinition def
						INNER JOIN il_dcl_view view ON view.id = def.view_id AND view.type = ".$view."
						WHERE def.field LIKE '".$this->id."' AND view.table_id = ".$this->table_id;
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);
		$prop = $rec['is_set'];
		
		switch($view)
		{
			case self::VIEW_VIEW:
				$this->visible = $prop;
				break;
			case self::EDIT_VIEW:
				$this->editable = $prop;
				break;
			case self::FILTER_VIEW:
				$this->filterable = $prop;
				break;
		}
		
		$this->order = $rec['field_order'];
	}
	
	/**
	 * isEditable
	 * @return int
	 */
	public function isEditable()
	{
		if(!isset($this->editable))
		{
			$this->loadEditability();
		}
		
		return $this->editable;
	}
	
	/*
	 * editable
	 */
	public function setEditable($editable)
	{
		$this->editable = $editable;
	}

	/*
	 * loadEditability
	 */
	private function loadEditability()
	{
		if($this->editable == NULL)
		{
		   $this->loadViewDefinition(self::EDIT_VIEW);
		}
	}
	
	/*
	 * toArray
	 */
	public function toArray()
	{
		return (array) $this;
	}
	
	/*
	 * isStandardField
	 */
	public function isStandardField()
	{
		return false;
	}

	/**
	 * Read field
	 */
	public function doRead()
	{
		global $ilDB;

		//$query = "SELECT f.*, CASE WHEN (SELECT COUNT(*) FROM il_dcl_field_prop fo WHERE fo.field_id = f.id) > 0 
		//THEN 1 ELSE 0 END AS has_options FROM il_dcl_field f WHERE id = ".$ilDB->quote($this->getId(),"integer");
		$query = "SELECT * FROM il_dcl_field WHERE id = ".$ilDB->quote($this->getId(),"integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTableId($rec["table_id"]);
		$this->setTitle($rec["title"]);
		$this->setDescription($rec["description"]);
		$this->setDatatypeId($rec["datatype_id"]);
		$this->setRequired($rec["required"]);
		$this->setUnique($rec["is_unique"]);
		$this->setLocked($rec["is_locked"]);

		//Set the additional properties 
		$this->setProperties();

	}
	
	/*
	 * buildFromDBRecord
	 */
	public function buildFromDBRecord($rec)
	{
		$this->setId($rec["id"]);
		$this->setTableId($rec["table_id"]);
		$this->setTitle($rec["title"]);
		$this->setDescription($rec["description"]);
		$this->setDatatypeId($rec["datatype_id"]);
		$this->setRequired($rec["required"]);
		$this->setUnique($rec["is_unique"]);
		$this->setLocked($rec["is_locked"]);
		$this->setProperties();
	}

	/**
	 * Create new field
	 */
	public function doCreate()
	{
		global $ilDB;
		$this->getLocked() == NULL?$this->setLocked(false):true;

		$id = $ilDB->nextId("il_dcl_field");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_field (".
			"id".
			", table_id".
			", datatype_id".
			", title".
			", description".
			", required".
			", is_unique".
			", is_locked".
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getTableId(), "integer")
			.",".$ilDB->quote($this->getDatatypeId(), "integer")
			.",".$ilDB->quote($this->getTitle(), "text")
			.",".$ilDB->quote($this->getDescription(), "text")
			.",".$ilDB->quote($this->getRequired(), "integer")
			.",".$ilDB->quote($this->isUnique(), "integer")
			.",".$ilDB->quote($this->getLocked()?1:0, "integer")
			.")";
		$ilDB->manipulate($query);

		$this->updateVisibility();
		$this->updateFilterability();
		$this->updateEditability();
	}

	/**
	 * Update field
	 */
	public function doUpdate()
	{
		global $ilDB;

		$ilDB->update("il_dcl_field", array(
			"table_id" => array("integer", $this->getTableId()),
			"datatype_id" => array("text", $this->getDatatypeId()),
			"title" => array("text", $this->getTitle()),
			"description" => array("text", $this->getDescription()),
			"required" => array("integer",$this->getRequired()),
			"is_unique" => array("integer",$this->isUnique()),
			"is_locked" => array("integer",$this->getLocked()?1:0)
			), array(
			"id" => array("integer", $this->getId())
			));
		$this->updateVisibility();
		$this->updateFilterability();
		$this->updateEditability();
	}

	/*
	 * getFilterable
	 */
	/*public function getFilterable()
	{
		
	}*/

	/*
	 * updateVisibility
	 */
	protected function updateVisibility()
	{
		$this->updateViewDefinition(self::VIEW_VIEW);
	}

	/*
	 * updateFilterability
	 */
	protected function updateFilterability()
	{
		$this->updateViewDefinition(self::FILTER_VIEW);
	}

	protected function updateEditability(){
		$this->updateViewDefinition(self::EDIT_VIEW);
	}

	/**
	 * updateViewDefinition
	 * @param $view use constant VIEW_VIEW or EDIT_VIEW
	 */
	private function updateViewDefinition($view)
	{
		global $ilDB;

		switch($view)
		{
			case self::EDIT_VIEW:
				$set = $this->isEditable();
				break;
			case self::VIEW_VIEW:
				$set = $this->isVisible();
				if($set && $this->order === NULL)
					$this->order = 0;
				break;
			case self::FILTER_VIEW:
				$set = $this->isFilterable();
				if($set && $this->order === NULL)
					$this->order = 0;
				break;
		}

		if(!$set)
			$set = 0;
		else
			$set = 1;
		if(!isset($this->order))
			$this->order = 0;


		$query = "DELETE def FROM il_dcl_viewdefinition def INNER JOIN il_dcl_view ON il_dcl_view.type = "
			.$view." AND il_dcl_view.table_id = "
			.$this->getTableId()." WHERE def.view_id = il_dcl_view.id AND def.field = '"
			.$this->getId()."'";
			
		$ilDB->manipulate($query);
		
		$query = "INSERT INTO il_dcl_viewdefinition (view_id, field, field_order, is_set) SELECT id, '"
			.$this->getId()."', "
			.$this->getOrder().", "
			.$set."  FROM il_dcl_view WHERE il_dcl_view.type = "
			.$view." AND il_dcl_view.table_id = "
			.$this->getTableId();
			
		$ilDB->manipulate($query);
	}
	
	
	/*
	 * deleteViewDefinition
	 */
	private function deleteViewDefinition($view)
	{
		global $ilDB;
		
		$query = "DELETE def FROM il_dcl_viewdefinition def INNER JOIN il_dcl_view ON il_dcl_view.type = "
			.$view." AND il_dcl_view.table_id = "
			.$this->getTableId()." WHERE def.view_id = il_dcl_view.id AND def.field = "
			.$this->getId();
			
		$ilDB->manipulate($query);
	}

	/*
	 * doDelete
	 */
	public function doDelete()
	{
		global $ilDB;

			// delete viewdefinitions.
		$this->deleteViewDefinition(self::VIEW_VIEW);
		$this->deleteViewDefinition(self::FILTER_VIEW);
		$this->deleteViewDefinition(self::EDIT_VIEW);

		$query = "DELETE FROM il_dcl_field_prop WHERE field_id = ".$this->getId();
		$ilDB->manipulate($query);

		$query = "DELETE FROM il_dcl_field WHERE id = ".$this->getId();
		$ilDB->manipulate($query);

	}
	
	/*
	 * getOrder
	 */
	public function getOrder()
	{
		if(!isset($this->order))
		{
			$this->loadVisibility();
		}
		
		return !$this->order?0:$this->order;
	}
	
	/*
	 * setOrder
	 */
	public function setOrder($order)
	{
		$this->order = $order;
	}
	
	/*
	 * getFieldRef
	 */
	public function getFieldRef()
	{
		$props = $this->getPropertyvalues();
		$id = self::PROPERTYID_REFERENCE;
		
		return $props[$id];
	}

	/**
	 * Get all properties of a field
	 *
	 * @return array
	 */
	public function setProperties()
	{  
		global $ilDB;
		
		$query = "SELECT datatype_prop_id, 
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
	 * Get all properties of a field
	 *
	 * @return array
	 */
	public function getProperties()
	{  
		global $ilDB;
		
		$query = "SELECT datatype_prop_id, title, value FROM il_dcl_field_prop fp
		LEFT JOIN il_dcl_datatype_prop p ON p.id = fp.datatype_prop_id
		WHERE fp.field_id = ".$ilDB->quote($this->getId(), "integer");

		$set = $ilDB->query($query);
		
		while($rec = $ilDB->fetchObject($set))
		{
			$data[$rec->datatype_prop_id] = $rec;
		}

		return $data;
	}

	/**
	 * @param boolean $locked
	 */
	public function setLocked($locked)
	{
		$this->locked = $locked;
	}

	/**
	 * @return boolean
	 */
	public function getLocked()
	{
		return $this->locked;
	}
	
	/*
	 * checkValidity
	 */
	public function checkValidity($value, $record_id)
	{
		//Don't check empty values
		if($value == NULL)
		{
			return true;
		}


		if(!ilDataCollectionDatatype::checkValidity($this->getDatatypeId(), $value))
		{
			throw new ilDataCollectionInputException(ilDataCollectionInputException::TYPE_EXCEPTION);
		}
			
		$properties = $this->getPropertyvalues();
		$length = ilDataCollectionField::PROPERTYID_LENGTH;
		$regex_id = ilDataCollectionField::PROPERTYID_REGEX;
		$url = ilDataCollectionField::PROPERTYID_URL;
		
		if($this->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_TEXT)
		{
			$regex = $properties[$regex_id];
			if(substr($regex,0,1) != "/")
				$regex = "/".$regex;
			if(substr($regex, -1) != "/")
				$regex .= "/";
			if($properties[$length] < strlen($value) && is_numeric($properties[$length]))
				throw new ilDataCollectionInputException(ilDataCollectionInputException::LENGTH_EXCEPTION);
			if(!($properties[$regex_id] == NULL || preg_match($regex, $value)))
				throw new ilDataCollectionInputException(ilDataCollectionInputException::REGEX_EXCEPTION);
			if($properties[$url] && !preg_match('(^(news|(ht|f)tp(s?)\://){1}\S+)', $value))
				throw new ilDataCollectionInputException(ilDataCollectionInputException::NOT_URL);
		}
		
		if($this->isUnique())
		{
			$table = new ilDataCollectionTable($this->getTableId());
			
			foreach($table->getRecords() as $record)
			{
				if($record->getRecordFieldValue($this->getId()) == $value && ($record->getId() != $record_id || $record_id == 0))
					throw new ilDataCollectionInputException(ilDataCollectionInputException::UNIQUE_EXCEPTION);

				//for text it has to be case insensitive.
				if($this->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_TEXT)
				{
					if(strtolower($record->getRecordFieldValue($this->getId())) == strtolower($value) && ($record->getId() != $record_id || $record_id == 0))
					{
						throw new ilDataCollectionInputException(ilDataCollectionInputException::UNIQUE_EXCEPTION);
					}
						
				}

				if($this->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_DATETIME)
				{
					$datestring = $value["date"]." ".$value["time"];//["y"]."-".$value["date"]['m']."-".$value["date"]['d']." 00:00:00";
					
					if($record->getRecordFieldValue($this->getId()) == $datestring && ($record->getId() != $record_id || $record_id == 0))
					{
						throw new ilDataCollectionInputException(ilDataCollectionInputException::UNIQUE_EXCEPTION);
					}
				}
			}
		}
		
		return true;
	}
	
	/*
	 * cloneStructure
	 */
	public function cloneStructure($original_id)
	{
		$original = new ilDataCollectionField($original_id);
		$this->setTitle($original->getTitle());
		$this->setDatatypeId($original->getDatatypeId());
		$this->setDescription($original->getDescription());
		$this->setEditable($original->isEditable());
		$this->setLocked($original->getLocked());
		$this->setFilterable($original->isFilterable());
		$this->setVisible($original->isVisible());
		$this->setOrder($original->getOrder());
		$this->setRequired($original->getRequired());
		$this->setUnique($original->isUnique());
		$this->setProperties($original->getProperties());
		$this->doCreate();
	}
}

?>