<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for meta data
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesCOPage
 */
class ilCOPageExporter extends ilXmlExporter
{
    private $ds;
    /**
     * @var ilCOPageExportConfig
     */
    protected $config;

    /**
     * List of dependencies for page component plugins with an own exporter
     *
     * The list of ids in the dependency definition has the following format:
     * 		<parent_type>:<page_id>:<lang>:<pc_id>
     *
     * The implementation assumes the following call sequence of methods
     * to avoid a multiple instatiation of page objects
     * 1. init()
     * 2. getXmlRepresentation()
     * 3. getXmlExportTailDependencies()
     *
     *
     * @var array  	plugin_name => depencency definition array
     */
    protected $plugin_dependencies = array();

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
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getExport()->getConfig("Services/COPage");
        if ($this->config->getMasterLanguageOnly()) {
            $this->ds->setMasterLanguageOnly(true);
        }

        // collect all page component plugins that have their own exporter
        require_once('Services/COPage/classes/class.ilPageComponentPluginExporter.php');
        foreach (ilPluginAdmin::getActivePluginsForSlot(IL_COMP_SERVICE, "COPage", "pgcp") as $plugin_name) {
            if ($ilPluginAdmin->supportsExport(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name)) {
                require_once('Customizing/global/plugins/Services/COPage/PageComponent/'
                    . $plugin_name . '/classes/class.il' . $plugin_name . 'Exporter.php');

                $this->plugin_dependencies[$plugin_name] = array(
                    "component" => "Plugins/" . $plugin_name,
                    "entity" => "pgcp",
                    "ids" => array()
                );
            }
        }
    }


    /**
     * Get head dependencies
     *
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
    {
        if ($a_entity == "pg") {
            // get all media objects and files of the page
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
            include_once("./Modules/File/classes/class.ilObjFile.php");
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
    
    /**
     * Get tail dependencies
     *
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
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

        if (!empty($this->plugin_dependencies)) {
            // use numeric keys instead plugin names
            return array_values($this->plugin_dependencies);
        }

        return array();
    }


    /**
     * Get xml representation
     *
     * @param string	entity
     * @param string	schema version
     * @param array		ids
     * @return string	xml
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        if ($a_entity == "pg") {
            include_once("./Services/COPage/classes/class.ilPageObject.php");
            
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

            include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
            $xml = "";
            foreach ($langs as $l) {
                $page_object = ilPageObjectFactory::getInstance($id[0], $id[1], 0, $l);
                $page_object->buildDom();
                $page_object->insertInstIntoIDs(IL_INST_ID);
                $this->extractPluginProperties($page_object);
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
            return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
        }
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     *
     * @return
     */
    public function getValidSchemaVersions($a_entity)
    {
        if ($a_entity == "pg") {
            return array(
                "4.2.0" => array(
                    "namespace" => "http://www.ilias.de/Services/COPage/pg/4_2",
                    "xsd_file" => "ilias_pg_4_2.xsd",
                    "min" => "4.2.0",
                    "max" => ""),
                "4.1.0" => array(
                    "namespace" => "http://www.ilias.de/Services/COPage/pg/4_1",
                    "xsd_file" => "ilias_pg_4_1.xsd",
                    "min" => "4.1.0",
                    "max" => "4.1.99")
            );
        }
        if ($a_entity == "pgtp") {
            return array(
                "4.2.0" => array(
                    "namespace" => "http://www.ilias.de/Services/COPage/pgtp/4_1",
                    "xsd_file" => "ilias_pgtp_4_1.xsd",
                    "uses_dataset" => true,
                    "min" => "4.2.0",
                    "max" => "")
            );
        }
    }

    /**
     * Extract the properties of the plugged page contents
     * The page XML is scanned for plugged contents with own exporters
     * Their ids are added as dependencies
     *
     * Called from getXmlRepresentation() for each handled page object
     * Extracted data is used by dependent exporters afterwards
     *
     * @param ilPageObject $a_page
     */
    protected function extractPluginProperties($a_page)
    {
        if (empty($this->plugin_dependencies)) {
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

            // dependency should be exported
            if (isset($this->plugin_dependencies[$plugin_name])) {
                // construct a unique dependency id of the plugged page content
                $id = $a_page->getParentType()
                    . ':' . $a_page->getId()
                    . ':' . $a_page->getLanguage()
                    . ':' . $pc_id;

                $properties = array();
                /** @var DOMElement $child */
                foreach ($plnode->childNodes as $child) {
                    $properties[$child->getAttribute('Name')] = $child->nodeValue;
                }

                // statical provision of content to the exporter classes
                ilPageComponentPluginExporter::setPCVersion($id, $plugin_version);
                ilPageComponentPluginExporter::setPCProperties($id, $properties);

                // each plugin exporter gets only the ids of its own content
                $this->plugin_dependencies[$plugin_name]['ids'][] = $id;
            }
        }
    }
}
