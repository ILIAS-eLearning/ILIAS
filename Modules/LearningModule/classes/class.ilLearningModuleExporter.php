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
 * Exporter class for html learning modules
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLearningModuleExporter extends ilXmlExporter
{
    private ilLearningModuleDataSet $ds;
    private ilExportConfig $config;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function init(): void
    {
        global $DIC;

        $this->ds = new ilLearningModuleDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getExport()->getConfig("Modules/LearningModule");
        if ($this->config->getMasterLanguageOnly()) {
            $conf = $this->getExport()->getConfig("Services/COPage");
            $conf->setMasterLanguageOnly(true, $this->config->getIncludeMedia());
            $this->ds->setMasterLanguageOnly(true);
        }
        $this->content_style_domain = $DIC->contentStyle()
            ->domain();
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        $deps = array();

        if ($a_entity == "lm") {
            $md_ids = array();

            // lm related ids
            foreach ($a_ids as $id) {
                $md_ids[] = $id . ":0:lm";
            }

            // chapter related ids
            foreach ($a_ids as $id) {
                $chaps = ilLMObject::getObjectList($id, "st");
                foreach ($chaps as $c) {
                    $md_ids[] = $id . ":" . $c["obj_id"] . ":st";
                }
            }

            // page related ids
            $pg_ids = array();
            foreach ($a_ids as $id) {
                $pages = ilLMPageObject::getPageList($id);
                foreach ($pages as $p) {
                    $pg_ids[] = "lm:" . $p["obj_id"];
                    $md_ids[] = $id . ":" . $p["obj_id"] . ":pg";
                }
            }

            // style, multilang, metadata per page/chap?

            $deps = array(
                array(
                    "component" => "Services/COPage",
                    "entity" => "pg",
                    "ids" => $pg_ids),
                array(
                    "component" => "Services/MetaData",
                    "entity" => "md",
                    "ids" => $md_ids),
            );

            if (!$this->config->getMasterLanguageOnly()) {
                $deps[] = array(
                    "component" => "Services/Object",
                    "entity" => "transl",
                    "ids" => $md_ids);
            }
            $deps[] = array(
                "component" => "Services/Object",
                "entity" => "tile",
                "ids" => $a_ids);

            // help export
            foreach ($a_ids as $id) {
                if (ilObjContentObject::isOnlineHelpModule($id, true)) {
                    $deps[] = array(
                        "component" => "Services/Help",
                        "entity" => "help",
                        "ids" => array($id));
                }
            }

            // style
            foreach ($a_ids as $id) {
                $style_id = $this->content_style_domain->styleForObjId($id)->getStyleId();
                if ($style_id > 0) {
                    $deps[] = array(
                        "component" => "Services/Style",
                        "entity" => "sty",
                        "ids" => $style_id
                    );
                }
            }
        }

        return $deps;
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ): string {
        // workaround: old question export
        $q_ids = array();
        $pages = ilLMPageObject::getPageList($a_id);
        foreach ($pages as $p) {
            $langs = array("-");
            if (!$this->config->getMasterLanguageOnly()) {
                $trans = ilPageObject::lookupTranslations("lm", $p["obj_id"]);
                foreach ($trans as $t) {
                    if ($t != "-") {
                        $langs[] = $t;
                    }
                }
            }
            foreach ($langs as $l) {
                // collect questions
                foreach (ilPCQuestion::_getQuestionIdsForPage("lm", $p["obj_id"], $l) as $q_id) {
                    $q_ids[$q_id] = $q_id;
                }
            }
        }
        if (count($q_ids) > 0) {
            $dir = $this->getExport()->export_run_dir;
            $qti_file = fopen($dir . "/qti.xml", "w");
            $pool = new ilObjQuestionPool();
            fwrite($qti_file, $pool->questionsToXML($q_ids));
            fclose($qti_file);
        }

        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getValidSchemaVersions(
        string $a_entity
    ): array {
        return array(
            "5.4.0" => array(
                "namespace" => "https://www.ilias.de/Modules/LearningModule/lm/5_4",
                "xsd_file" => "ilias_lm_5_4.xsd",
                "uses_dataset" => true,
                "min" => "5.4.0",
                "max" => ""),
            "5.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/LearningModule/lm/5_1",
                "xsd_file" => "ilias_lm_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => ""),
            "4.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/LearningModule/lm/4_1",
                "xsd_file" => "ilias_lm_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
