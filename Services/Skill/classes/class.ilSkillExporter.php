<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesSkill
 */
class ilSkillExporter extends ilXmlExporter
{
    private $ds;

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Services/Skill/classes/class.ilSkillDataSet.php");
        $this->ds = new ilSkillDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getExport()->getConfig("Services/Skill");
        $this->ds->setSelectedNodes($this->config->getSelectedNodes());
        $this->ds->setSelectedProfiles($this->config->getSelectedProfiles());
        $this->ds->setMode($this->config->getMode());
    }


    /**
     * Get tail dependencies
     *
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        $deps = array();
        /*$deps = array (
            array(
                "component" => "Services/COPage",
                "entity" => "pg",
                "ids" => $pg_ids),
            array(
                "component" => "Services/Rating",
                "entity" => "rating_category",
                "ids" => $a_ids
                )
            );*/

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
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     *
     * @return
     */
    public function getValidSchemaVersions($a_entity)
    {
        return array(
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/Skill/skll/5_1",
                "xsd_file" => "ilias_skll_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "")
        );
    }
}
