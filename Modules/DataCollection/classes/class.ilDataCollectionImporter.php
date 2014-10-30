<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/Export/classes/class.ilXmlImporter.php');
require_once('class.ilDataCollectionDataSet.php');
require_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");

/**
 * Class ilDataCollectionImporter
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDataCollectionImporter extends ilXmlImporter {

	/**
	 * @var ilDataCollectionDataSet
	 */
	protected $ds;


	public function init() {
		$this->ds = new ilDataCollectionDataSet();
		$this->ds->setDSPrefix("ds");
		$this->ds->setImportDirectory($this->getImportDirectory());
	}


	/**
	 * Executes the Import
	 *
	 * @param $a_entity
	 * @param $a_id
	 * @param $a_xml
	 * @param $a_mapping
	 *
	 * @return string|void
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping) {
		$parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(), $a_xml, $this->ds, $a_mapping);
	}


	/**
	 * Called before finishing the import
	 *
	 * @param $a_mapping
	 */
	public function finalProcessing($a_mapping) {
		$this->ds->beforeFinishImport($a_mapping);
	}
}