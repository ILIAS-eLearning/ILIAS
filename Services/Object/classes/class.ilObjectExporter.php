<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for object related data (please note that title and description
 * are usually included in the spefific object exporter classes, this class
 * takes care of additional general object related data (e.g. translations)
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id$
 * @ingroup ServicesObject
 */
class ilObjectExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Services/Object/classes/class.ilObjectDataSet.php");
		$this->ds = new ilObjectDataSet();
		$this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
		$this->ds->setDSPrefix("ds");
	}

	/**
	 * Get tail dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
		return array();
	}

	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		target release
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
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
            "5.4.0" => array(
                "namespace" => "http://www.ilias.de/Services/Object/obj/5_4",
                "xsd_file" => "ilias_obj_5_4.xsd",
                "uses_dataset" => true,
                "min" => "5.4.0",
                "max" => ""),
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/Object/obj/5_1",
                "xsd_file" => "ilias_obj_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "5.3.99"),
			"4.4.0" => array(
				"namespace" => "http://www.ilias.de/Services/Object/obj/4_4",
				"xsd_file" => "ilias_obj_4_4.xsd",
				"uses_dataset" => true,
				"min" => "4.4.0",
				"max" => "5.0.99")
		);
	}

}

?>