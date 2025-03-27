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
 * Exporter class for meta data
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCOPageExporter extends ilXmlExporter
{
    private ilCOPageDataSet $ds;
    protected ilExportConfig $config;
    private ilPageComponentPluginExportImportStore $plugin_store;

    /**
     * Initialisation
     */
    public function init(): void
    {
        $this->ds = new ilCOPageDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getExport()->getConfig("Services/COPage");
        if ($this->config->getMasterLanguageOnly()) {
            $this->ds->setMasterLanguageOnly(true);
        }

        $this->plugin_store = ilPageComponentPluginExportImportStore::getInstance();
    }

    public function getXmlExportHeadDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        if ($a_entity == "pg") {
            // get all media objects and files of the page
            $mob_ids = array();
            $file_ids = array();
            foreach ($a_ids as $pg_id) {
                $pg_id = explode(":", $pg_id);

                $lang = ($this->config->getMasterLanguageOnly())
                    ? "-"
                    : "";

                // get media objects
                if ($this->config->getIncludeMedia()) {
                    $mids = ilObjMediaObject::_getMobsOfObject($pg_id[0] . ":pg", $pg_id[1], 0, $lang);
                    foreach ($mids as $mid) {
                        if (ilObject::_lookupType($mid) == "mob") {
                            $mob_ids[] = $mid;
                        }
                    }
                }

                // get files
                $files = ilObjFile::_getFilesOfObject($pg_id[0] . ":pg", $pg_id[1], 0, $lang);
                foreach ($files as $file) {
                    if (ilObject::_lookupType($file) == "file") {
                        $file_ids[] = $file;
                    }
                }
            }

            return array(
                array(
                    "component" => "Services/MediaObjects",
                    "entity" => "mob",
                    "ids" => $mob_ids),
                array(
                    "component" => "Modules/File",
                    "entity" => "file",
                    "ids" => $file_ids)
                );
        }

        return array();
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        if ($a_entity == "pgtp") {
            $pg_ids = array();
            foreach ($a_ids as $id) {
                $pg_ids[] = "stys:" . $id;
            }

            return array(
                array(
                    "component" => "Services/COPage",
                    "entity" => "pg",
                    "ids" => $pg_ids)
                );
        }

        return $this->plugin_store->getPluginDependencies();
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ): string {
        if ($a_entity == "pg") {
            $id = explode(":", $a_id);

            $langs = array("-");
            if (!$this->config->getMasterLanguageOnly()) {
                $trans = ilPageObject::lookupTranslations($id[0], $id[1]);
                foreach ($trans as $t) {
                    if ($t != "-") {
                        $langs[] = $t;
                    }
                }
            }

            $xml = "";
            foreach ($langs as $l) {
                $page_object = ilPageObjectFactory::getInstance($id[0], $id[1], 0, $l);
                $page_object->buildDom();
                $page_object->insertInstIntoIDs(IL_INST_ID);
                $this->plugin_store->extractPluginProperties($page_object);
                $pxml = $page_object->getXMLFromDom(false, false, false, "", true);
                $pxml = str_replace("&", "&amp;", $pxml);
                $a_media = ($this->config->getIncludeMedia())
                    ? ""
                    : 'WithoutMedia="1"';
                $xml .= '<PageObject Language="' . $l . '" Active="' . $page_object->getActive() . '" ActivationStart="' . $page_object->getActivationStart() . '" ActivationEnd="' .
                    $page_object->getActivationEnd() . '" ShowActivationInfo="' . $page_object->getShowActivationInfo() . '" ' . $a_media . '>';
                $xml .= $pxml;
                $xml .= "</PageObject>";
                $page_object->freeDom();
            }

            return $xml;
        }
        if ($a_entity == "pgtp") {
            return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
        }
        return "";
    }

    public function getValidSchemaVersions(
        string $a_entity
    ): array {
        if ($a_entity == "pg") {
            return array(
                "4.2.0" => array(
                    "namespace" => "https://www.ilias.de/Services/COPage/pg/4_2",
                    "xsd_file" => "ilias_pg_4_2.xsd",
                    "min" => "4.2.0",
                    "max" => ""),
                "4.1.0" => array(
                    "namespace" => "https://www.ilias.de/Services/COPage/pg/4_1",
                    "xsd_file" => "ilias_pg_4_1.xsd",
                    "min" => "4.1.0",
                    "max" => "4.1.99")
            );
        }
        if ($a_entity == "pgtp") {
            return array(
                "4.2.0" => array(
                    "namespace" => "https://www.ilias.de/Services/COPage/pgtp/4_1",
                    "xsd_file" => "ilias_pgtp_4_1.xsd",
                    "uses_dataset" => true,
                    "min" => "4.2.0",
                    "max" => "")
            );
        }
        return [];
    }
}
