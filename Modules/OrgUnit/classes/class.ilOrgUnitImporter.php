<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Export/classes/class.ilXmlImporter.php');

/**
 * Class ilOrgUnitImporter
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitImporter extends ilXmlImporter {

	/**
	 * @var  array lang_var => language variable, import_id => the reference or import id, depending on the ou_id_type
	 */
	public $errors;
	/**
	 * @var  array lang_var => language variable, import_id => the reference or import id, depending on the ou_id_type
	 */
	public $warnings;
	/**
	 * @var array keys in {updated, edited, deleted}
	 */
	public $stats;


	/**
	 * @param $id
	 * @param $type
	 *
	 * @return bool|int
	 */
	protected function buildRef($id, $type) {
		if ($type == 'reference_id') {
			if (!ilObjOrgUnit::_exists($id, true)) {
				return false;
			}

			return $id;
		} elseif ($type == 'external_id') {
			$obj_id = ilObject::_lookupObjIdByImportId($id);

			if (!ilObject::_hasUntrashedReference($obj_id)) {
				return false;
			}

			$ref_ids = ilObject::_getAllReferences($obj_id);

			if (!count($ref_ids)) {
				return false;
			}

			foreach($ref_ids as $ref_id) {
				if(!ilObject::_isInTrash($ref_id))
					return $ref_id;
			}
			return false;
		} else {
			return false;
		}
	}


	/**
	 * @return bool
	 */
	public function hasErrors() {
		return count($this->errors) != 0;
	}


	/**
	 * @return bool
	 */
	public function hasWarnings() {
		return count($this->warnings) != 0;
	}


	/**
	 * @param      $lang_var
	 * @param      $import_id
	 * @param null $action
	 */
	public function addWarning($lang_var, $import_id, $action = NULL) {
		$this->warnings[] = array( 'lang_var' => $lang_var, 'import_id' => $import_id, 'action' => $action );
	}


	/**
	 * @param      $lang_var
	 * @param      $import_id
	 * @param null $action
	 */
	public function addError($lang_var, $import_id, $action = NULL) {
		$this->errors[] = array( 'lang_var' => $lang_var, 'import_id' => $import_id, 'action' => $action );
	}


	/**
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}


	/**
	 * @return array
	 */
	public function getWarnings() {
		return $this->warnings;
	}


	/**
	 * @return array
	 */
	public function getStats() {
		return $this->stats;
	}


	/**
	 * @param $a_entity
	 * @param $a_id
	 * @param $a_xml
	 * @param $a_mapping
	 *
	 * @return string|void
	 *
	 * @deprecated
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping) {
	}
}

?>