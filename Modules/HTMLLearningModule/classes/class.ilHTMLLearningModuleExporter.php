<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for html learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesHTMLLearningModule
 */
class ilHTMLLearningModuleExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Modules/HTMLLearningModule/classes/class.ilHTMLLearningModuleDataSet.php");
		$this->ds = new ilHTMLLearningModuleDataSet();
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
        $deps = [];
        $md_ids = [];
        foreach ($a_ids as $id) {
            $md_ids[] = $id.":0:htlm";
        }

        $deps[] = [
            "component" => "Services/MetaData",
            "entity" => "md",
            "ids" => $md_ids
        ];

        // service settings
        $deps[] = [
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids
        ];

        return $deps;
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
			"4.1.0" => array(
				"namespace" => "http://www.ilias.de/Modules/HTMLLearningModule/htlm/4_1",
				"xsd_file" => "ilias_htlm_4_1.xsd",
				"uses_dataset" => true,
				"min" => "4.1.0",
				"max" => "")
		);
	}

}

?>