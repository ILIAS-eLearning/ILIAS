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
 * Glossary XML export
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryExporter extends ilXmlExporter
{
    private ilGlossaryDataSet $ds;
    protected \ILIAS\Glossary\Metadata\MetadataManager $metadata;

    public function init(): void
    {
        global $DIC;

        $this->ds = new ilGlossaryDataSet();
        $this->ds->initByExporter($this);
        $this->ds->setDSPrefix("ds");
        $this->metadata = $DIC->glossary()->internal()->domain()->metadata();
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        if ($a_entity == "glo") {
            // definition related ids
            $page_ids = array();
            foreach ($a_ids as $id) {
                // workaround for #0023923
                $all_refs = ilObject::_getAllReferences($id);
                $ref_id = current($all_refs);

                // see #29014, we include referenced terms in the export as well
                $terms = ilGlossaryTerm::getTermList(
                    [$ref_id],
                    "",
                    "",
                    "",
                    0,
                    false,
                    null,
                    true
                );

                foreach ($terms as $t) {
                    $page_ids[] = "term:" . $t["id"];
                }
            }
            // definition pages and their metadat
            $deps = array(
                array(
                    "component" => "components/ILIAS/COPage",
                    "entity" => "pg",
                    "ids" => $page_ids)
            );

            // taxonomy
            $tax_ids = array();
            foreach ($a_ids as $id) {
                $t_ids = ilObjTaxonomy::getUsageOfObject((int) $id);
                if (count($t_ids) > 0) {
                    $tax_ids[$t_ids[0]] = $t_ids[0];
                }
            }
            if (count($tax_ids)) {
                $deps[] = array(
                    "component" => "components/ILIAS/Taxonomy",
                    "entity" => "tax",
                    "ids" => $tax_ids
                );
            }

            // advanced metadata
            $advmd_ids = array();
            foreach ($a_ids as $id) {
                $rec_ids = $this->getActiveAdvMDRecords($id);
                if (count($rec_ids)) {
                    foreach ($rec_ids as $rec_id) {
                        $advmd_ids[] = $id . ":" . $rec_id->getRecordId();
                    }
                }
            }
            if (count($advmd_ids)) {
                $deps[] = array(
                    "component" => "components/ILIAS/AdvancedMetaData",
                    "entity" => "advmd",
                    "ids" => $advmd_ids
                );
            }

            // style
            $obj_ids = (is_array($a_ids))
                ? $a_ids
                : array($a_ids);
            $deps[] = array(
                "component" => "components/ILIAS/Style",
                "entity" => "object_style",
                "ids" => $obj_ids
            );

            // service settings
            $deps[] = array(
                "component" => "components/ILIAS/Object",
                "entity" => "common",
                "ids" => $a_ids);

            $md_ids = [];
            foreach ($a_ids as $crs_id) {
                $md_ids[] = $crs_id . ":0:glo";
            }
            if ($md_ids !== []) {
                $deps[] = [
                    "component" => "components/ILIAS/MetaData",
                    "entity" => "md",
                    "ids" => $md_ids
                ];
            }

            return $deps;
        }
        return array();
    }

    protected function getActiveAdvMDRecords(int $a_id): array
    {
        $active = [];
        foreach (ilObject::_getAllReferences($a_id) as $ref_id) {
            foreach ($this->metadata->getActiveAdvMDRecords($ref_id) as $rec) {
                $active[$rec->getRecordId()] = $rec;
            }
        }
        return $active;

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

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ): string {
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "9.0.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Glossary/htlm/9_0",
                "xsd_file" => "ilias_glo_9_0.xsd",
                "uses_dataset" => true,
                "min" => "9.0.0",
                "max" => ""),
            "5.4.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Glossary/htlm/5_4",
                "xsd_file" => "ilias_glo_5_4.xsd",
                "uses_dataset" => true,
                "min" => "5.4.0",
                "max" => ""),
            "5.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Glossary/htlm/5_1",
                "xsd_file" => "ilias_glo_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => ""),
            "4.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Glossary/htlm/4_1",
                "xsd_file" => "ilias_glo_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "")
            );
    }
}
