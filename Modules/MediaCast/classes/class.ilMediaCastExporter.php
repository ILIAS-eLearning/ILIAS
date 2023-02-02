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
 * Exporter class for media casts
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaCastExporter extends ilXmlExporter
{
    private ilMediaCastDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilMediaCastDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        $news_ids = [];
        foreach ($a_ids as $id) {
            $mcst = new ilObjMediaCast($id, false);
            $items = $mcst->readItems(true);
            foreach ($items as $i) {
                $news_ids[] = $i["id"];
            }
        }

        $deps = [];

        $deps[] = [
            "component" => "Services/News",
            "entity" => "news",
            "ids" => $news_ids
        ];

        // common object properties
        $deps[] = array(
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids);

        return $deps;
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
            "8.0" => array(
                "namespace" => "https://www.ilias.de/Modules/MediaCast/mcst/8",
                "xsd_file" => "ilias_mcst_8.xsd",
                "uses_dataset" => true,
                "min" => "8.0",
                "max" => ""),
            "5.0.0" => array(
                "namespace" => "https://www.ilias.de/Modules/MediaCast/mcst/5_0",
                "xsd_file" => "ilias_mcst_5_0.xsd",
                "uses_dataset" => true,
                "min" => "5.0.0",
                "max" => "5.4.99"),
            "4.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/MediaCast/mcst/4_1",
                "xsd_file" => "ilias_mcst_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
