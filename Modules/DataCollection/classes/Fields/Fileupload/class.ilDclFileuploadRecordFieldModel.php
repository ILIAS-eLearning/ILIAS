<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordFieldModel.php');

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilDclFileuploadRecordFieldModel extends ilDclBaseRecordFieldModel {
	public function parseValue($value) {
		global $DIC;
		$ilUser = $DIC['ilUser'];

		$file = $value;

		$has_save_confirmation = ($this->getRecord()->getTable()->getSaveConfirmation() && !isset($_GET['record_id']));
		$is_confirmed = (bool) (isset($_POST['save_confirmed']));
		
		if (is_array($file) && $file['tmp_name'] != "" && (!$has_save_confirmation || $is_confirmed)) {
			$file_obj = new ilObjFile();
			$file_obj->setType("file");
			$file_obj->setTitle($file["name"]);
			$file_obj->setFileName($file["name"]);
			$file_obj->setFileType(ilMimeTypeUtil::getMimeType("", $file["name"], $file["type"]));
			$file_obj->setFileSize($file["size"]);
			$file_obj->setMode("object");
			$file_obj->create();

			if($has_save_confirmation) {
				$move_file = ilDclPropertyFormGUI::getTempFilename($_POST['ilfilehash'], 'field_'.$this->getField()->getId(), $file["name"], $file["type"]);
				$file_obj->storeUnzipedFile($move_file, $file["name"]);
			} else {
				$move_file = $file['tmp_name'];
				$file_obj->getUploadFile($move_file, $file["name"]);
			}

			$file_id = $file_obj->getId();
			$return = $file_id;
		// handover for save-confirmation
		} else if(is_array($file) && isset($file['tmp_name']) && $file['tmp_name'] != "") {
			$return = $file;
		} else {
			$return = $this->getValue();
		}

		return $return;
	}

	/**
	 * Set value for record field
	 *
	 * @param mixed $value
	 * @param bool  $omit_parsing If true, does not parse the value and stores it in the given format
	 */
	public function setValue($value, $omit_parsing = false) {
		$this->loadValue();

		if (! $omit_parsing) {
			$tmp = $this->parseValue($value, $this);
			$old = $this->value;
			//if parse value fails keep the old value
			if ($tmp !== false) {
				$this->value = $tmp;
				//delete old file from filesystem
				if ($old && $old != $tmp) {
					$this->getRecord()->deleteFile($old);
				}
			}
		} else {
			$this->value = $value;
		}
	}


	/**
	 * @inheritdoc
	 */
	public function parseExportValue($value) {
		if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
			return;
		}

		$file = $value;
		if ($file != "-") {
			$file_obj = new ilObjFile($file, false);
			$file_name = $file_obj->getFileName();

			return $file_name;
		}
		return $file;
	}


	/**
	 * Returns sortable value for the specific field-types
	 *
	 * @param                           $value
	 * @param ilDclBaseRecordFieldModel $record_field
	 * @param bool|true                 $link
	 *
	 * @return int|string
	 */
	public function parseSortingValue($value, $link = true) {
		if (!ilObject2::_exists($value) || ilObject2::_lookupType($value, false) != "file") {
			return '';
		}
		$file_obj = new ilObjFile($value, false);
		return $file_obj->getTitle();
	}
	
	
}