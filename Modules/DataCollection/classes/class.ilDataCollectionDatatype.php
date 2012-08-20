<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once ("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
include_once ("./Modules/DataCollection/classes/class.ilObjDataCollectionFile.php");
include_once ("class.ilObjDataCollectionFile.php");

include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
include_once("./Services/Form/classes/class.ilTextInputGUI.php");
include_once("./Services/Form/classes/class.ilFileInputGUI.php");
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
	
	/*
	 * checkValidity
	 */
    static function checkValidity($type_id, $value)
    {
        //TODO: finish this list.

		//required is checked by form. so no input is valid.
		if($value == Null)
			return true;
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
     * @return ilCheckboxInputGUI|ilDateTimeInputGUI|ilFileInputGUI|ilTextInputGUI|NULL
     */
    static function getInputField(ilDataCollectionField $field)
    {
        $type_id = $field->getDatatypeId();
        $input = NULL;
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
                $input->setStartYear(date("Y")-100);
                break;
            case ilDataCollectionDatatype::INPUTFORMAT_FILE:
                $input = new ilFileInputGUI($title, 'field_'.$field->getId());
                break;
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				$input = new ilSelectInputGUI($title, 'field_'.$field->getId());
				break;
        }
        return $input;
    }

	static function addFilterInputFieldToTable(ilDataCollectionField $field, ilTable2GUI &$table){
		global $lng;
		$type_id = $field->getDatatypeId();
		$input = null;
		switch($type_id)
		{
			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_NUMBER_RANGE, false, $field->getId());
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_SELECT, false, $field->getId());
				$input->setOptions(array("" => $lng->txt("dcl_any"), 0 => $lng->txt("dcl_not_checked"), 1 => $lng->txt("dcl_checked")));
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_DATE_RANGE, false, $field->getId());
				$input->setStartYear(date("Y")-100);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_FILE:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_SELECT, false, $field->getId());
				$options = array("" => $lng->txt("dcl_any"));
				$ref_field_id = $field->getFieldRef();
				$ref_field = new ilDataCollectionField($ref_field_id);
				$ref_table = new ilDataCollectionTable($ref_field->getTableId());
				foreach($ref_table->getRecords() as $record)
				{
					$options[$record->getId()] = $record->getRecordFieldValue($ref_field_id);
				}
				$input->setOptions($options);
				break;
		}
		if($input != null)
			$input->setTitle($field->getTitle());
		return $input;
	}

	static function passThroughFilter(ilDataCollectionRecord $record,ilDataCollectionField $field, $filter){
		$pass = false;
		$type_id = $field->getDatatypeId();
		$value = $record->getRecordFieldValue($field->getId());
		switch($type_id)
		{
			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				if(!$filter || strpos($value, $filter) !== false)
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
				if((!$filter['from'] || $value >= $filter['from']) && (!$filter['to'] || $value <= $filter['to']))
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
				if($filter == $value || $filter == '' || !$filter)
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
				if((!$filter['from'] || $value >= $filter['from']) && (!$filter['to'] || $value <= $filter['to']))
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_FILE:
				$file_obj = new ilObjFile($value, false);
				$file_name = $file_obj->getTitle();
				if(!$filter || strpos($file_name, $filter) !== false)
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				if(!$filter || $filter == $value)
					$pass = true;
				break;
		}

		if(($field->getId() == "owner" || $field->getId() == "last_edit_by") && $filter){
			$pass = false;
			$user = new ilObjUser($value);
			if(strpos($user->getFullname(), $filter) !== false)
				$pass = true;
		}

		return $pass;
	}


    /**
     * Function to parse incoming data from form input value $value. returns the strin/number/etc. to store in the database.
     * @param $value
     * @return int|string
     */
    public function parseValue($value){
		$return = false;
        if($this->id == ilDataCollectionDatatype::INPUTFORMAT_FILE)
        {
            $file = $value;
            if($file['tmp_name'])
            {
				$file_obj = new ilObjDataCollectionFile();

                $file_obj->setType("file");
                $file_obj->setTitle($file["name"]);
                $file_obj->setFileName($file["name"]);
                $file_obj->setFileType(ilMimeTypeUtil::getMimeType("", $file["name"], $file["type"]));
                $file_obj->setFileSize($file["size"]);
                $file_obj->setMode("object");
                $file_obj->create();
                $file_obj->getUploadFile($file["tmp_name"], $file["name"]);

                $file_id = $file_obj->getId();
				$return = $file_id;
			}
        }elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_DATETIME){
            return $value["date"]." ".$value["time"];
        }elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN){
			$return = $value?1:0;
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
    public function parseHTML($value, ilDataCollectionRecordField $record_field){
        switch($this->id){
            case self::INPUTFORMAT_DATETIME:
                $html = substr($value, 0, -9);
                break;
			case self::INPUTFORMAT_FILE:
				global $ilCtrl;
				$file_obj = new ilObjDataCollectionFile($value,false);
				$ilCtrl->setParameterByClass("ildatacollectionrecordlistgui", "record_id", $record_field->getRecord()->getId());
				$ilCtrl->setParameterByClass("ildatacollectionrecordlistgui", "field_id", $record_field->getField()->getId());

				//ilUtil::deliverFile($file_obj->getFile(), $file_obj->getTitle());
				$html = "<a href=".$ilCtrl->getLinkTargetByClass("ildatacollectionrecordlistgui","sendFile")." >".$file_obj->getFileName()."</a>";
				break;
			case self::INPUTFORMAT_BOOLEAN:
				switch($value)
				{
					case 0:
						$im = ilUtil::getImagePath('icon_not_ok.png');
						break;
					case 1:
						$im = ilUtil::getImagePath('icon_ok.png');
						break;
				}
				$html = "<img src='".$im."'>";
				break;
			case IlDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				if(!$value || $value == "-"){
					$html = "";
					break;
				}

				$record = new ilDataCollectionRecord($value);
				if(!$record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()){
					//the referenced record_field does not seem to exist.
					$html = "-";
					$record_field->setValue(null);
					$record_field->doUpdate();
				}
				else
					$html = $record->getRecordFieldHTML($record_field->getField()->getFieldRef());
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
				if(!$value || $value = "-")
					return null;
                //$datetime = new DateTime();
                $input = array( "date" => substr($value, 0, -9),
                                "time" => "00:00:00");
                break;
			case self::INPUTFORMAT_FILE:
				$file_obj = new ilObjFile($value, false);
				//$input = ilObjFile::_lookupAbsolutePath($value);
				$input = $file_obj->getFile();
				break;
            default:
                $input = $value;
        }
        return $input;
    }
}

?>