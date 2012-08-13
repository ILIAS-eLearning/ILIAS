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
	protected $length; // [int]
	protected $regex; // [text]
	protected $required; // [bool]
    /**
     * @var bool whether this field is visible for everyone.
     */
    protected $visible;

    /**
     * @var ilDataCollectionDatatype This fields Datatype.
     */
    protected $datatype;

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
		$this->table_id = $a_id;
	}

	/**
	* Get table id
	*
	* @return int
	*/
	function getTableId()
	{
		return $this->table_id;
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
        //unset the cached datatype.
        $this->datatype = NULL;
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
     * setVisible
     * @param $visible bool
     */
    function setVisible($visible)
    {
        $this->visible = $visible;
    }
    
    /*
     * getDatatype
     */
    function getDatatype()
    {
        $this->loadDatatype();
        
        return $this->datatype;
    }
    
    /*
     * getDatatypeTitle
     */
    function getDatatypeTitle()
    {
        $this->loadDatatype();
        
        return $this->datatype->getTitle();
    }
    
    /*
     * getStorageLocation
     */
    function getStorageLocation()
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
        $this->loadVisibility();
        
        return $this->visible;
    }
    
	/*
	 * loadVisibility
	 */
    private function loadVisibility()
    {
        if($this->visible == NULL)
        {
            global $ilDB;
            $query = "  SELECT view.table_id FROM il_dcl_viewdefinition def
                        INNER JOIN il_dcl_view view ON view.id = def.view_id
                        WHERE def.field LIKE '".$this->id."' AND view.table_id = ".$this->table_id;
            $set = $ilDB->query($query);
            $this->visible = $set->numRows() != 0 ;
        }
    }
    
    /**
	* isEditable
	* @return int
	*/
	public function isEditable()
	{
		$this->loadEditability();
		
		return $this->editable;
	}
	
	/*
	 * loadEditability
	 */
    private function loadEditability()
    {
        if($this->editable == NULL)
        {
            global $ilDB;
            // TODO: Abfrage muss noch gemacht werden
            /*$query = "  SELECT view.table_id FROM il_dcl_viewdefinition def
                        INNER JOIN il_dcl_view view ON view.id = def.view_id
                        WHERE def.field LIKE '".$this->id."' AND view.table_id = ".$this->table_id;
            $set = $ilDB->query($query);
            $this->editable = $set->numRows() != 0 ;*/
            $this->editable = 0;
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
	
	/*
	 * buildFromDBRecord
	 */
    function buildFromDBRecord($rec)
    {
        $this->setId($rec["id"]);
        $this->setTableId($rec["table_id"]);
        $this->setTitle($rec["title"]);
        $this->setDescription($rec["description"]);
        $this->setDatatypeId($rec["datatype_id"]);
        $this->setRequired($rec["required"]);
        $this->setProperties();
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

        $this->updateVisibility();
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
        $this->updateVisibility();
	}
	
	/*
     * updateVisibility
     */
    protected function updateVisibility()
    {
        //TODO: also insert field_order
        global $ilDB;
        $query = "DELETE FROM il_dcl_viewdefinition USING il_dcl_viewdefinition INNER JOIN il_dcl_view view ON view.id = il_dcl_viewdefinition.view_id WHERE view.table_id = ".$this->getTableId()." AND il_dcl_viewdefinition.field LIKE '".$this->getId()."'";
        $ilDB->manipulate($query);
        
        if($this->isVisible())
        {
            $query = "INSERT INTO il_dcl_viewdefinition (view_id, field, field_order) SELECT id, '".$this->getId()."', 0  FROM il_dcl_view WHERE il_dcl_view.table_id = ".$this->getTableId()."";
        }
        
        $ilDB->manipulate($query);
    }
    
    
    /*
     * doDelete
     */
    public function doDelete()
    {
        global $ilDB;

        //trick to delete entries in viewdefinition table
        $this->visible = false;
        $this->updateVisibility();

        $query = "DELETE FROM il_dcl_field WHERE id = ".$this->getId();
        $ilDB->manipulate($query);
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