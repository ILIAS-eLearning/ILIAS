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

include_once './Modules/DataCollection/classes/class.ilDataCollectionStandardField.php';
include_once './Modules/DataCollection/classes/class.ilDataCollectionRecord.php';

class ilDataCollectionTable
{
	protected $id; // [int]
	protected $objId; // [int]
	protected $obj;
	protected $title; // [string]
    private $fields; // [array][ilDataCollectionField]
	private $stdFields;
    private $records;
	private $blocked; //[bool]
	/**
	 * @var bool
	 */
	private $add_perm;

	/**
	 * @var bool
	 */
	private $edit_perm;
	/**
	 * @var bool
	 */
	private $delete_perm;
	/**
	 * @var bool
	 */
	private $edit_by_owner;

	/**
	 * @var bool
	 */
	private $limited;
	/**
	 * @var string
	 */
	private $limit_start;
	/**
	 * @var string
	 */
	private $limit_end;

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
	
	/*
	 * getCollectionObject
	 * @return object
	 */
	public function getCollectionObject()
	{
		$this->loadObj();
		
		return $this->obj;
	}
	
	/*
	 * loadObj
	 */
	private function loadObj()
	{
		if($this->obj == null)
		{
			$this->obj = new ilObjDataCollection($this->objId, false);
		}
	}
	
	/*
	 * getRecords
	 */
    function getRecords()
    {
        $this->loadRecords();
        
        return $this->records;
    }

	/**
	 * @param $filter filter is of the form array("filter_{field_id}" => filter); For dates and integers this filter must be of the form array("from" => from, "to" => to). In case of dates from and to have to be ilDateTime objects in case of integers they have to be integers as well.
	 */
	function getRecordsByFilter($filter){
		$this->loadRecords();
		$filtered = array();
		foreach($this->records as $record)
			if($record->passThroughFilter($filter?$filter:array()))
				array_push($filtered, $record);
		return $filtered;
	}

	function getRecordsWithFilter(array $filter){

	}

    /*
     * loadRecords
     */
    private function loadRecords()
    {
        if($this->records == NULL)
        {
            $records = array();
            global $ilDB;
            $query = "SELECT id FROM il_dcl_record WHERE table_id = ".$this->id;
            $set = $ilDB->query($query);
            
            while($rec = $ilDB->fetchAssoc($set))
            {
                $records[$rec['id']] = new ilDataCollectionRecord($rec['id']);
            }
            $this->records = $records;
        }
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
		$this->setBlocked($rec["blocked"]);
		$this->setAddPerm($rec["add_perm"]);
		$this->setEditPerm($rec["edit_perm"]);
		$this->setDeletePerm($rec["delete_perm"]);
		$this->setEditByOwner($rec["edit_by_owner"]);
		$this->setLimited($rec["limited"]);
		$this->setLimitStart($rec["limit_start"]);
		$this->setLimitEnd($rec["limit_end"]);
	}

    //TODO: replace this method with DataCollection->getTables()
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
	
	
	/*
	 * deleteField
	 */
    function deleteField($field_id)
    {
        $field = new ilDataCollectionField($field_id);
        $field->doDelete();
        $records = $this->getRecords();
        
        foreach($records as $record)
        {
            $record->deleteField($field_id);
        }
    }
    
    
    /*
     * getField
     */
    function getField($field_id)
    {
        $fields = $this->getFields();
        
        return $fields[$field_id];
    }
    
    /*
     * getFieldIds
     */
    function getFieldIds()
    {
        return array_keys($this->getFields());
    }
    
    /*
     * loadFields
     */
    private function loadFields()
    {
        if($this->fields == NULL)
        {
            global $ilDB;
            $query = "SELECT * FROM il_dcl_field WHERE table_id =".$this->getId();
            $fields = array();
            $set = $ilDB->query($query);
            
            while($rec = $ilDB->fetchAssoc($set))
            {
                $field = new ilDataCollectionField();
                $field->buildFromDBRecord($rec);
                $fields[$field->getId()] = $field;
            }


            $this->fields = $fields;
        }
    }

    /**
     * Returns all fields of this table including the standard fields
     * @return array ilDataCollectionField
     */
    function getFields()
    {
        $this->loadFields();
		if($this->stdFields == Null)
			$this->stdFields = ilDataCollectionStandardField::_getStandardFields($this->id);
        $fields = array_merge($this->fields, $this->stdFields);


		$this->sortFields($fields);
        return $fields;
    }

    /**
     * Returns all fields of this table which are NOT standard fields.
     * @return mixed
     */
    function getRecordFields()
    {
        $this->loadFields();
        
        return $this->fields;
    }

