<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for html learning modules
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesGlossary
 */
class ilGlossaryExporter extends ilXmlExporter
{
    private $ds;

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Modules/Glossary/classes/class.ilGlossaryDataSet.php");
        $this->ds = new ilGlossaryDataSet();
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
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        if ($a_entity == "glo") {
            include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
            include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");

            $md_ids = array();

            // glo related ids
            foreach ($a_ids as $id) {
                $md_ids[] = $id . ":0:glo";
            }

            // definition related ids
            $page_ids = array();
            foreach ($a_ids as $id) {
                // workaround for #0023923
                $all_refs = ilObject::_getAllReferences($id);
                $ref_id = current($all_refs);
                $terms = ilGlossaryTerm::getTermList($ref_id);
                foreach ($terms as $t) {
                    $defs = ilGlossaryDefinition::getDefinitionList($t["id"]);
                    foreach ($defs as $d) {
                        $page_ids[] = "gdf:" . $d["id"];
                        $md_ids[] = $id . ":" . $d["id"] . ":gdf";
                    }
                }
            }
            // definition pages and their metadat
            $deps = array(
                array(
                    "component" => "Services/COPage",
                    "entity" => "pg",
                    "ids" => $page_ids),
                array(
                    "component" => "Services/MetaData",
                    "entity" => "md",
                    "ids" => $md_ids),
            );

            // taxonomy
            include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
            $tax_ids = array();
            foreach ($a_ids as $id) {
                $t_ids = ilObjTaxonomy::getUsageOfObject($id);
                if (count($t_ids) > 0) {
                    $tax_ids[$t_ids[0]] = $t_ids[0];
                }
            }
            if (sizeof($tax_ids)) {
                $deps[] = array(
                    "component" => "Services/Taxonomy",
                    "entity" => "tax",
                    "ids" => $tax_ids
                );
            }

            // advanced metadata
            $advmd_ids = array();
            foreach ($a_ids as $id) {
                $rec_ids = $this->getActiveAdvMDRecords($id);
                if (sizeof($rec_ids)) {
                    foreach ($rec_ids as $rec_id) {
                        $advmd_ids[] = $id . ":" . $rec_id;
                    }
                }
            }
            if (sizeof($advmd_ids)) {
                $deps[] = array(
                    "component" => "Services/AdvancedMetaData",
                    "entity" => "advmd",
                    "ids" => $advmd_ids
                );
            }

            // style
            $obj_ids = (is_array($a_ids))
                ? $a_ids
                : array($a_ids);
            $deps[] = array(
                "component" => "Services/Style",
                "entity" => "object_style",
                "ids" => $obj_ids
            );

            // service settings
            $deps[] = array(
                "component" => "Services/Object",
                "entity" => "common",
                "ids" => $a_ids);

            return $deps;
        }
        return array();
    }

    protected function getActiveAdvMDRecords($a_id)
    {
        include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
        $active = array();
        // selected globals
        $sel_globals = ilAdvancedMDRecord::getObjRecSelection($a_id, "term");

        foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType("glo", "term") as $record_obj) {
            // local ones and globally activated for the object
            if ($record_obj->getParentObject() == $a_id || in_array($record_obj->getRecordId(), $sel_globals)) {
                $active[] = $record_obj->getRecordId();
            }
        }

        return $active;
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

        /*include_once './Modules/Glossary/classes/class.ilObjGlossary.php';
        $glo = new ilObjGlossary($a_id,false);

        include_once './Modules/Glossary/classes/class.ilGlossaryExport.php';
        $exp = new ilGlossaryExport($glo,'xml');
        $zip = $exp->buildExportFile();*/
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
                "namespace" => "http://www.ilias.de/Modules/Glossary/htlm/4_1",
                "xsd_file" => "ilias_glo_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => ""),
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Glossary/htlm/4_1",
                "xsd_file" => "ilias_glo_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "")
            );
    }
}
