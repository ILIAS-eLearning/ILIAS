<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for pages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesMediaPool
 */
class ilCOPageImporter extends ilXmlImporter
{
    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var ilCOPageDataSet
     */
    protected $ds;

    /**
     * Names of active plugins with own importers for additional data
     * @var array
     */
    protected $importer_plugins = array();

    /**
     * Initialisation
     */
    public function init()
    {
        global $DIC;
        /** @var ilPluginAdmin $ilPluginAdmin */
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        include_once("./Services/COPage/classes/class.ilCOPageDataSet.php");
        $this->ds = new ilCOPageDataSet();
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getImport()->getConfig("Services/COPage");

        $this->log = ilLoggerFactory::getLogger('copg');

        // collect all page component plugins that have their own exporter
        require_once('Services/COPage/classes/class.ilPageComponentPluginImporter.php');
        foreach (ilPluginAdmin::getActivePluginsForSlot(IL_COMP_SERVICE, "COPage", "pgcp") as $plugin_name) {
            if ($ilPluginAdmin->supportsExport(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name)) {
                require_once('Customizing/global/plugins/Services/COPage/PageComponent/'
                    . $plugin_name . '/classes/class.il' . $plugin_name . 'Importer.php');

                $this->importer_plugins[] = $plugin_name;
            }
        }
    }
    
    
    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $this->log->debug("entity: " . $a_entity . ", id: " . $a_id);

        if ($a_entity == "pgtp") {
            include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
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
                    include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");

                    while (substr($a_xml, 0, 11) == "<PageObject") {
                        $l1 = strpos($a_xml, ">");

                        $page_tag = "<?xml version='1.0'?> " . substr($a_xml, 0, $l1+1) . "</PageObject>";
                        $page_data = simplexml_load_string($page_tag);
                        $lstr = $page_data['Language'];
                        $p = strpos($a_xml, "</PageObject>") + 13;
                        $next_xml = "<PageObject>" . substr($a_xml, $l1+1, $p - $l1 -1);

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
                                $new_page->setActive($page_data["Active"]);
                            }
                            $new_page->setActivationStart($page_data["ActivationStart"]);
                            $new_page->setActivationEnd($page_data["ActivationEnd"]);
                            $new_page->setShowActivationInfo($page_data["ShowActivationInfo"]);
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

    /**
     * Final processing
     *
     * @param	array		mapping array
     */
    public function finalProcessing($a_mapping)
    {
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
                include_once("./Services/COPage/classes/class.ilPageObject.php");
                if (ilPageObject::_exists($id[0], $id[1], $id[2], true)) {
                    include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");

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
     *
     * @param ilPageObject $a_page
     */
    protected function extractPluginProperties($a_page)
    {
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
     *
     * @param ilPageObject $a_page
     * @return bool	page is modified
     */
    public function replacePluginProperties($a_page)
    {
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
