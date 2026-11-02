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
 * Exporter class for object groups
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjectGroupExporter extends ilXmlExporter
{
    private ilObjectGroupDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilObjectGroupDataSet();
        $this->ds->initByExporter($this);
        $this->ds->setDSPrefix("ds");
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
        return [
            "10.12" => [
                "uses_dataset" => true,
                "min" => "10.12",
                "max" => "",
            ],
            "5.3.0" => [
                "namespace" => "https://www.ilias.de/Modules/ObjectGroup/obgr/5_3",
                "xsd_file" => "ilias_obgr_5_3.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => "",
            ],
            "4.3.0" => [
                "namespace" => "https://www.ilias.de/Modules/ObjectGroup/obgr/4_3",
                "xsd_file" => "ilias_obgr_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "",
            ],
        ];
    }
}
