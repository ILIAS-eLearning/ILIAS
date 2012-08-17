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
     * @var ilDataCollectionDatatype This fields Datatype.
     */
    protected $datatype;

	const PROPERTYID_LENGTH = 1;
	const PROPERTYID_REGEX = 2;
	const PROPERTYID_REFERENCE = 3;



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

	function isUnique(){
		return $this->unique;
	}

	function setUnique($unique){
		$this->unique = $unique?1:0;
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
		if($visible == true && $this->order === NULL)
			$this->setOrder(0);
        $this->visible = $visible;
    }

	/**
     * setFilterable
     * @param $filterable bool
     */
    function setFilterable($filterable)
    {
		if($filterable == true && $this->order === NULL)
			$this->setOrder(0);
			$this->filterable = $filterable;
    }
    
    /*
     * getDatatype
     */
    function getDatatype()
    {
        $this->loadDatatype();
        
        return $this->datatype;
    }

	function getLength(){
		$props = $this->getPropertyvalues();
		$l = self::PROPERTYID_LENGTH;
		return $props[$l];
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
		if(!isset($this->visible))
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
			$this->loadViewDefinition(self::VIEW_VIEW);
        }
    }

	/*
     * isFilterable
     */
    public function isFilterable()
    {
		if(!isset($this->filterable))
        	$this->loadFilterability;
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
	 * @param $view use VIEW_VIEW or EDIT_VIEW
	 */
	private function loadViewDefinition($view){
		global $ilDB;
		$query = "  SELECT view.table_id, def.field_order, def.is_set FROM il_dcl_viewdefinition def
                        INNER JOIN il_dcl_view view ON view.id = def.view_id AND view.type = ".$view."
                        WHERE def.field LIKE '".$this->id."' AND view.table_id = ".$this->table_id;
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);
		$prop = $rec['is_set'];
		switch($view){
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
			$this->loadEditability();
		
		return $this->editable;
	}

	public function setEditable($editable){
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
		$this->setUnique($rec["is_unique"]);

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
        $this->setUnique($rec["is_unique"]);
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
		", is_unique".
		" ) VALUES (".
		$ilDB->quote($this->getId(), "integer")
		.",".$ilDB->quote($this->getTableId(), "integer")
		.",".$ilDB->quote($this->getDatatypeId(), "integer")
		.",".$ilDB->quote($this->getTitle(), "text")
		.",".$ilDB->quote($this->getDescription(), "text")
		.",".$ilDB->quote($this->getRequired(), "integer")
		.",".$ilDB->quote($this->isUnique(), "integer")
		.")";
		$ilDB->manipulate($query);

        $this->updateVisibility();
		$this->updateFilterability();
		$this->updateEditability();
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
								"required" => array("integer",$this->getRequired()),
								"is_unique" => array("integer",$this->isUnique())
								), array(
								"id" => array("integer", $this->getId())
								));
        $this->updateVisibility();
		$this->updateFilterability();
		$this->updateEditability();
	}
	
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
	 * @param $view use constant VIEW_VIEW or EDIT_VIEW
	 */
    private function updateViewDefinition($view){
		global $ilDB;

		switch($view){
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


		$query = "DELETE def FROM il_dcl_viewdefinition def INNER JOIN il_dcl_view ON il_dcl_view.type = ".$view." AND il_dcl_view.table_id = ".$this->getTableId()." WHERE def.view_id = il_dcl_view.id AND def.field = '".$this->getId()."'";
		$ilDB->manipulate($query);
		$query = "INSERT INTO il_dcl_viewdefinition (view_id, field, field_order, is_set) SELECT id, '".$this->getId()."', ".$this->getOrder().", ".$set."  FROM il_dcl_view WHERE il_dcl_view.type = ".$view." AND il_dcl_view.table_id = ".$this->getTableId();
		$ilDB->manipulate($query);
	}

	private function deleteViewDefinition($view){
		global $ilDB;
		$query = "DELETE def FROM il_dcl_viewdefinition def INNER JOIN il_dcl_view ON il_dcl_view.type = ".$view." AND il_dcl_view.table_id = ".$this->getTableId()." WHERE def.view_id = il_dcl_view.id";
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

	public function getOrder(){
		if(!isset($this->order))
			$this->loadVisibility();
		return !$this->order?0:$this->order;
	}

	public function setOrder($order){
		$this->order = $order;
	}

	public function getFieldRef(){
		$props = $this->getPropertyvalues();
		$id = self::PROPERTYID_REFERENCE;
		return $props[$id];
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