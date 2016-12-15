<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Individual Assessment dataset class
 *
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilIndividualAssessmentDataSet extends ilDataSet {

	/**
	 * @return array
	 */
	public function getSupportedVersions() {
		return array('5.2.0');
	}


	/**
	 * @param string $a_entity
	 * @param string $a_schema_version
	 *
	 * @return string
	 */
	public function getXmlNamespace($a_entity, $a_schema_version) {
		return 'http://www.ilias.de/xml/Modules/IndividualAssessment/'.$a_entity;
	}

	/**
	 * Map XML attributes of entities to datatypes (text, integer...)
	 *
	 * @param string $a_entity
	 * @param string $a_version
	 *
	 * @return array
	 */
	protected function getTypes($a_entity, $a_version) {
		switch ($a_entity) {
			case 'iass':
				return array(
					"id" => "integer",
					"title" => "text",
					"description" => "text",
					"content" => "text",
					"recordTemplate" => "text",
				);
			default:
				return array();
		}
	}

	/**
	 * Return dependencies form entities to other entities (in our case these are all the DB relations)
	 *
	 * @param string $a_entity
	 * @param string $a_version
	 * @param array  $a_rec
	 * @param array  $a_ids
	 *
	 * @return array
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids) {
		return false;
	}

	/**
	 * Read data from Cache for a given entity and ID(s)
	 *
	 * @param string $a_entity
	 * @param string $a_version
	 * @param array  $a_ids one or multiple ids
	 */
	public function readData($a_entity, $a_version, $a_ids) {
		$this->data = array();
		if (!is_array($a_ids)) {
			$a_ids = array($a_ids);
		}
		$this->_readData($a_entity, $a_ids);
	}

	/**
	 * Build data array, data is read from cache except iass object itself
	 *
	 * @param string $a_entity
	 * @param array  $a_ids
	 */
	protected function _readData($a_entity, $a_ids) {
		switch ($a_entity) {
			case 'iass':
				foreach ($a_ids as $iass_id) {
					if (ilObject::_lookupType($iass_id) == 'iass') {
						$obj = new ilObjIndividualAssessment($iass_id, false);
						$data = array(
							'id' => $bibl_id,
							'title' => $obj->getTitle(),
							'description' => $obj->getDescription(),
							'content' => $obj->getSettings()->content(),
							'recordTemplate' => $obj->getSettings()->recordTemplate(),
						);
						$this->data[] = $data;
					}
				}
				break;
			default:
		}
	}
}