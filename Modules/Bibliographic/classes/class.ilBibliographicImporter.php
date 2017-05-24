<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Export/classes/class.ilXmlImporter.php');
require_once('class.ilBibliographicDataSet.php');
require_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
/**
 * Class ilBibliographicImporter
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilBibliographicImporter extends ilXmlImporter {

	/**
	 * @var ilBibliographicDataSet
	 */
	protected $ds;


	public function init() {
		$this->ds = new ilBibliographicDataSet();
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
}