<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for sessions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesSession
 */
class ilSessionExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Modules/Session/classes/class.ilSessionDataSet.php");
		$this->ds = new ilSessionDataSet();
		$this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
		$this->ds->setDSPrefix("ds");
	}


	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		schema version
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
	}

	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top.
	 *
	 * @return
	 */
	function getValidSchemaVersions($a_entity)
	{
		return array (
			"4.1.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Session/sess/4_1",
				"xsd_file" => "ilias_sess_4_1.xsd",
				"uses_dataset" => true,
				"min" => "4.1.0",
				"max" => "")
		);
	}

}

?>