    /**
     * Returns all fields of this table who have set their visibility to true, including standard fields.
     * @return array
     */
    function getVisibleFields()
    {
        $fields = $this->getFields();
        $visibleFields = array();
        
        foreach($fields as $field)
        {
	        if($field->isVisible())
	        {
		        array_push($visibleFields, $field);
	        }
        }
            
        return $visibleFields;
    }

	function getEditableFields(){
		$fields = $this->getRecordFields();
		$editableFields = array();

		foreach($fields as $field)
		{
			if(!$field->getLocked())
			{
				array_push($editableFields, $field);
			}
		}

		return $editableFields;
	}

	 /**
     * Returns all fields of this table who have set their filterable to true, including standard fields.
     * @return array
     */
    function getFilterableFields()
    {
        $fields = $this->getFields();
        $filterableFields = array();
        
        foreach($fields as $field)
        {
	        if($field->isFilterable())
	        {
		        array_push($filterableFields, $field);
	        }
        }
            
        return $filterableFields;
    }

	function hasPermissionToFields($ref_id){
		return ilObjDataCollection::_hasWriteAccess($ref_id);
	}

	function hasPermissionToAddTable($ref_id) {
		return ilObjDataCollection::_hasWriteAccess($ref_id);
	}

	/**
	 * Attention this does not delete the maintable of it's the maintabla of the collection. unlink the the maintable in the collections object to make this work.
	 */
	public function doDelete(){
		global $ilDB;

		foreach($this->getRecords() as $record)
			$record->doDelete();
		foreach($this->getRecordFields() as $field)
			$field->doDelete();
		if($this->getCollectionObject()->getMainTableId() != $this->getId()){
			$query = "DELETE FROM il_dcl_table WHERE id = ".$this->getId();
			$ilDB->manipulate($query);
		}
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
		", blocked".
		", add_perm".
		", edit_perm".
		", delete_perm".
		", edit_by_owner".
		", limited".
		", limit_start".
		", limit_end".
		" ) VALUES (".
		$ilDB->quote($this->getId(), "integer")
		.",".$ilDB->quote($this->getObjId(), "integer")
		.",".$ilDB->quote($this->getTitle(), "text")
		.",".$ilDB->quote($this->isBlocked()?1:0, "integer")
		.",".$ilDB->quote($this->getAddPerm()?1:0, "integer")
		.",".$ilDB->quote($this->getEditPerm()?1:0, "integer")
		.",".$ilDB->quote($this->getDeletePerm()?1:0, "integer")
		.",".$ilDB->quote($this->getEditByOwner()?1:0, "integer")
		.",".$ilDB->quote($this->getLimited()?1:0, "integer")
		.",".$ilDB->quote($this->getLimitStart(), "datetime")
		.",".$ilDB->quote($this->getLimitEnd(), "datetime")
		.")";
		$ilDB->manipulate($query);

		//FIXME
		//FromType sollen ebenfalls als Konstante definiert werden.

		//add view definition
        $view_id = $ilDB->nextId("il_dcl_view");
        $query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (".$view_id.", ".$this->id.", ".ilDataCollectionField::VIEW_VIEW.", 1)";
        $ilDB->manipulate($query);

		//add edit definition
		$view_id = $ilDB->nextId("il_dcl_view");
		$query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (".$view_id.", ".$this->id.", ".ilDataCollectionField::EDIT_VIEW.", 1)";
		$ilDB->manipulate($query);

		//add filter definition
		$view_id = $ilDB->nextId("il_dcl_view");
		$query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (".$view_id.", ".$this->id.", ".ilDataCollectionField::FILTER_VIEW.", 1)";
		$ilDB->manipulate($query);
	}

	function doUpdate(){
		global $ilDB;

		$ilDB->update("il_dcl_table", array(
			"obj_id" => array("integer", $this->getObjId()),
			"title" => array("text", $this->getTitle()),
			"blocked" => array("integer",$this->isBlocked()),
			"add_perm" => array("integer",$this->getAddPerm()),
			"edit_perm" => array("integer",$this->getEditPerm()),
			"delete_perm" => array("integer",$this->getDeletePerm()),
			"edit_by_owner" => array("integer",$this->getEditByOwner()),
			"limited" => array("integer",$this->getLimited()),
			"limit_start" => array("date",$this->getLimitStart()),
			"limit_end" => array("date",$this->getLimitEnd())
		), array(
			"id" => array("integer", $this->getId())
		));
	}

	public function hasPermissionToAddRecord($ref){
		return ($this->getAddPerm() && ilObjDataCollection::_hasReadAccess($ref) && $this->checkLimit()) || ilObjDataCollection::_hasWriteAccess($ref);
	}

