<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Exporter class for wikis
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiExporter extends ilXmlExporter
{
    private ilWikiDataSet $ds;
    protected ilLogger $wiki_log;

    public function init() : void
    {
        $this->ds = new ilWikiDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
        $this->wiki_log = ilLoggerFactory::getLogger('wiki');
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ) : array {
        $pg_ids = array();
        foreach ($a_ids as $id) {
            $pages = ilWikiPage::getAllWikiPages($id);
            foreach ($pages as $p) {
                if (ilWikiPage::_exists("wpg", $p["id"])) {
                    $pg_ids[] = "wpg:" . $p["id"];
                }
            }
        }

        $deps = array(
            array(
                "component" => "Services/COPage",
                "entity" => "pg",
                "ids" => $pg_ids),
            array(
                "component" => "Services/Rating",
                "entity" => "rating_category",
                "ids" => $a_ids
                )
            );
        
        $advmd_ids = array();
        foreach ($a_ids as $id) {
            $rec_ids = $this->getActiveAdvMDRecords($id);
            $this->wiki_log->debug("advmd rec ids: wiki id:" . $id . ", adv rec ids" . print_r($rec_ids, true));
            if (count($rec_ids)) {
                foreach ($rec_ids as $rec_id) {
                    $advmd_ids[] = $id . ":" . $rec_id;
                }
            }
        }

        $this->wiki_log->debug("advmd ids: " . print_r($advmd_ids, true));

        if (count($advmd_ids)) {
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
    
    protected function getActiveAdvMDRecords(int $a_id) : array
    {
        $active = array();
        // selected globals
        $sel_globals = ilAdvancedMDRecord::getObjRecSelection($a_id, "wpg");

        foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType("wiki", "wpg") as $record_obj) {
            // local ones and globally activated for the object
            if ($record_obj->getParentObject() === $a_id || in_array($record_obj->getRecordId(), $sel_globals)) {
                $active[] = $record_obj->getRecordId();
            }
        }

        $this->wiki_log->debug("active md rec: " . print_r($active, true));

        return $active;
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ) : string {
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "5.4.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Wiki/wiki/5_4",
                "xsd_file" => "ilias_wiki_5_4.xsd",
                "uses_dataset" => true,
                "min" => "5.4.0",
                "max" => ""),
            "4.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Wiki/wiki/4_1",
                "xsd_file" => "ilias_wiki_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "4.2.99"),
            "4.3.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Wiki/wiki/4_3",
                "xsd_file" => "ilias_wiki_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "4.3.99"),
            "4.4.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Wiki/wiki/4_4",
                "xsd_file" => "ilias_wiki_4_4.xsd",
                "uses_dataset" => true,
                "min" => "4.4.0",
                "max" => "5.0.99"),
            "5.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Wiki/wiki/5_1",
                "xsd_file" => "ilias_wiki_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "5.3.99")
        );
    }
}
