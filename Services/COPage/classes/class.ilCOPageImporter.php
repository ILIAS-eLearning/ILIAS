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
 * Importer class for pages
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCOPageImporter extends ilXmlImporter
{
    protected ilImportConfig $config;
    protected ilLogger $log;
    protected ilCOPageDataSet $ds;
    private ilPageComponentPluginExportImportStore $plugin_store;

    public function init(): void
    {
        global $DIC;
        /** @var ilComponentRepository $component_repository */
        $component_repository = $DIC["component.repository"];

        $this->ds = new ilCOPageDataSet();
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getImport()->getConfig("Services/COPage");

        $this->log = ilLoggerFactory::getLogger('copg');

        $this->plugin_store = ilPageComponentPluginExportImportStore::getInstance();
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $this->log->debug("entity: " . $a_entity . ", id: " . $a_id);

        if ($a_entity == "pgtp") {
            $parser = new ilDataSetImportParser(
                $a_entity,
                $this->getSchemaVersion(),
                $a_xml,
                $this->ds,
                $a_mapping
            );
        }

        if ($a_entity == "pg") {
            $pg_id = $a_mapping->getMapping("Services/COPage", "pg", $a_id);

            $this->log->debug("mapping id: " . $pg_id);
            if ($pg_id != "") {
                $id = explode(":", $pg_id);
                if (count($id) == 2) {
                    while (substr($a_xml, 0, 11) == "<PageObject") {
                        $l1 = strpos($a_xml, ">");

                        $page_tag = "<?xml version='1.0'?> " . substr($a_xml, 0, $l1 + 1) . "</PageObject>";
                        $page_data = simplexml_load_string($page_tag);
                        $lstr = $page_data['Language'];
                        $p = strpos($a_xml, "</PageObject>") + 13;
                        $next_xml = "<PageObject>" . substr($a_xml, $l1 + 1, $p - $l1 - 1);

                        if ($this->config->getForceLanguage() != "") {
                            $lstr = $this->config->getForceLanguage();
                        }
                        if ($lstr == "") {
                            $lstr = "-";
                        }
                        // see bug #0019049
                        $next_xml = str_replace("&amp;", "&", $next_xml);
                        if ($this->config->getUpdateIfExists() && ilPageObject::_exists($id[0], $id[1], $lstr)) {
                            $page = ilPageObjectFactory::getInstance($id[0], $id[1], 0, $lstr);
                            $page->setImportMode(true);
                            $page->setXMLContent($next_xml);
                            $page->updateFromXML();
                            $this->plugin_store->extractPluginProperties($page);
                        } else {
                            // #31937, #39229 (added lang === "-")
                            if ($lstr === "-" && ilPageObject::_exists($id[0], (int) $id[1], "-", true)) {
                                return;
                            }
                            $new_page = ilPageObjectFactory::getInstance($id[0]);
                            $new_page->setImportMode(true);
                            $new_page->setId($id[1]);
                            if ($lstr != "" && $lstr != "-") {
                                $new_page->setLanguage($lstr);
                            }
                            $this->log->debug(">>> CREATE PAGE " . $id[0] . ":" . $id[1]);
                            $new_page->setXMLContent($next_xml);
                            $new_page->setActive(true);
                            // array_key_exists does NOT work on simplexml!
                            if (isset($page_data["Active"])) {
                                $new_page->setActive((string) $page_data["Active"]);
                            }
                            $new_page->setActivationStart($page_data["ActivationStart"]);
                            $new_page->setActivationEnd($page_data["ActivationEnd"]);
                            $new_page->setShowActivationInfo((string) $page_data["ShowActivationInfo"]);
                            $new_page->createFromXML();
                            $this->plugin_store->extractPluginProperties($new_page);
                        }

                        $a_xml = substr($a_xml, $p);
                        if ($lstr == "") {
                            $lstr = "-";
                        }
                        $a_mapping->addMapping("Services/COPage", "pgl", $a_id . ":" . $lstr, $pg_id . ":" . $lstr);
                    }
                }
            }
        }
        $this->log->debug("done");
    }

    public function finalProcessing(
        ilImportMapping $a_mapping
    ): void {
        $this->log->debug("start");
        $pages = $a_mapping->getMappingsOfEntity("Services/COPage", "pgl");
        $media_objects = $a_mapping->getMappingsOfEntity("Services/MediaObjects", "mob");
        $file_objects = $a_mapping->getMappingsOfEntity("Modules/File", "file");

        $ref_mapping = $a_mapping->getMappingsOfEntity('Services/Container', 'refs');

        foreach ($pages as $p) {
            $id = explode(":", $p);
            if (count($id) == 3) {
                if (ilPageObject::_exists($id[0], $id[1], $id[2], true)) {
                    /** @var ilPageObject $new_page */
                    $new_page = ilPageObjectFactory::getInstance($id[0], $id[1], 0, $id[2]);
                    $new_page->buildDom();
                    $med = $new_page->resolveMediaAliases($media_objects, $this->config->getReuseOriginallyExportedMedia());
                    $fil = $new_page->resolveFileItems($file_objects);
                    $res = $new_page->resolveResources($ref_mapping);
                    $il = false;
                    if (!$this->config->getSkipInternalLinkResolve()) {
                        $il = $new_page->resolveIntLinks();
                        $this->log->debug("resolve internal link for page " . $id[0] . "-" . $id[1] . "-" . $id[2]);
                    }
                    $plug = $this->plugin_store->replacePluginProperties($new_page);
                    if ($med || $fil || $il || $plug || $res) {
                        $new_page->update(false, true);
                    }
                }
            }
        }
        $this->log->debug("end");
    }

    public function afterContainerImportProcessing(
        ilImportMapping $a_mapping
    ): void {
    }
}
