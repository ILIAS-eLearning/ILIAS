<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Export/classes/class.ilXmlExporter.php");
require_once("./Services/Export/classes/class.ilExport.php");
require_once('class.ilBibliographicDataSet.php');
/**
 * Exporter class for Bibliographic class
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 * @version $Id: $
 * @ingroup ModulesBibliographic
 */
class ilBibliographicExporter extends ilXmlExporter {

	/**
	 * @var ilBibliographicDataSet
	 */
	protected $ds;
	/**
	 * @var ilDB
	 */
	protected $db;


	public function init() {
		global $ilDB;
		$this->ds = new ilBibliographicDataSet();
		$this->ds->setDSPrefix('ds');
		$this->db = $ilDB;
	}


	/**
	 * @param string $a_entity
	 *
	 * @return array
	 */
	public function getValidSchemaVersions($a_entity) {
		return array(
			'4.5.0' => array(
				'namespace' => 'http://www.ilias.de/Modules/DataCollection/dcl/4_5',
				'xsd_file" => "ilias_dcl_4_5.xsd',
				'uses_dataset' => true,
				'min' => '4.5.0',
				'max' => ''
			)
		);
	}


	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id) {
		ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
		$this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
		$this->ds->exportLibraryFile($a_id);

		return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, '', true, true);
	}
}

?>