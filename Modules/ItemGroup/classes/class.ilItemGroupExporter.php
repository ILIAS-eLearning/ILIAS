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
 * Exporter class for item groups
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilItemGroupExporter extends ilXmlExporter
{
    private ilItemGroupDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilItemGroupDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
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
        return array(
            "5.3.0" => array(
                "namespace" => "https://www.ilias.de/Modules/ItemGroup/itgr/5_3",
                "xsd_file" => "ilias_itgr_5_3.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => ""),
            "4.3.0" => array(
                "namespace" => "https://www.ilias.de/Modules/ItemGroup/itgr/4_3",
                "xsd_file" => "ilias_itgr_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "")
        );
    }
}
