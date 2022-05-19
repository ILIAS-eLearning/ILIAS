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
    // Names of active plugins with own importers for additional data
    protected array $importer_plugins = array();

    public function init() : void
    {
        global $DIC;
        /** @var ilComponentRepository $component_repository */
        $component_repository = $DIC["component.repository"];

        $this->ds = new ilCOPageDataSet();
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getImport()->getConfig("Services/COPage");

        $this->log = ilLoggerFactory::getLogger('copg');

        // collect all page component plugins that have their own exporter
        foreach ($component_repository->getPluginSlotById("pgcp")->getActivePlugins() as $plugin) {
            $plugin_name = $plugin->getName();
            if ($plugin->supportsExport()) {
                require_once('Customizing/global/plugins/Services/COPage/PageComponent/'
                    . $plugin_name . '/classes/class.il' . $plugin_name . 'Importer.php');

                $this->importer_plugins[] = $plugin_name;
            }
        }
    }
    
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
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
                            $this->extractPluginProperties($page);
                        } else {
                            $new_page = ilPageObjectFactory::getInstance($id[0]);
                            $new_page->setImportMode(true);
                            $new_page->setId($id[1]);
                            if ($lstr != "" && $lstr != "-") {
                                $new_page->setLanguage($lstr);
                            }
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
                            $this->extractPluginProperties($new_page);
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
    ) : void {
        $this->log->debug("start");
        $pages = $a_mapping->getMappingsOfEntity("Services/COPage", "pgl");
        $media_objects = $a_mapping->getMappingsOfEntity("Services/MediaObjects", "mob");
        $file_objects = $a_mapping->getMappingsOfEntity("Modules/File", "file");

        $ref_mapping = $a_mapping->getMappingsOfEntity('Services/Container', 'refs');

        //if (count($media_objects) > 0 || count($file_objects) > 0)
        //{
        foreach ($pages as $p) {
            $id = explode(":", $p);
            if (count($id) == 3) {
                if (ilPageObject::_exists($id[0], $id[1], $id[2], true)) {
                    /** @var ilPageObject $new_page */
                    $new_page = ilPageObjectFactory::getInstance($id[0], $id[1], 0, $id[2]);
                    $new_page->buildDom();
                    $med = $new_page->resolveMediaAliases($media_objects, $this->config->getReuseOriginallyExportedMedia());
                    $fil = $new_page->resolveFileItems($file_objects);
                    $new_page->resolveResources($ref_mapping);
                    $il = false;
                    if (!$this->config->getSkipInternalLinkResolve()) {
                        $il = $new_page->resolveIntLinks();
                        $this->log->debug("resolve internal link for page " . $id[0] . "-" . $id[1] . "-" . $id[2]);
                    }
                    $plug = $this->replacePluginProperties($new_page);
                    if ($med || $fil || $il || $plug) {
                        $new_page->update(false, true);
                    }
                }
            }
        }
        //}
        $this->log->debug("end");
    }

    /**
     * Extract the properties of the plugged page contents
     * The page XML is scanned for plugged contents with own importers
     *
     * Called from importXmlRepresentation() for each handled page object
     * Extracted data is used by plugin importers afterwards
     */
    protected function extractPluginProperties(
        ilPageObject $a_page
    ) : void {
        if (empty($this->importer_plugins)) {
            return;
        }

        $a_page->buildDom();
        $domdoc = $a_page->getDomDoc();
        $xpath = new DOMXPath($domdoc);
        $nodes = $xpath->query("//PageContent[child::Plugged]");

        /** @var DOMElement $pcnode */
        foreach ($nodes as $pcnode) {
            // page content id (unique in the page)
            $pc_id = $pcnode->getAttribute('PCID');
            $plnode = $pcnode->childNodes->item(0);
            $plugin_name = $plnode->getAttribute('PluginName');
            $plugin_version = $plnode->getAttribute('PluginVersion');

            // additional data will be imported
            if (in_array($plugin_name, $this->importer_plugins)) {
                // get the id of the mapped plugged page content
                $id = $a_page->getParentType()
                    . ':' . $a_page->getId()
                    . ':' . $a_page->getLanguage()
                    . ':' . $pc_id;

                $properties = array();
                /** @var DOMElement $child */
                foreach ($plnode->childNodes as $child) {
                    $properties[$child->getAttribute('Name')] = $child->nodeValue;
                }

                // statical provision of content to the pluged importer classes
                ilPageComponentPluginImporter::setPCVersion($id, $plugin_version);
                ilPageComponentPluginImporter::setPCProperties($id, $properties);
            }
        }
    }

    /**
     * Replace the properties of the plugged page contents
     * The page XML is scanned for plugged contents with own importers
     * The pluged content is replace
     *
     * Called finalProcessing() for each handled page
     * Extracted data is used by dependent plugin importers afterwards
     */
    public function replacePluginProperties(
        ilPageObject $a_page
    ) : bool {
        if (empty($this->importer_plugins)) {
            return false;
        }

        $a_page->buildDom();
        $domdoc = $a_page->getDomDoc();
        $xpath = new DOMXPath($domdoc);
        $nodes = $xpath->query("//PageContent[child::Plugged]");

        $modified = false;

        /** @var DOMElement $pcnode */
        foreach ($nodes as $pcnode) {
            // page content id (unique in the page)
            $pc_id = $pcnode->getAttribute('PCID');
            $plnode = $pcnode->childNodes->item(0);
            $plugin_name = $plnode->getAttribute('PluginName');

            // get the id of the mapped plugged page content
            $id = $a_page->getParentType()
                . ':' . $a_page->getId()
                . ':' . $a_page->getLanguage()
                . ':' . $pc_id;

            $plugin_version = ilPageComponentPluginImporter::getPCVersion($id);
            $properties = ilPageComponentPluginImporter::getPCProperties($id);

            // update the version if modified by the plugin importer
            if (isset($plugin_version)) {
                $plnode->setAttribute('PluginVersion', $plugin_version);
                $modified = true;
            }

            // update the properties if modified by the plugin importer
            if (is_array($properties)) {
                /** @var DOMElement $child */
                foreach ($plnode->childNodes as $child) {
                    $plnode->removeChild($child);
                }
                foreach ($properties as $name => $value) {
                    $child = new DOMElement('PluggedProperty', $value);
                    $plnode->appendChild($child);
                    $child->setAttribute('Name', $name);
                }
                $modified = true;
            }
        }

        return $modified;
    }
}
