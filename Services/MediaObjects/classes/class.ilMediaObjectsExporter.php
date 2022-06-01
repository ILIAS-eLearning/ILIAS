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
 * Export2 class for media pools
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaObjectsExporter extends ilXmlExporter
{
    private ilMediaObjectDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilMediaObjectDataSet();
        $this->ds->setDSPrefix("ds");
    }
    
    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ) : array {
        $md_ids = array();
        foreach ($a_ids as $mob_id) {
            $md_ids[] = "0:" . $mob_id . ":mob";
        }

        return array(
            array(
                "component" => "Services/MetaData",
                "entity" => "md",
                "ids" => $md_ids)
            );
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ) : string {
        ilFileUtils::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    /**
     * @return array[]
     */
    public function getValidSchemaVersions(
        string $a_entity
    ) : array {
        return array(
            "5.1.0" => array(
                "namespace" => "https://www.ilias.de/Services/MediaObjects/mob/5_1",
                "xsd_file" => "ilias_mob_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => ""),
            "4.3.0" => array(
                "namespace" => "https://www.ilias.de/Services/MediaObjects/mob/4_3",
                "xsd_file" => "ilias_mob_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => ""),
            "4.1.0" => array(
                "namespace" => "https://www.ilias.de/Services/MediaObjects/mob/4_1",
                "xsd_file" => "ilias_mob_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
