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
class ilMediaPoolExporter extends ilXmlExporter
{
    private ilMediaPoolDataSet $ds;
    private ilExportConfig $config;

    public function init(): void
    {
        $this->ds = new ilMediaPoolDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getExport()->getConfig("Modules/MediaPool");
        if ($this->config->getMasterLanguageOnly()) {
            $conf = $this->getExport()->getConfig("Services/COPage");
            $conf->setMasterLanguageOnly(true, $this->config->getIncludeMedia());
            $this->ds->setMasterLanguageOnly(true);
        }
    }

    public function getXmlExportHeadDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        $mob_ids = array();

        foreach ($a_ids as $id) {
            $m_ids = ilObjMediaPool::getAllMobIds($id);
            foreach ($m_ids as $m) {
                $mob_ids[] = $m;
            }
        }

        if ($this->config->getMasterLanguageOnly()) {
            return array();
        }

        return array(
            array(
                "component" => "Services/MediaObjects",
                "entity" => "mob",
                "ids" => $mob_ids)
            );
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        $pg_ids = array();

        foreach ($a_ids as $id) {
            $pages = ilMediaPoolItem::getIdsForType($id, "pg");
            foreach ($pages as $p) {
                $pg_ids[] = "mep:" . $p;
            }
        }

        $deps = array(
            array(
                "component" => "Services/COPage",
                "entity" => "pg",
                "ids" => $pg_ids)
            );

        if (!$this->config->getMasterLanguageOnly()) {
            $deps[] = array(
                "component" => "Services/Object",
                "entity" => "transl",
                "ids" => $a_ids);
        }

        $deps[] = array(
            "component" => "Services/Object",
            "entity" => "tile",
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
            "5.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/MediaPool/mep/5_1",
                "xsd_file" => "ilias_mep_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => ""),
            "4.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/MediaPool/mep/4_1",
                "xsd_file" => "ilias_mep_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "")
                );
    }
}
