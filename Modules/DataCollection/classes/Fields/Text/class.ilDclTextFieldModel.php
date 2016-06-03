<?php
require_once("./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldModel.php");
require_once ('./Modules/DataCollection/classes/Fields/Text/class.ilDclTextRecordQueryObject.php');
require_once("./Modules/DataCollection/classes/Helpers/class.ilDclRecordQueryObject.php");

/**
 * Class ilDclTextFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclTextFieldModel extends ilDclBaseFieldModel {

	/**
	 * @inheritdoc
	 */
	public function getRecordQueryFilterObject($filter_value = "", ilDclBaseFieldModel $sort_field = null) {
		global $ilDB;

		$join_str = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
			. $ilDB->quote($this->getId(), 'integer') . ") ";
		$join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id AND filter_stloc_{$this->getId()}.value LIKE "
			. $ilDB->quote("%$filter_value%", 'text') . ") ";

		$sql_obj = new ilDclRecordQueryObject();
		$sql_obj->setJoinStatement($join_str);

		return $sql_obj;
	}

	/**
	 * @inheritdoc
	 */
	public function getRecordQuerySortObject($direction = "asc", $sort_by_status = false) {
		// use custom record sorting for url-fields
		if($this->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
			return new ilDclTextRecordQueryObject();
		} else {
			return parent::getRecordQuerySortObject($direction, $sort_by_status);
		}
	}


	/**
	 * @inheritdoc
	 */
	public function checkValidity($value, $record_id = NULL) {
		//Don't check empty values
		if ($value == NULL) {
			return true;
		}

		$regex = $this->getProperty(ilDclBaseFieldModel::PROP_REGEX);
		if (substr($regex, 0, 1) != "/") {
			$regex = "/" . $regex;
		}
		if (substr($regex, - 1) != "/") {
			$regex .= "/";
		}

		if ($this->getProperty(ilDclBaseFieldModel::PROP_LENGTH) < mb_strlen($value, 'UTF-8') && is_numeric($this->getProperty(ilDclBaseFieldModel::PROP_LENGTH))) {
			throw new ilDclInputException(ilDclInputException::LENGTH_EXCEPTION);
		}

		if (! ($this->getProperty(ilDclBaseFieldModel::PROP_REGEX) == NULL || preg_match($regex, $value) === false)) {
			throw new ilDclInputException(ilDclInputException::REGEX_EXCEPTION);
		}

		//email or url
		$has_url_properties = $this->getProperty(ilDclBaseFieldModel::PROP_URL);
		if ($has_url_properties) {
			if ($json = json_decode($value) && json_decode($value) instanceof stdClass) {
				$value = $json->link;
			}
			if (substr($value, 0, 3) === 'www') {
				$value = 'http://' . $value;
			}
			if (! filter_var($value, FILTER_VALIDATE_URL) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
				throw new ilDclInputException(ilDclInputException::NOT_URL);
			}
		}

		if ($this->isUnique()) {
			$table = ilDclCache::getTableCache($this->getTableId());
			foreach ($table->getRecords() as $record) {
				//for text it has to be case insensitive.
				$record_value = $record->getRecordFieldValue($this->getId());
				if ($has_url_properties) {
					$record_value = $record_value['link'];
				}

				if (strtolower($this->normalizeValue($record_value)) == strtolower($this->normalizeValue($value))
					&& ($record->getId() != $record_id
						|| $record_id == 0)
				) {
					throw new ilDclInputException(ilDclInputException::UNIQUE_EXCEPTION);
				}
			}
		}
	}


	/**
	 * @inheritDoc
	 */
	public function checkFieldCreationInput(ilPropertyFormGUI $form) {
		global $lng;

		$return = true;
		// Additional check for text fields: The length property should be max 200 if the textarea option is not set
		if ((int)$form->getInput('prop_' . ilDclBaseFieldModel::PROP_LENGTH) > 200 && !$form->getInput('prop_' . ilDclBaseFieldModel::PROP_TEXTAREA)) {
			$inputObj = $form->getItemByPostVar('prop_' . ilDclBaseFieldModel::PROP_LENGTH);
			$inputObj->setAlert($lng->txt("form_msg_value_too_high"));
			$return = false;
		}

		return $return;
	}


	/**
	 * @inheritDoc
	 */
	public function getValidFieldProperties() {
		return array(ilDclBaseFieldModel::PROP_LENGTH, ilDclBaseFieldModel::PROP_REGEX, ilDclBaseFieldModel::PROP_URL, ilDclBaseFieldModel::PROP_TEXTAREA, ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT);
	}
}