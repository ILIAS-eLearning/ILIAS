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
    protected \ILIAS\Help\InternalDomainService $help_domain;

    public function init(): void
    {
        global $DIC;

        $this->ds = new ilHelpDataSet();
        $this->ds->initByExporter($this);
        $this->ds->setDSPrefix("ds");
        $this->help_domain = $DIC->help()->internal()->domain();
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
                    "component" => "components/ILIAS/Help",
                    "entity" => "help_map",
                    "ids" => $lm_node_ids),
                array(
                    "component" => "components/ILIAS/Help",
                    "entity" => "help_tooltip",
                    "ids" => $a_ids)
                );
        }

        if ($a_entity === "gdtr") {
            $res = [];
            // step pages
            $pg_ids = [];
            $step_ids = [];
            foreach ($a_ids as $id) {
                foreach ($this->help_domain->guidedTour()->step()->getStepsOfTour((int) $id) as $step) {
                    $pg_ids[] = "gdtr:" . $step->getId();
                    $step_ids[] = $step->getId();
                }
            }
            if (count($pg_ids)) {
                $res[] = [
                    "component" => "components/ILIAS/Help",
                    "entity" => "gdtr_step",
                    "ids" => $step_ids];
                $res[] = array(
                    "component" => "components/ILIAS/COPage",
                    "entity" => "pg",
                    "ids" => $pg_ids
                );
            }
            return $res;
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
            "10.0" => array(
                "namespace" => "https://www.ilias.de/Services/Help/help/10_0",
                "xsd_file" => "ilias_help_10.xsd",
                "uses_dataset" => true,
                "min" => "10.0",
                "max" => ""),
            "4.3.0" => array(
                "namespace" => "https://www.ilias.de/Services/Help/help/4_3",
                "xsd_file" => "ilias_help_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "")
        );
    }
}
