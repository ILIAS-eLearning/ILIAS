<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for help system information
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesHelp
 */
class ilHelpExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Services/Help/classes/class.ilHelpDataSet.php");
		$this->ds = new ilHelpDataSet();
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
		if ($a_target_release == "4.3.0")
		{
			if ($a_entity == "help")
			{
				$lm_node_ids = array();
				include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
				foreach($a_ids as $lm_id)
				{
					$chaps = ilLMObject::getObjectList($lm_id, "st");
					foreach ($chaps as $chap)
					{
						$lm_node_ids[] = $chap["obj_id"];
					}
				}
					
				return array (
					array(
						"component" => "Services/Help",
						"entity" => "help_map",
						"ids" => $lm_node_ids),
					array(
						"component" => "Services/Help",
						"entity" => "help_tooltip",
						"ids" => $a_ids)
					);
			}
		}
		
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
			"4.3.0" => array(
				"namespace" => "http://www.ilias.de/Services/Help/help/4_3",
				"xsd_file" => "ilias_help_4_3.xsd",
				"uses_dataset" => true,
				"min" => "4.3.0",
				"max" => "")
		);
	}

}

?>