	/**
	 * @param $ref int the reference id of the current datacollection object
	 * @param $record ilDataCollectionRecord the record which will be edited
	 * @return bool
	 */
	public function hasPermissionToEditRecord($ref, $record){
		return ($this->getEditPerm() && ilObjDataCollection::_hasReadAccess($ref) && $this->checkEditByOwner($record) && $this->checkLimit())  || ilObjDataCollection::_hasWriteAccess($ref);
	}

	/**
	 * @param $ref int the reference id of the current datacollection object
	 * @param $record ilDataCollectionRecord the record which will be deleted
	 * @return bool
	 */
	public function hasPermissionToDeleteRecord($ref, $record){
		return ($this->getDeletePerm() && ilObjDataCollection::_hasReadAccess($ref) && $this->checkEditByOwner($record) && $this->checkLimit())  || ilObjDataCollection::_hasWriteAccess($ref);
	}

	private function checkEditByOwner($record){
		global $ilUser;
		if($this->getEditByOwner() && $ilUser->getId() != $record->getOwner())
			return false;
		return true;
	}

	private function checkLimit(){
		if($this->getLimited()){
			$now = new ilDateTime(time(), IL_CAL_UNIX);
			$from = new ilDateTime($this->getLimitStart(), IL_CAL_DATE);
			$to = new ilDateTime($this->getLimitEnd(), IL_CAL_DATE);
			if(!($from <= $now && $now <= $to))
				return false;
		}
		return true;
	}

	public function updateFields(){
		foreach($this->getFields() as $field)
			$field->doUpdate();
	}

	public function isBlocked(){
		return $this->blocked;
	}

	public function setBlocked($blocked){
		$this->blocked = $blocked?1:0;
	}

	public function toggleBlocked(){
		$this->setBlocked(!$this->isBlocked());
	}

	private function sortFields(&$fields){
		$this->sortByMethod($fields, "getOrder");
	}

	private function sortByMethod(&$array, $method_name){
		usort($array, function($a, $b) use ($method_name){
			if(is_null($a->$method_name() == Null) && is_null($b->$method_name() == Null))
				return 0;
			if(is_null($a->$method_name()))
				return 1;
			if(is_null($b->$method_name()))
				return -1;
			return $a->$method_name() < $b->$method_name() ? -1 : 1;
		});
	}

	/**
	 * orders the fields.
	 */
	public function buildOrderFields(){
		$fields = $this->getFields();

		$this->sortByMethod($fields, "getOrder");

		$count = 10;
		$offset = 10;

		foreach($fields as $field){
			if(!is_null($field->getOrder())){
				$field->setOrder($count);
				$count = $count + $offset;
			}
		}
	}

	/**
	 * @param boolean $add_perm
	 */
	public function setAddPerm($add_perm)
	{
		$this->add_perm = $add_perm;
	}

	/**
	 * @return boolean
	 */
	public function getAddPerm()
	{
		return $this->add_perm;
	}

	/**
	 * @param boolean $delete_perm
	 */
	public function setDeletePerm($delete_perm)
	{
		$this->delete_perm = $delete_perm;
	}

	/**
	 * @return boolean
	 */
	public function getDeletePerm()
	{
		return $this->delete_perm;
	}

	/**
	 * @param boolean $edit_by_owner
	 */
	public function setEditByOwner($edit_by_owner)
	{
		$this->edit_by_owner = $edit_by_owner;
	}

	/**
	 * @return boolean
	 */
	public function getEditByOwner()
	{
		return $this->edit_by_owner;
	}

	/**
	 * @param boolean $edit_perm
	 */
	public function setEditPerm($edit_perm)
	{
		$this->edit_perm = $edit_perm;
	}

	/**
	 * @return boolean
	 */
	public function getEditPerm()
	{
		return $this->edit_perm;
	}

	/**
	 * @param boolean $limited
	 */
	public function setLimited($limited)
	{
		$this->limited = $limited;
	}

	/**
	 * @return boolean
	 */
	public function getLimited()
	{
		return $this->limited;
	}

	/**
	 * @param string $limit_end
	 */
	public function setLimitEnd($limit_end)
	{
		$this->limit_end = $limit_end;
	}

	/**
	 * @return string
	 */
	public function getLimitEnd()
	{
		return $this->limit_end;
	}

	/**
	 * @param string $limit_start
	 */
	public function setLimitStart($limit_start)
	{
		$this->limit_start = $limit_start;
	}

	/**
	 * @return string
	 */
	public function getLimitStart()
	{
		return $this->limit_start;
	}
}

?>