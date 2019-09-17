<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for exercise
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesExercise
 */
class ilExerciseExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Modules/Exercise/classes/class.ilExerciseDataSet.php");
		$this->ds = new ilExerciseDataSet();
		$this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
		$this->ds->setDSPrefix("ds");
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

    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        $res = [];

        if ($a_entity == "exc") {
            // service settings
            $res[] = array(
                "component" => "Services/Object",
                "entity" => "common",
                "ids" => $a_ids
            );
        }

        return $res;
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
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/4_1",
				"xsd_file" => "ilias_exc_4_1.xsd",
				"uses_dataset" => true,
				"min" => "4.1.0",
				"max" => "4.3.99"),
			"4.4.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/4_4",
				"xsd_file" => "ilias_exc_4_4.xsd",
				"uses_dataset" => true,
				"min" => "4.4.0",
				"max" => "4.4.99"),
			"5.0.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/5_0",
				"xsd_file" => "ilias_exc_5_0.xsd",
				"uses_dataset" => true,
				"min" => "5.0.0",
				"max" => "5.0.99"),
			"5.1.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/5_1",
				"xsd_file" => "ilias_exc_5_1.xsd",
				"uses_dataset" => true,
				"min" => "5.1.0",
				"max" => "5.1.99"),
			"5.2.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/5_2",
				"xsd_file" => "ilias_exc_5_2.xsd",
				"uses_dataset" => true,
				"min" => "5.2.0",
				"max" => "5.2.99"),
			"5.3.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/5_3",
				"xsd_file" => "ilias_exc_5_3.xsd",
				"uses_dataset" => true,
				"min" => "5.3.0",
				"max" => "")
		);
	}

}

?>