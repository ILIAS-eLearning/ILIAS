<?php
require_once('./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordRepresentation.php');

/**
 * Class ilDclReferenceRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclReferenceRecordRepresentation extends ilDclBaseRecordRepresentation {

	/**
	 * @return array|mixed|string
	 */
	public function getHTML($link = true) {
		$value = $this->getRecordField()->getValue();
		$record_field = $this->getRecordField();

		if (!$value || $value == "-") {
			return "";
		}

		$ref_record = ilDclCache::getRecordCache($value);
		$html = "";
		if (!$ref_record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()) {
			//the referenced record_field does not seem to exist.
			$record_field->setValue(NULL);
			$record_field->doUpdate();
		} else {
			$field = $this->getRecordField()->getField();
			if ($field->getProperty(ilDclBaseFieldModel::PROP_REFERENCE_LINK)) {
				global $ilDB;
				/** @var ilDB $ilDB */
				$ref_record = ilDclCache::getRecordCache($value);
				$ref_table = $ref_record->getTable();

				if ($ref_table->getVisibleTableViews($_GET['ref_id'], true)) {
					$html = $this->getLinkHTML(NULL, $value);
				} else {
					$html = $ref_record->getRecordFieldHTML($field->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
				}
			} else {
				$html = $ref_record->getRecordFieldHTML($field->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
			}
		}

		return $html;
	}


	/**
	 * @param null $link_name
	 * @param      $value
	 *
	 * @return string
	 */
	protected function getLinkHTML($link_name = NULL, $value) {
		global $ilCtrl;

		if (!$value || $value == "-") {
			return "";
		}
		$record_field = $this;
		$ref_record = ilDclCache::getRecordCache($value);
		if (!$link_name) {
			$link_name = $ref_record->getRecordFieldHTML($record_field->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
		}
		$ilCtrl->clearParametersByClass("ilDclDetailedViewGUI");
		$ilCtrl->setParameterByClass("ilDclDetailedViewGUI", "record_id", $ref_record->getId());
		$html = "<a href='" . $ilCtrl->getLinkTargetByClass("ilDclDetailedViewGUI", "renderRecord") . "&disable_paging=1'>" . $link_name . "</a>";

		return $html;
	}

}