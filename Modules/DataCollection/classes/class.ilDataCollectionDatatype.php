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
    protected $dbType;

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

    function getDbType(){
        return $this->dbType;
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
        $this->dbType = $rec["ildb_type"];
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

    static function checkValidity($type_id, $value){
        //TODO: finish this list.
        switch($type_id){
            case self::INPUTFORMAT_NUMBER:
                $return = is_numeric($value);
                break;
            default:
                $return = true;
                break;
        }
        return $return;
    }

    /**
     * @param $type_id
     * @param ilDataCollectionField $field
     * @return ilCheckboxInputGUI|ilDateTimeInputGUI|ilFileInputGUI|ilTextInputGUI|null
     */
    static function getInputField(ilDataCollectionField $field){
        $type_id = $field->getDatatypeId();
        $input = Null;
        $title = $field->getTitle();
        switch($type_id)
        {
            case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
                $input = new ilTextInputGUI($title, 'field_'.$field->getId());
                break;

            case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
                $input = new ilTextInputGUI($title, 'field_'.$field->getId());
                break;
            case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
                $input = new ilCheckboxInputGUI($title, 'field_'.$field->getId());
                break;
            case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
                $input = new ilDateTimeInputGUI($title, 'field_'.$field->getId());
                break;
            case ilDataCollectionDatatype::INPUTFORMAT_FILE:
                $input = new ilFileInputGUI($title, 'field_'.$field->getId());
                break;
        }
        return $input;
    }


    /**
     * Function to parse incoming data from form input value $value. returns the strin/number/etc. to store in the database.
     * @param $value
     * @return int|string
     */
    public function parseValue($value){
        global $ilLog;
        if($this->id == ilDataCollectionDatatype::INPUTFORMAT_FILE)
        {
            $file = $value;
            if($file['tmp_name'])
            {
                include("class.ilObjDataCollectionFile.php");
                $file_obj = new ilObjDataCollectionFile();

                $file_obj->setType("file");
                $file_obj->setTitle($file["name"]);
                $file_obj->setFileName($file["name"]);
                include_once ("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
                $file_obj->setFileType(ilMimeTypeUtil::getMimeType("", $file["name"], $file["type"]));
                $file_obj->setFileSize($file["size"]);
                $file_obj->setMode("object");
                $file_obj->create();
                $file_obj->getUploadFile($file["tmp_name"], $file["name"]);

                $file_id = $file_obj->getId();

            }
            $return = $file_id;
        }elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_DATETIME){
            return $value["date"]." ".$value["time"];
        }
        else{
            $return = $value;
        }
        return $return;
    }


    /**
     * function parses stored value in database to a html output for eg. the record list gui.
     * @param $value
     * @return mixed
     */
    public function parseHTML($value){
        switch($this->id){
            case self::INPUTFORMAT_DATETIME:
                $html = $value;
                break;
            default:
                $html = $value;
        }
        return $html;
    }


    /**
     * function parses stored value to the variable needed to fill into the form for editing.
     * @param $value
     * @return mixed
     */
    public function parseFormInput($value){
        switch($this->id){
            case self::INPUTFORMAT_DATETIME:
                //$datetime = new DateTime();
                $input = array( "date" => array("d" => "17", "m" => "2", "y" => "1990"),
                                /*"time" => array("h" => "10", "i" => "00", "s" => "00")*/);
                break;
            default:
                $input = $value;
        }
        return $input;
    }
}

?>