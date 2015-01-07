<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
require_once("class.ilDataCollectionTreePickInputGUI.php");
require_once("class.ilDataCollectionCache.php");
require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
require_once("./Modules/File/classes/class.ilObjFile.php");
require_once("./Services/Form/classes/class.ilSelectInputGUI.php");
require_once("./Services/Form/classes/class.ilMultiSelectInputGUI.php");
require_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
require_once("./Services/Form/classes/class.ilTextInputGUI.php");
require_once("./Services/Form/classes/class.ilFileInputGUI.php");
require_once("./Services/Form/classes/class.ilImageFileInputGUI.php");
require_once("./Services/Preview/classes/class.ilPreview.php");
require_once('./Services/Preview/classes/class.ilPreviewGUI.php');
require_once('class.ilDataCollectionRecordViewViewdefinition.php');
require_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");

/**
 * Class ilDataCollectionDatatype
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionDatatype {

	const INPUTFORMAT_NONE = 0;
	const INPUTFORMAT_NUMBER = 1;
	const INPUTFORMAT_TEXT = 2;
	const INPUTFORMAT_REFERENCE = 3;
	const INPUTFORMAT_BOOLEAN = 4;
	const INPUTFORMAT_DATETIME = 5;
	const INPUTFORMAT_FILE = 6;
	const INPUTFORMAT_RATING = 7;
	const INPUTFORMAT_ILIAS_REF = 8;
	const INPUTFORMAT_MOB = 9;
	const INPUTFORMAT_REFERENCELIST = 10;
	const INPUTFORMAT_FORMULA = 11;
    const INPUTFORMAT_NON_EDITABLE_VALUE = 12;
	const LINK_MAX_LENGTH = 40;

    public static $mob_suffixes = array('jpg', 'jpeg', 'gif', 'png', 'mp3', 'flx', 'mp4', 'm4v', 'mov', 'wmv');

    /**
	 * @var int
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var int
	 */
	protected $storageLocation;
	/**
	 * @var string
	 */
	protected $dbType;


	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  integer datatype_id
	 *
	 */
	public function __construct($a_id = 0) {
		if ($a_id != 0) {
			$this->id = $a_id;
			$this->doRead();
		}
	}


	/**
	 * Get field id
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * Set title
	 *
	 * @param string $a_title
	 */
	public function setTitle($a_title) {
		$this->title = $a_title;
	}


	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * Set Storage Location
	 *
	 * @param int $a_id
	 */
	public function setStorageLocation($a_id) {
		$this->storageLocation = $a_id;
	}


	/**
	 * Get Storage Location
	 *
	 * @return int
	 */
	public function getStorageLocation() {
		return $this->storageLocation;
	}


	/*
	 * getDbType
	 */
	public function getDbType() {
		return $this->dbType;
	}


	/**
	 * Read Datatype
	 */
	public function doRead() {
		global $ilDB;

		$query = "SELECT * FROM il_dcl_datatype WHERE id = " . $ilDB->quote($this->getId(), "integer") . " ORDER BY sort";
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
	static function getAllDatatypes() {
		global $ilDB;

		$query = "SELECT * FROM il_dcl_datatype ORDER BY sort";
		$set = $ilDB->query($query);

		$all = array();
		while ($rec = $ilDB->fetchAssoc($set)) {
			$all[$rec[id]] = $rec;
		}

		return $all;
	}


	/**
	 * Get all properties of a Datatype
	 *
	 * @param int $a_id datatype_id
	 *
	 * @return array
	 */
	public static function getProperties($a_id) {
		global $ilDB;

		$query = "SELECT * FROM il_dcl_datatype_prop
					WHERE datatype_id = " . $ilDB->quote($a_id, "integer");
		$set = $ilDB->query($query);

		$all = array();
		while ($rec = $ilDB->fetchAssoc($set)) {
			$all[] = $rec;
		}

		return $all;
	}


	/**
	 * now only distinguishes between number and text values
	 *
	 * @param $type_id int
	 * @param $value   mixed
	 *
	 * @return bool
	 */
	static function checkValidity($type_id, $value) {
		//required is checked by form. so null input is valid.
		if ($value == NULL) {
			return true;
		}

		switch ($type_id) {
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
	 * @param ilDataCollectionField $field
	 *
	 * @internal param $type_id
	 * @return ilCheckboxInputGUI|ilDateTimeInputGUI|ilFileInputGUI|ilTextInputGUI|NULL
	 */
	static function getInputField(ilDataCollectionField $field) {
		global $lng;
		$type_id = $field->getDatatypeId();
		$title = $field->getTitle();
		$input = NULL;
		switch ($type_id) {
			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				$input = new ilTextInputGUI($title, 'field_' . $field->getId());
				if ($field->getTextArea()) {
					$input = new ilTextAreaInputGUI($title, 'field_' . $field->getId());
				}
				if ($field->getLength()) {
					$input->setInfo($lng->txt("dcl_max_text_length") . ": " . $field->getLength());
					if (!$field->getTextArea()) {
						$input->setMaxLength($field->getLength());
					}
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
				$input = new ilTextInputGUI($title, 'field_' . $field->getId());
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
				$input = new ilCheckboxInputGUI($title, 'field_' . $field->getId());
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
				$input = new ilDateTimeInputGUI($title, 'field_' . $field->getId());
				$input->setStartYear(date("Y") - 100);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_FILE:
				$input = new ilFileInputGUI($title, 'field_' . $field->getId());
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				if (!$field->isNRef()) {
					$input = new ilSelectInputGUI($title, 'field_' . $field->getId());
				} else {
					$input = new ilMultiSelectInputGUI($title, 'field_' . $field->getId());
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_RATING:
				$input = new ilTextInputGUI($title, 'field_' . $field->getId());
				$input->setValue($lng->txt("dcl_editable_in_table_gui"));
				$input->setDisabled(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
				$input = new ilDataCollectionTreePickInputGUI($title, 'field_' . $field->getId());
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_MOB:
				$input = new ilFileInputGUI($title, 'field_' . $field->getId());
				$input->setSuffixes(self::$mob_suffixes);
				$input->setAllowDeletion(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_FORMULA:
				$input = new ilNonEditableValueGUI($title, 'field_' . $field->getId());
				$input->setValue('-');
				break;
		}
		if ($field->getDescription() && $input !== NULL) {
			$input->setInfo($field->getDescription() . ($input->getInfo() ? "<br>" . $input->getInfo() : ""));
		}

		return $input;
	}


	/**
	 * addFilterInputFieldToTable This function adds the according filter item to the table gui passed as argument.
	 *
	 * @param $field  ilDataCollectionField The field which should be filterable.
	 * @param &$table ilTable2GUI The table you want the filter to be added to.
	 *
	 * @return null|object
	 */
	static function addFilterInputFieldToTable(ilDataCollectionField $field, ilTable2GUI &$table) {
		global $lng;

		$type_id = $field->getDatatypeId();
		$input = NULL;

		switch ($type_id) {
			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_NUMBER_RANGE, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_SELECT, false, $field->getId());
				$input->setOptions(array(
					"" => $lng->txt("dcl_any"),
					"not_checked" => $lng->txt("dcl_not_checked"),
					"checked" => $lng->txt("dcl_checked")
				));
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_DATE_RANGE, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				$input->setStartYear(date("Y") - 100);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_FILE:
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_SELECT, false, $field->getId());
				$ref_field_id = $field->getFieldRef();
				$ref_field = ilDataCollectionCache::getFieldCache($ref_field_id);
				$ref_table = ilDataCollectionCache::getTableCache($ref_field->getTableId());
				$options = array();
				foreach ($ref_table->getRecords() as $record) {
					$options[$record->getId()] = $record->getRecordFieldValue($ref_field_id);
				}
				// Sort by values ASC
				asort($options);
				$options = array( '' => $lng->txt('dcl_any') ) + $options;
				$input->setOptions($options);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_RATING:
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_SELECT, false, $field->getId());
				$options = array( "" => $lng->txt("dcl_any"), 1 => ">1", 2 => ">2", 3 => ">3", 4 => ">4", 5 => "5" );
				$input->setOptions($options);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_MOB:
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_TEXT, false, $field->getId());
				$input->setSubmitFormOnEnter(true);
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCELIST:
				//FIXME
				$input = $table->addFilterItemByMetaType("filter_" . $field->getId(), ilTable2GUI::FILTER_SELECT, false, $field->getId());
				$ref_field_id = $field->getFieldRef();
				$ref_field = ilDataCollectionCache::getFieldCache($ref_field_id);
				$ref_table = ilDataCollectionCache::getTableCache($ref_field->getTableId());
				$options = array();
				foreach ($ref_table->getRecords() as $record) {
					$options[$record->getId()] = $record->getRecordFieldValue($ref_field_id);
				}
				// Sort by values ASC
				asort($options);
				$options = array( '' => $lng->txt('dcl_any') ) + $options;
				$input->setOptions($options);
				break;
		}

		if ($input != NULL) {
			$input->setTitle($field->getTitle());
		}

		return $input;
	}


	/**
	 * @param ilDataCollectionRecord $record
	 * @param ilDataCollectionField  $field
	 * @param                        $filter
	 *
	 * @return bool
	 */
	public static function passThroughFilter(ilDataCollectionRecord $record, ilDataCollectionField $field, $filter) {
		$pass = false;
		$type_id = $field->getDatatypeId();
		$value = $record->getRecordFieldValue($field->getId());

		switch ($type_id) {
			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				if (!$filter || strpos(strtolower($value), strtolower($filter)) !== false) {
					$pass = true;
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
				if ((!$filter['from'] || $value >= $filter['from']) && (!$filter['to'] || $value <= $filter['to'])) {
					$pass = true;
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
				if ((($filter == "checked" && $value == 1) || ($filter == "not_checked" && $value == 0)) || $filter == '' || !$filter) {
					$pass = true;
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
				if ((!$filter['from'] || $value >= $filter['from']) && (!$filter['to'] || $value <= $filter['to'])) {
					$pass = true;
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_FILE:
				if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {

					$pass = true;
					break;
				}

				$file_obj = new ilObjFile($value, false);
				$file_name = $file_obj->getTitle();
				if (!$filter || strpos(strtolower($file_name), strtolower($filter)) !== false) {
					$pass = true;
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				$props = $field->getProperties();
				if ($filter && $props[ilDataCollectionField::PROPERTYID_N_REFERENCE] && is_array($value) && in_array($filter, $value)) {
					$pass = true;
				}
				if (!$filter || $filter == $value) {
					$pass = true;
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_RATING:
				if (!$filter || $filter <= $value['avg']) {
					$pass = true;
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
				$obj_id = ilObject::_lookupObjId($value);
				if (!$filter || strpos(strtolower(ilObject::_lookupTitle($obj_id)), strtolower($filter)) !== false) {
					$pass = true;
				}
				break;
			case ilDataCollectionDatatype::INPUTFORMAT_MOB:
				$m_obj = new ilObjMediaObject($value, false);
				$file_name = $m_obj->getTitle();
				if (!$filter || strpos(strtolower($file_name), strtolower($filter)) !== false) {
					$pass = true;
				}
				break;
		}

		//for the fields owner and last edit by, we check the name, not the ID
		if (($field->getId() == "owner" || $field->getId() == "last_edit_by") && $filter) {
			$pass = false;
			$user = new ilObjUser($value);
			if (strpos($user->getFullname(), $filter) !== false) {
				$pass = true;
			}
		}

		return $pass;
	}


	/**
	 * Function to parse incoming data from form input value $value. returns the string/number/etc. to store in the database.
	 *
	 * @param                             $value
	 * @param ilDataCollectionRecordField $record_field
	 *
	 * @return int|string
	 */
	public function parseValue($value, ilDataCollectionRecordField $record_field) {
		$return = false;

		if ($this->id == ilDataCollectionDatatype::INPUTFORMAT_FILE) {
			$file = $value;

			if (is_array($file) && $file['tmp_name']) {
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
			} else {
				$return = $record_field->getValue();
			}
		} elseif ($this->id == ilDataCollectionDatatype::INPUTFORMAT_MOB) {
			if ($value == - 1) //marked for deletion.
			{
				return 0;
			}

			$media = $value;
			if (is_array($media) && $media['tmp_name']) {
				$mob = new ilObjMediaObject();
				$mob->setTitle($media['name']);
				$mob->create();
				$mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
				if (!is_dir($mob_dir)) {
					$mob->createDirectory();
				}
				$media_item = new ilMediaItem();
				$mob->addMediaItem($media_item);
				$media_item->setPurpose("Standard");
				$file_name = ilUtil::getASCIIFilename($media['name']);
				$file_name = str_replace(" ", "_", $file_name);
				$file = $mob_dir . "/" . $file_name;
				$title = $file_name;
				$location = $file_name;
				ilUtil::moveUploadedFile($media['tmp_name'], $file_name, $file);
				ilUtil::renameExecutables($mob_dir);
				// Check image/video
				$format = ilObjMediaObject::getMimeType($file);
				if ($format == 'image/jpeg') {
					list($width, $height, $type, $attr) = getimagesize($file);
					$arr_properties = $record_field->getField()->getProperties();
					$new_width = $arr_properties[ilDataCollectionField::PROPERTYID_WIDTH];
					$new_height = $arr_properties[ilDataCollectionField::PROPERTYID_HEIGHT];
					if ($new_width || $new_height) {
						//only resize if it is bigger, not if it is smaller
						if ($new_height < $height && $new_width < $width) {
							//resize proportional
							if (!$new_height || !$new_width) {
								$format = ilObjMediaObject::getMimeType($file);
								$wh = ilObjMediaObject::_determineWidthHeight("", "", $format, "File", $file, "", true, false, $arr_properties[ilDataCollectionField::PROPERTYID_WIDTH], (int)$arr_properties[ilDataCollectionField::PROPERTYID_HEIGHT]);
							} else {
								$wh['width'] = (int)$arr_properties[ilDataCollectionField::PROPERTYID_WIDTH];
								$wh['height'] = (int)$arr_properties[ilDataCollectionField::PROPERTYID_HEIGHT];
							}

							$location = ilObjMediaObject::_resizeImage($file, $wh['width'], $wh['height'], false);
						}
					}
				}

				ilObjMediaObject::_saveUsage($mob->getId(), "dcl:html", $record_field->getRecord()->getTable()->getCollectionObject()->getId());
				$media_item->setFormat($format);
				$media_item->setLocation($location);
				$media_item->setLocationType("LocalFile");

				// FSX MediaPreview
				include_once("./Services/MediaObjects/classes/class.ilFFmpeg.php");
				if (ilFFmpeg::supportsImageExtraction($format)) {
					$med = $mob->getMediaItem("Standard");
					$mob_file = ilObjMediaObject::_getDirectory($mob->getId()) . "/" . $med->getLocation();
					$a_target_dir = ilObjMediaObject::_getDirectory($mob->getId());
					$new_file = ilFFmpeg::extractImage($mob_file, "mob_vpreview.png", $a_target_dir, 1);
				}

				$mob->update();
				$return = $mob->getId();
			} else {
				$return = $record_field->getValue();
			}
		} elseif ($this->id == ilDataCollectionDatatype::INPUTFORMAT_DATETIME) {
			return $value["date"] . " " . $value["time"];
		} elseif ($this->id == ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN) {
			$return = $value ? 1 : 0;
		} elseif ($this->id == ilDataCollectionDatatype::INPUTFORMAT_TEXT) {
			$arr_properties = $record_field->getField()->getProperties();
			if ($arr_properties[ilDataCollectionField::PROPERTYID_TEXTAREA]) {
				$return = nl2br($value);
			} else {
				$return = $value;
			}
		} else {
			if ($this->id == ilDataCollectionDatatype::INPUTFORMAT_NUMBER) {
				$return = ($value == '') ? NULL : $value; //SW, Ilias Mantis #0011799: Return null otherwise '' is casted to 0 in DB
			} else {
				$return = $value;
			}
		}

		return $return;
	}


	/**
	 * Function to parse incoming data from form input value $value. returns the strin/number/etc. to store in the database.
	 *
	 * @param $value
	 *
	 * @return int|string
	 */
	public function parseExportValue($value) {
		$return = false;

		if ($this->id == ilDataCollectionDatatype::INPUTFORMAT_FILE) {
			if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
				return;
			}

			$file = $value;
			if ($file != "-") {
				$file_obj = new ilObjFile($file, false);
				$file_name = $file_obj->getFileName();

				$return = $file_name;
			} else {
				$return = $file;
			}
		} elseif ($this->id == ilDataCollectionDatatype::INPUTFORMAT_MOB) {
			$file = $value;
			if ($file != "-") {
				$mob = new ilObjMediaObject($file, false);
				$mob_name = $mob->getTitle();

				$return = $mob_name;
			} else {
				$return = $file;
			}
		} elseif ($this->id == ilDataCollectionDatatype::INPUTFORMAT_DATETIME) {
			$return = substr($value, 0, 10);
		} elseif ($this->id == ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN) {
			$return = $value ? 1 : 0;
		} else {
			$return = $value;
		}

		return $return;
	}


	/**
	 * function parses stored value in database to a html output for eg. the record list gui.
	 *
	 * @param                             $value
	 * @param ilDataCollectionRecordField $record_field
	 *
	 * @return mixed
	 */
	public function parseHTML($value, ilDataCollectionRecordField $record_field, $link = true) {
		global $ilAccess, $ilCtrl, $lng;;

		switch ($this->id) {
			case self::INPUTFORMAT_DATETIME:
				$html = ilDatePresentation::formatDate(new ilDate($value, IL_CAL_DATETIME));
				break;

			case self::INPUTFORMAT_FILE:

				if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
					$html = "";
					break;
				}

				$file_obj = new ilObjFile($value, false);
				$ilCtrl->setParameterByClass("ildatacollectionrecordlistgui", "record_id", $record_field->getRecord()->getId());
				$ilCtrl->setParameterByClass("ildatacollectionrecordlistgui", "field_id", $record_field->getField()->getId());

				$html = '<a href="' . $ilCtrl->getLinkTargetByClass("ildatacollectionrecordlistgui", "sendFile") . '">' . $file_obj->getFileName()
					. '</a>';
				if (ilPreview::hasPreview($file_obj->getId())) {
					ilPreview::createPreview($file_obj); // Create preview if not already existing
					$preview = new ilPreviewGUI((int)$_GET['ref_id'], ilPreviewGUI::CONTEXT_REPOSITORY, $file_obj->getId(), $ilAccess);
					$preview_status = ilPreview::lookupRenderStatus($file_obj->getId());
					$preview_status_class = "";
					$preview_text_topic = "preview_show";
					if ($preview_status == ilPreview::RENDER_STATUS_NONE) {
						$preview_status_class = "ilPreviewStatusNone";
						$preview_text_topic = "preview_none";
					}
					$wrapper_html_id = 'record_field_' . $record_field->getId();
					$script_preview_click = $preview->getJSCall($wrapper_html_id);
					$preview_title = $lng->txt($preview_text_topic);
					$preview_icon = ilUtil::getImagePath("preview.png", "Services/Preview");
					$html = '<div id="' . $wrapper_html_id . '">' . $html;
					$html .= '<span class="il_ContainerItemPreview ' . $preview_status_class . '"><a href="javascript:void(0);" onclick="'
						. $script_preview_click . '" title="' . $preview_title . '"><img src="' . $preview_icon
						. '" height="16" width="16"></a></span></div>';
				}
				break;

			case self::INPUTFORMAT_MOB:

				$mob = new ilObjMediaObject($value, false);
				$med = $mob->getMediaItem('Standard');
				if (!$med->location) {
					$html = "";
					break;
				}
				$arr_properties = $record_field->getField()->getProperties();
				$is_linked_field = $arr_properties[ilDataCollectionField::PROPERTYID_LINK_DETAIL_PAGE_MOB];
				$has_view = ilDataCollectionRecordViewViewdefinition::getIdByTableId($record_field->getRecord()->getTableId());
				if (in_array($med->getSuffix(), array( 'jpg', 'jpeg', 'png', 'gif' ))) {
					// Image
					$dir = ilObjMediaObject::_getDirectory($mob->getId());
					$width = (int)$arr_properties[ilDataCollectionField::PROPERTYID_WIDTH];
					$height = (int)$arr_properties[ilDataCollectionField::PROPERTYID_HEIGHT];
					$html = ilUtil::img($dir . "/" . $med->location, '', $width, $height);

					if ($is_linked_field AND $has_view AND $link) {
						$ilCtrl->setParameterByClass('ildatacollectionrecordviewgui', 'record_id', $record_field->getRecord()->getId());
						$html = '<a href="' . $ilCtrl->getLinkTargetByClass("ildatacollectionrecordviewgui", 'renderRecord') . '">' . $html . '</a>';
					}
				} else {
					// Video/Audio
					$mpl = new ilMediaPlayerGUI($med->getId(), '');
					$mpl->setFile(ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation());
					$mpl->setMimeType($med->getFormat());
					$mpl->setDisplayWidth((int)$arr_properties[ilDataCollectionField::PROPERTYID_WIDTH] . 'px');
					$mpl->setDisplayHeight((int)$arr_properties[ilDataCollectionField::PROPERTYID_HEIGHT] . 'px');
					$mpl->setVideoPreviewPic($mob->getVideoPreviewPic());
					$html = $mpl->getPreviewHtml();

					if ($is_linked_field AND $has_view) {
						global $lng;
						$ilCtrl->setParameterByClass('ildatacollectionrecordviewgui', 'record_id', $record_field->getRecord()->getId());
						$html = $html . '<a href="' . $ilCtrl->getLinkTargetByClass("ildatacollectionrecordviewgui", 'renderRecord') . '">'
							. $lng->txt('details') . '</a>';
					}
				}
				break;

			case self::INPUTFORMAT_BOOLEAN:
				switch ($value) {
					case 0:
						$im = ilUtil::getImagePath('icon_not_ok.svg');
						break;
					case 1:
						$im = ilUtil::getImagePath('icon_ok.svg');
						break;
				}
				$html = "<img src='" . $im . "'>";
				break;

			case ilDataCollectionDatatype::INPUTFORMAT_TEXT:
				//Property URL
				$arr_properties = $record_field->getField()->getProperties();
				if ($arr_properties[ilDataCollectionField::PROPERTYID_URL]) {
					$link = $value;
					if (preg_match("/^[a-z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i", $value)) {
						$value = "mailto:" . $value;
					} elseif (!(preg_match('~(^(news|(ht|f)tp(s?)\://){1}\S+)~i', $value))) {
						return $link;
					}

					$link = $this->shortenLink($link);
					$html = "<a target='_blank' href='" . $value . "'>" . $link . "</a>";
				} elseif ($arr_properties[ilDataCollectionField::PROPERTYID_LINK_DETAIL_PAGE_TEXT] AND $link
					AND ilDataCollectionRecordViewViewdefinition::getIdByTableId($record_field->getRecord()->getTableId())
				) {
					$ilCtrl->setParameterByClass('ildatacollectionrecordviewgui', 'record_id', $record_field->getRecord()->getId());
					$html = '<a href="' . $ilCtrl->getLinkTargetByClass("ildatacollectionrecordviewgui", 'renderRecord') . '">' . $value . '</a>';
				} else {
					$html = $value;
				}
				break;

			default:
				$html = $value;
				break;
		}

		return $html;
	}


	/**
	 * This method shortens a link. The http(s):// and the www part are taken away. The rest will be shortened to sth similar to:
	 * "somelink.de/lange...gugus.html".
	 *
	 * @param $value The link in it's original form.
	 *
	 * @return string The shortened link
	 */
	private function shortenLink($value) {
		if (strlen($value) > self::LINK_MAX_LENGTH) {
			if (substr($value, 0, 7) == "http://") {
				$value = substr($value, 7);
			}
			if (substr($value, 0, 8) == "https://") {
				$value = substr($value, 8);
			}
			if (substr($value, 0, 4) == "www.") {
				$value = substr($value, 4);
			}
		}
		$link = $value;
		if (strlen($value) > self::LINK_MAX_LENGTH) {
			$link = substr($value, 0, (self::LINK_MAX_LENGTH - 3) / 2);
			$link .= "...";
			$link .= substr($value, - (self::LINK_MAX_LENGTH - 3) / 2);
		}

		return $link;
	}


	/**
	 * function parses stored value to the variable needed to fill into the form for editing.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function parseFormInput($value, ilDataCollectionRecordField $record_field) {
		switch ($this->id) {
			case self::INPUTFORMAT_DATETIME:
				if (!$value || $value == "-") {
					return NULL;
				}
				//$datetime = new DateTime();
				$input = array(
					"date" => substr($value, 0, - 9),
					"time" => "00:00:00"
				);
				break;
			case self::INPUTFORMAT_FILE:

				if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
					$input = "";
					break;
				}

				$file_obj = new ilObjFile($value, false);
				//$input = ilObjFile::_lookupAbsolutePath($value);
				$input = $file_obj->getFileName();
				break;
			case self::INPUTFORMAT_MOB:
				if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "mob") {
					$input = "";
					break;
				}

				$media_obj = new ilObjMediaObject($value, false);
				//$input = ilObjFile::_lookupAbsolutePath($value);
				$input = $value;
				break;
			case self::INPUTFORMAT_TEXT:
				$arr_properties = $record_field->getField()->getProperties();
				if ($arr_properties[ilDataCollectionField::PROPERTYID_TEXTAREA]) {
					$breaks = array( "<br />" );
					$input = str_ireplace($breaks, "", $value);
				} else {
					$input = $value;
				}
				break;
			default:
				$input = $value;
				break;
		}

		return $input;
	}
}

?>