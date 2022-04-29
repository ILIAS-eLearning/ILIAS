<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilPCPlugged
 * Plugged content object (see ILIAS DTD)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCPlugged extends ilPageContent
{
    protected ilLanguage $lng;
    public php4DOMElement $plug_node;
    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;

    public function init() : void
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setType("plug");
        $this->component_repository = $DIC["component.repository"];
        $this->component_factory = $DIC["component.factory"];
    }

    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->plug_node = $a_node->first_child();		// this is the Plugged node
    }

    /**
     * Create plugged node in xml.
     */
    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id,
        string $a_plugin_name,
        string $a_plugin_version
    ) : void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->plug_node = $this->dom->create_element("Plugged");
        $this->plug_node = $this->node->append_child($this->plug_node);
        $this->plug_node->set_attribute("PluginName", $a_plugin_name);
        $this->plug_node->set_attribute("PluginVersion", $a_plugin_version);
    }

    /**
     * Set properties of plugged component.
     */
    public function setProperties(array $a_properties) : void
    {
        if (!is_object($this->plug_node)) {
            return;
        }
        
        // delete properties
        $children = $this->plug_node->child_nodes();
        for ($i = 0; $i < count($children); $i++) {
            $this->plug_node->remove_child($children[$i]);
        }
        // set properties
        foreach ($a_properties as $key => $value) {
            $prop_node = $this->dom->create_element("PluggedProperty");
            $prop_node = $this->plug_node->append_child($prop_node);
            $prop_node->set_attribute("Name", $key);
            if ($value != "") {
                $prop_node->set_content($value);
            }
        }
    }

    /**
     * Get properties of plugged component
     */
    public function getProperties() : array
    {
        $properties = array();
        
        if (is_object($this->plug_node)) {
            // delete properties
            $children = $this->plug_node->child_nodes();
            for ($i = 0; $i < count($children); $i++) {
                if ($children[$i]->node_name() == "PluggedProperty") {
                    $properties[$children[$i]->get_attribute("Name")] =
                        $children[$i]->get_content();
                }
            }
        }
        
        return $properties;
    }
    
    public function setPluginVersion(string $a_version) : void
    {
        if (!empty($a_version)) {
            $this->plug_node->set_attribute("PluginVersion", $a_version);
        } else {
            if ($this->plug_node->has_attribute("PluginVersion")) {
                $this->plug_node->remove_attribute("PluginVersion");
            }
        }
    }

    public function getPluginVersion() : string
    {
        if (is_object($this->plug_node)) {
            return $this->plug_node->get_attribute("PluginVersion");
        }
        return "";
    }

    public function setPluginName(string $a_name) : void
    {
        if (!empty($a_name)) {
            $this->plug_node->set_attribute("PluginName", $a_name);
        } else {
            if ($this->plug_node->has_attribute("PluginName")) {
                $this->plug_node->remove_attribute("PluginName");
            }
        }
    }

    public function getPluginName() : string
    {
        if (is_object($this->plug_node)) {
            return $this->plug_node->get_attribute("PluginName");
        }
        return "";
    }

    /**
     * Handle copied plugged content. This function must, e.g. create copies of
     * objects referenced within the content (e.g. question objects)
     */
    public static function handleCopiedPluggedContent(
        ilPageObject $a_page,
        DOMDocument $a_domdoc
    ) : void {
        global $DIC;
        $component_repository = $DIC['component.repository'];
        $component_factory = $DIC['component.factory'];

        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query("//Plugged");

        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            $plugin_name = $node->getAttribute('PluginName');
            $plugin_version = $node->getAttribute('PluginVersion');

            $plugin_info = $component_repository->getPluginByName($plugin_name);
            if ($plugin_info->isActive()) {
                /** @var ilPageComponentPlugin $plugin_obj */
                $plugin_obj = $component_factory->getPlugin($plugin_info->getId());
                $plugin_obj->setPageObj($a_page);

                $properties = array();
                /** @var DOMElement $child */
                foreach ($node->childNodes as $child) {
                    $properties[$child->getAttribute('Name')] = $child->nodeValue;
                }

                // let the plugin copy additional content
                // and allow it to modify the saved parameters
                $plugin_obj->onClone($properties, $plugin_version);

                foreach ($node->childNodes as $child) {
                    $node->removeChild($child);
                }
                foreach ($properties as $name => $value) {
                    $child = new DOMElement('PluggedProperty', $value);
                    $node->appendChild($child);
                    $child->setAttribute('Name', $name);
                }
            }
        }
    }

    /**
     * After repository (container) copy action
     */
    public static function afterRepositoryCopy(ilPageObject $page, array $mapping, int $source_ref_id) : void
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $xpath = new DOMXPath($page->getDomDoc());
        $nodes = $xpath->query("//Plugged");

        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            $plugin_name = $node->getAttribute('PluginName');
            $plugin_version = $node->getAttribute('PluginVersion');

            if ($ilPluginAdmin->isActive(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name)) {
                /** @var ilPageComponentPlugin $plugin_obj */
                $plugin_obj = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name);
                $plugin_obj->setPageObj($page);

                $properties = array();
                /** @var DOMElement $child */
                foreach ($node->childNodes as $child) {
                    $properties[$child->getAttribute('Name')] = $child->nodeValue;
                }

                // let the plugin copy additional content
                // and allow it to modify the saved parameters
                $plugin_obj->afterRepositoryCopy($properties, $mapping, $source_ref_id, $plugin_version);

                foreach ($node->childNodes as $child) {
                    $node->removeChild($child);
                }
                foreach ($properties as $name => $value) {
                    $child = new DOMElement('PluggedProperty', $value);
                    $node->appendChild($child);
                    $child->setAttribute('Name', $name);
                }
            }
        }
    }

    /**
     * Handle deleted plugged content. This function must, e.g. delete
     * objects referenced within the content (e.g. question objects)
     */
    public static function handleDeletedPluggedNode(
        ilPageObject $a_page,
        DOMNode $a_node
    ) : void {
        global $DIC;
        $component_repository = $DIC['component.repository'];
        $component_factory = $DIC['component.factory'];

        $plugin_name = $a_node->getAttribute('PluginName');
        $plugin_version = $a_node->getAttribute('PluginVersion');

        $plugin_info = $component_repository->getPluginByName($plugin_name);
        if ($plugin_info->isActive()) {
            /** @var ilPageComponentPlugin $plugin_obj */
            $plugin_obj = $component_factory->getPlugin($plugin_info->getId());
            $plugin_obj->setPageObj($a_page);

            $properties = array();
            /** @var DOMElement $child */
            foreach ($a_node->childNodes as $child) {
                $properties[$child->getAttribute('Name')] = $child->nodeValue;
            }

            // let the plugin delete additional content
            $plugin_obj->onDelete($properties, $plugin_version);
        }
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ) : string {
        $lng = $this->lng;

        $end = 0;
        $start = strpos($a_output, "{{{{{Plugged<pl");
        //echo htmlentities($a_html)."-";
        if (is_int($start)) {
            $end = strpos($a_output, "}}}}}", $start);
        }

        while ($end > 0) {
            $param = substr($a_output, $start + 5, $end - $start - 5);
            $param = str_replace(' xmlns:xhtml="http://www.w3.org/1999/xhtml"', "", $param);
            $param = explode("<pl/>", $param);
            //var_dump($param); exit;
            $plugin_name = $param[1];
            $plugin_version = $param[2];
            $properties = array();

            for ($i = 3; $i < count($param); $i += 2) {
                $properties[$param[$i]] = $param[$i + 1];
            }
            
            // get html from plugin
            if ($a_mode == "edit") {
                $plugin_html = '<div class="ilBox">' . $lng->txt("content_plugin_not_activated") . " (" . $plugin_name . ")</div>";
            }

            $plugin_info = $this->component_repository->getPluginByName($plugin_name);
            $plugin_html = '';
            if ($plugin_info->isActive()) {
                $plugin_obj = $this->component_factory->getPlugin($plugin_info->getId());
                $plugin_obj->setPageObj($this->getPage());
                $gui_obj = $plugin_obj->getUIClassInstance();
                $plugin_html = $gui_obj->getElementHTML($a_mode, $properties, $plugin_version);
            }
            
            $a_output = substr($a_output, 0, $start) .
                $plugin_html .
                substr($a_output, $end + 5);

            if (strlen($a_output) > $start + 5) {
                $start = strpos($a_output, "{{{{{Plugged<pl", $start + 5);
            } else {
                $start = false;
            }
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_output, "}}}}}", $start);
            }
        }
                
        return $a_output;
    }
    
    public function getJavascriptFiles(string $a_mode) : array
    {
        $js_files = array();
        
        foreach ($this->component_factory->getActivePluginsInSlot("pgcp") as $plugin) {
            $plugin->setPageObj($this->getPage());
            $pl_dir = $plugin->getDirectory();
            
            $pl_js_files = $plugin->getJavascriptFiles($a_mode);
            foreach ($pl_js_files as $pl_js_file) {
                if (!is_int(strpos($pl_js_file, "//"))) {
                    $pl_js_file = $pl_dir . "/" . $pl_js_file;
                }
                if (!in_array($pl_js_file, $js_files)) {
                    $js_files[] = $pl_js_file;
                }
            }
        }
        //var_dump($js_files);
        return $js_files;
    }
    
    public function getCssFiles(string $a_mode) : array
    {
        $css_files = array();
        
        foreach ($this->component_factory->getActivePluginsInSlot("pgcp") as $plugin) {
            $plugin->setPageObj($this->getPage());
            $pl_dir = $plugin->getDirectory();
            
            $pl_css_files = $plugin->getCssFiles($a_mode);
            foreach ($pl_css_files as $pl_css_file) {
                if (!is_int(strpos($pl_css_file, "//"))) {
                    $pl_css_file = $pl_dir . "/" . $pl_css_file;
                }
                if (!in_array($pl_css_file, $css_files)) {
                    $css_files[] = $pl_css_file;
                }
            }
        }

        return $css_files;
    }
}
