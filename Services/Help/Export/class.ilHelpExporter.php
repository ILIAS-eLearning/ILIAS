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
 * Exporter class for help system information
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelpExporter extends ilXmlExporter
{
    private ilHelpDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilHelpDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        if ($a_entity === "help") {
            $lm_node_ids = array();
            foreach ($a_ids as $lm_id) {
                $chaps = ilLMObject::getObjectList($lm_id, "st");
                foreach ($chaps as $chap) {
                    $lm_node_ids[] = $chap["obj_id"];
                }
            }

            return array(
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

        return array();
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
            "4.3.0" => array(
                "namespace" => "https://www.ilias.de/Services/Help/help/4_3",
                "xsd_file" => "ilias_help_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "")
        );
    }
}
