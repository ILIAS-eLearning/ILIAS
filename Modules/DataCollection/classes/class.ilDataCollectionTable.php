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

class ilDataCollectionTable
{
	protected $id; // [int]
	protected $objId; // [int]
	protected $title; // [string]
    private $fields; // [array][ilDataCollectionField]
    private $records;

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

    function getRecords(){
        $this->loadRecords();
        return $this->records;
    }

    private function loadRecords(){
        if($this->records == Null){
            $records = array();
            global $ilDB;
            $query = "SELECT id FROM il_dlc_records WHERE table_id = ".$this->id;
            $set = $ilDB->query($query);
            while($rec = $ilDB->fetchAssoc($set)){
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

    function deleteField($field_id){
        $field = new ilDataCollectionField($field_id);
        $field->doDelete();

        //TODO: delete records.
    }

    function getField($field_id){
        $fields = $this->getFields();
        return $fields[$field_id];
    }

    function getFieldIds(){
        return array_keys($this->getFields());
    }

    private function loadFields(){
        if($this->fields == NULL){
            global $ilDB;

            $query = "SELECT * FROM il_dcl_field WHERE table_id =".$this->id;
            $fields = array();
            $set = $ilDB->query($query);
            while($rec = $ilDB->fetchAssoc($set)){
                $field = new ilDataCollectionField();
                $field->buildFromDBRecord($rec);
                $fields[$field->getId()] = $field;
            }
            $fields = array_merge($fields, ilDataCollectionStandardField::_getStandardFields($this->id));
            $this->fields = $fields;
        }
    }

    function getFields(){
        $this->loadFields();
        return $this->fields;
    }

    function getVisibleFields(){
        $fields = $this->getFields();
        $visibleFields = array();
        foreach($fields as $field)
            if($field->isVisible())
                array_push($visibleFields, $field);
        return $visibleFields;
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

        $view_id = $ilDB->nextId("il_dcl_view");
        $query = "INSERT INTO il_dcl_view (id, table_id, type, formtype) VALUES (".$view_id.", ".$this->id.", 1, 1)";
        $ilDB->manipulate($query);
	}
}

?>