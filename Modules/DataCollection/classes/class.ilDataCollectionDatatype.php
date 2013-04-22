<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once "./Services/Utilities/classes/class.ilMimeTypeUtil.php";
require_once "class.ilDataCollectionTreePickInputGUI.php";
require_once "class.ilDataCollectionCache.php";
require_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
require_once "./Modules/File/classes/class.ilObjFile.php";
require_once "./Services/Form/classes/class.ilSelectInputGUI.php";
require_once "./Services/Form/classes/class.ilDateTimeInputGUI.php";
require_once "./Services/Form/classes/class.ilTextInputGUI.php";
require_once "./Services/Form/classes/class.ilFileInputGUI.php";


/**
* Class ilDataCollectionDatatype
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @author Oskar Truffer <ot@studer-raimann.ch>
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
	// Rating
	const INPUTFORMAT_RATING 		= 7;
	// ILIAS REFERENCE
	const INPUTFORMAT_ILIAS_REF 	= 8;
    // Meida Object
    const INPUTFORMAT_MOB 		    = 9;

    const LINK_MAX_LENGTH = 30;


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
	public function getId()
	{
		return $this->id;
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
	 * Set Storage Location
	 *
	 * @param int $a_id
	 */
	public function setStorageLocation($a_id)
	{
		$this->storageLocation = $a_id;
	}

	/**
	 * Get Storage Location
	 *
	 * @return int
	 */
	public function getStorageLocation()
	{
		return $this->storageLocation;
	}
	
	/*
	 * getDbType
	 */
	public function getDbType()
	{
		return $this->dbType;
	}

	/**
	 * Read Datatype
	 */
	public function doRead()
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
	public function getProperties($a_id)
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

	/**
	 * now only distinguishes between number and text values
	 * @param $type_id int
	 * @param $value mixed
	 * @return bool
	 */
	static function checkValidity($type_id, $value)
	{
		//required is checked by form. so null input is valid.
		if($value == NULL)
		{
			return true;
		}
			
		switch($type_id)
		{
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
		global $lng;
		$type_id = $field->getDatatypeId();
		$title = $field->getTitle();
		switch($type_id)
		{
			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				$input = new ilTextInputGUI($title, 'field_'.$field->getId());
				if($field->getTextArea())
					$input = new ilTextAreaInputGUI($title, 'field_'.$field->getId());
				if($field->getLength())
					$input->setInfo($lng->txt("dcl_max_text_length").": ".$field->getLength());
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
			case ilDataCollectionDatatype::INPUTFORMAT_RATING:
				$input = new ilTextInputGUI($title, 'field_'.$field->getId());
				$input->setValue($lng->txt("dcl_editable_in_table_gui"));
				$input->setDisabled(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
				$input = new ilDataCollectionTreePickInputGUI($title, 'field_'.$field->getId());
				break;
            case ilDataCollectionDatatype::INPUTFORMAT_MOB:
                $input = new ilFileInputGUI($title, 'field_'.$field->getId());
                break;
		}
        if($field->getDescription())
            $input->setInfo($field->getDescription().($input->getInfo()?"<br>".$input->getInfo():""));
		return $input;
	}
	
	/**
	 * addFilterInputFieldToTable This function adds the according filter item to the table gui passed as argument.
	 * @param $field ilDataCollectionField The field which should be filterable.
	 * @param &$table ilTable2GUI The table you want the filter to be added to.
	 */
	static function addFilterInputFieldToTable(ilDataCollectionField $field, ilTable2GUI &$table)
	{
		global $lng;
		
		$type_id = $field->getDatatypeId();
		$input = NULL;
		
		switch($type_id)
		{
			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_NUMBER_RANGE, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_SELECT, false, $field->getId());
				$input->setOptions(array("" => $lng->txt("dcl_any"), "not_checked" => $lng->txt("dcl_not_checked"), "checked" => $lng->txt("dcl_checked")));
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_DATE_RANGE, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				$input->setStartYear(date("Y")-100);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_FILE:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_SELECT, false, $field->getId());
				$options = array("" => $lng->txt("dcl_any"));
				$ref_field_id = $field->getFieldRef();
				$ref_field = ilDataCollectionCache::getFieldCache($ref_field_id);
				$ref_table = ilDataCollectionCache::getTableCache($ref_field->getTableId());
				foreach($ref_table->getRecords() as $record)
				{
					$options[$record->getId()] = $record->getRecordFieldValue($ref_field_id);
				}
				$input->setOptions($options);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_RATING:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_SELECT, false, $field->getId());
				$options = array("" => $lng->txt("dcl_any"), 1 => ">1", 2 => ">2", 3 => ">3", 4 => ">4", 5 => "5");
				$input->setOptions($options);
				break;
            case ilDataCollectionDatatype::INPUTFORMAT_MOB:
                $input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
                $input->setSubmitFormOnEnter(true);
                break;
			case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
				$input = $table->addFilterItemByMetaType("filter_".$field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				break;
		}
		
		if($input != NULL)
		{
			$input->setTitle($field->getTitle());
		}
			
		return $input;
	}
	
	/*
	 * passThroughFilter
	 */
	static function passThroughFilter(ilDataCollectionRecord $record,ilDataCollectionField $field, $filter)
	{
		$pass = false;
		$type_id = $field->getDatatypeId();
		$value = $record->getRecordFieldValue($field->getId());
		
		switch($type_id)
		{
			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				if(!$filter || strpos(strtolower($value), strtolower($filter)) !== false)
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
				if((!$filter['from'] || $value >= $filter['from']) && (!$filter['to'] || $value <= $filter['to']))
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
				if((($filter == "checked" && $value == 1) || ($filter == "not_checked" && $value == 0))|| $filter == '' || !$filter)
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
				if((!$filter['from'] || $value >= $filter['from']) && (!$filter['to'] || $value <= $filter['to']))
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_FILE:
                if(!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {

                    $pass = true;
                    break;
                }

                    $file_obj = new ilObjFile($value, false);
                    $file_name = $file_obj->getTitle();
                    if(!$filter || strpos(strtolower($file_name), strtolower($filter)) !== false)
                        $pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				if(!$filter || $filter == $value)
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_RATING:
				if(!$filter || $filter <= $value['avg'])
					$pass = true;
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
				$obj_id = ilObject::_lookupObjId($value);
				if(!$filter || strpos(strtolower(ilObject::_lookupTitle($obj_id)), strtolower($filter)) !== false)
					$pass = true;
				break;
            case ilDataCollectionDatatype::INPUTFORMAT_MOB:
                $m_obj = new ilObjMediaObject($value, false);
                $file_name = $m_obj->getTitle();
                if(!$filter || strpos(strtolower($file_name), strtolower($filter)) !== false)
                    $pass = true;
                break;
		}

		//for the fields owner and last edit by, we check the name, not the ID
		if(($field->getId() == "owner" || $field->getId() == "last_edit_by") && $filter)
		{
			$pass = false;
			$user = new ilObjUser($value);
			if(strpos($user->getFullname(), $filter) !== false)
			{
				$pass = true;
			}
		}

		return $pass;
	}


	/**
	 * Function to parse incoming data from form input value $value. returns the strin/number/etc. to store in the database.
	 * @param $value
     * @param ilDataCollectionRecordField $record_field
	 * @return int|string
	 */
	public function parseValue($value,ilDataCollectionRecordField $record_field)
	{
		$return = false;
		
		if($this->id == ilDataCollectionDatatype::INPUTFORMAT_FILE)
		{
			$file = $value;

			if($file['tmp_name'])
			{
				$file_obj = new ilObjFile();

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
			}else
                $return = $record_field->getValue();
			}
        elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_MOB)
        {
            $media = $value;
            if($media['tmp_name'])
            {
                $mob = new ilObjMediaObject();
                $mob->setTitle($media['name']);
                $mob->create();

                $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
                if (!is_dir($mob_dir))
                    $mob->createDirectory();

                $media_item = new ilMediaItem();
                $mob->addMediaItem($media_item);
                $media_item->setPurpose("Standard");


                $file_name = ilUtil::getASCIIFilename($media['name']);
                $file_name = str_replace(" ", "_", $file_name);

                $file = $mob_dir."/".$file_name;
                $title = $file_name;

                ilUtil::moveUploadedFile($media['tmp_name'], $file_name, $file);
                ilUtil::renameExecutables($mob_dir);

                list($width, $height, $type, $attr) = getimagesize($file);

                $arr_properties = $record_field->getField()->getProperties();
                $new_width = $arr_properties[ilDataCollectionField::PROPERTYID_WIDTH];
                $new_height = $arr_properties[ilDataCollectionField::PROPERTYID_HEIGHT];
                if($new_width || $new_height)
                {
                    //only resize if it is bigger, not if it is smaller
                    if($new_height < $height && $new_width < $width)
                    $location = ilObjMediaObject::_resizeImage($file, (int) $arr_properties[ilDataCollectionField::PROPERTYID_WIDTH],
                        (int) $arr_properties[ilDataCollectionField::PROPERTYID_HEIGHT], true);
                } else {
                    $location = $title;
                }

                $format = ilObjMediaObject::getMimeType($file);
                $media_item->setFormat($format);
                $media_item->setLocation($location);
                $media_item->setLocationType("LocalFile");

                $mob->update();
                $return = $mob->getId();
            }else
                $return = $record_field->getValue();
            }
		elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_DATETIME)
		{
			return $value["date"]." ".$value["time"];
		}
		elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN)
		{
			$return = $value ? 1 : 0;
		}elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_TEXT){
            $arr_properties = $record_field->getField()->getProperties();
            if($arr_properties[ilDataCollectionField::PROPERTYID_TEXTAREA])
                $return = nl2br($value);
            else
                $return = $value;
        }
		else
		{
			$return = $value;
		}
		return $return;
	}
	
	
	/**
	 * Function to parse incoming data from form input value $value. returns the strin/number/etc. to store in the database.
	 * @param $value
	 * @return int|string
	 */
	public function parseExportValue($value)
	{
		$return = false;

		if($this->id == ilDataCollectionDatatype::INPUTFORMAT_FILE)
		{
            if(!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
                return;
            }



			$file = $value;
			if($file!="-")
			{
				$file_obj = new ilObjFile($file, false);
				$file_name = $file_obj->getFileName();
				
				$return = $file_name;
			}
			else
			{
				$return = $file;
			}
		}
        elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_MOB)
        {
            $file = $value;
            if($file!="-")
            {
                $mob = new ilObjMediaObject($file, false);
                $mob_name = $mob->getTitle();

                $return = $mob_name;
            }
            else
            {
                $return = $file;
            }
        }
		elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_DATETIME)
		{
			$return = substr($value, 0, 10);
		}
		elseif($this->id == ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN)
		{
			$return = $value ? 1 : 0;
        }
        else
        {
           $return = $value;
        }
		return $return;
	}

	/**
	 * function parses stored value in database to a html output for eg. the record list gui.
	 * @param $value
	 * @return mixed
	 */
	public function parseHTML($value, ilDataCollectionRecordField $record_field)
	{
		switch($this->id)
		{
			case self::INPUTFORMAT_DATETIME:
				$html = substr($value, 0, -9);
				break;
				
			case self::INPUTFORMAT_FILE:
				global $ilCtrl;

                 if(!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
                    $html = "-";
                    break;
                }

				$file_obj = new ilObjFile($value,false);

				$ilCtrl->setParameterByClass("ildatacollectionrecordlistgui", "record_id", $record_field->getRecord()->getId());
				$ilCtrl->setParameterByClass("ildatacollectionrecordlistgui", "field_id", $record_field->getField()->getId());

				$html = "<a href=".$ilCtrl->getLinkTargetByClass("ildatacollectionrecordlistgui", "sendFile")." >".$file_obj->getFileName()."</a>";
				break;

            case self::INPUTFORMAT_MOB:

                $mob = new ilObjMediaObject($value,false);
                $dir  = ilObjMediaObject::_getDirectory($mob->getId());
                $media_item = $mob->getMediaItem('Standard');
                if(!$media_item->location) {
                    $html = "";
                    break;
                }
                $html = '<img src="'.$dir."/".$media_item->location.'" />';
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

				
			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				//Property URL

				$arr_properties = $record_field->getField()->getProperties();
                if($arr_properties[ilDataCollectionField::PROPERTYID_URL])
				{
                    $link = $value;
                    if (preg_match("/^[a-z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i", $value))
                        $value = "mailto:".$value;
                    elseif(!(preg_match('~(^(news|(ht|f)tp(s?)\://){1}\S+)~i', $value)))
                        return $link;

                    if(strlen($link) > self::LINK_MAX_LENGTH){
                        $link = substr($value, 0, (self::LINK_MAX_LENGTH-3)/2);
                        $link.= "...";
                        $link .= substr($value, -(self::LINK_MAX_LENGTH-3)/2);
                    }
					$html = "<a target='_blank' href='".$value."'>".$link."</a>";
				}
				else
				{
					$html = $value;
				}
				// BEGIN EASTEREGG
				/*if(strtolower($value) == "nyan it plx!"){
					$link = ilLink::_getLink($_GET['ref_id']);
					$html = "<a href='http://nyanit.com/".$link."'>Data Collections rock!</a>";
				}*/
				// END EASTEREGG
				break;
				
			default:
				$html = $value;
				break;
		}
		return $html;
	}


	/**
	 * function parses stored value to the variable needed to fill into the form for editing.
	 * @param $value
	 * @return mixed
	 */
	public function parseFormInput($value, ilDataCollectionRecordField $record_field){
		switch($this->id)
		{
			case self::INPUTFORMAT_DATETIME:
				if(!$value || $value == "-")
					return NULL;
				//$datetime = new DateTime();
				$input = array( "date" => substr($value, 0, -9),
								"time" => "00:00:00");
				break;
			case self::INPUTFORMAT_FILE:

                 if(!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
                    $input = "";
                    break;
                }

                $file_obj = new ilObjFile($value, false);
				//$input = ilObjFile::_lookupAbsolutePath($value);
				$input = $file_obj->getFileName();
				break;
            case self::INPUTFORMAT_MOB:
                if(!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "mob") {
                    $input = "";
                    break;
                }

                $media_obj = new ilObjMediaObject($value, false);
                //$input = ilObjFile::_lookupAbsolutePath($value);
                $input = $media_obj->getTitle();
                break;
            case self::INPUTFORMAT_TEXT:
                $arr_properties = $record_field->getField()->getProperties();
                if($arr_properties[ilDataCollectionField::PROPERTYID_TEXTAREA]){
                    $breaks = array("<br />","<br>","<br/>");
                    $input = str_ireplace($breaks, "\r\n", $value);
                }
                else
                    $input= $value;
                break;
			default:
				$input = $value;
		}
		return $input;
	}
}

?>