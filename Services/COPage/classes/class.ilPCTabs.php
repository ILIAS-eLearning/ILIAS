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
 * Tabbed contents (see ILIAS DTD)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCTabs extends ilPageContent
{
    public const ACCORDION_HOR = "HorizontalAccordion";
    public const ACCORDION_VER = "VerticalAccordion";
    public const CAROUSEL = "Carousel";

    public php4DOMElement $tabs_node;

    public function init() : void
    {
        $this->setType("tabs");
    }

    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->tabs_node = $a_node->first_child();		// this is the Tabs node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) : void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->tabs_node = $this->dom->create_element("Tabs");
        $this->tabs_node = $this->node->append_child($this->tabs_node);
    }

    protected function setTabsAttribute(
        string $a_attr,
        string $a_value
    ) : void {
        if (!empty($a_value)) {
            $this->tabs_node->set_attribute($a_attr, $a_value);
        } else {
            if ($this->tabs_node->has_attribute($a_attr)) {
                $this->tabs_node->remove_attribute($a_attr);
            }
        }
    }

    /**
     * @param	string		$a_type		("HorizontalTabs" | "Accordion")
     */
    public function setTabType(
        string $a_type = "HorizontalTabs"
    ) : void {
        switch ($a_type) {
            case ilPCTabs::ACCORDION_VER:
            case ilPCTabs::ACCORDION_HOR:
            case ilPCTabs::CAROUSEL:
                $this->tabs_node->set_attribute("Type", $a_type);
                break;
        }
    }

    public function getTabType() : string
    {
        return $this->tabs_node->get_attribute("Type");
    }
    
    public function setContentWidth(string $a_val) : void
    {
        $this->setTabsAttribute("ContentWidth", $a_val);
    }
    
    public function getContentWidth() : string
    {
        return $this->tabs_node->get_attribute("ContentWidth");
    }
    
    public function setContentHeight(string $a_val) : void
    {
        $this->setTabsAttribute("ContentHeight", $a_val);
    }
    
    public function getContentHeight() : string
    {
        return $this->tabs_node->get_attribute("ContentHeight");
    }

    public function setHorizontalAlign(string $a_val) : void
    {
        $this->setTabsAttribute("HorizontalAlign", $a_val);
    }
    
    public function getHorizontalAlign() : string
    {
        return $this->tabs_node->get_attribute("HorizontalAlign");
    }

    public function setBehavior(string $a_val) : void
    {
        $this->setTabsAttribute("Behavior", $a_val);
    }
    
    public function getBehavior() : string
    {
        return $this->tabs_node->get_attribute("Behavior");
    }
    
    public function getCaptions() : array
    {
        $captions = array();
        $tab_nodes = $this->tabs_node->child_nodes();
        $k = 0;
        for ($i = 0; $i < count($tab_nodes); $i++) {
            if ($tab_nodes[$i]->node_name() == "Tab") {
                $pc_id = $tab_nodes[$i]->get_attribute("PCID");
                $hier_id = $tab_nodes[$i]->get_attribute("HierId");

                $tab_node_childs = $tab_nodes[$i]->child_nodes();
                $current_caption = "";
                for ($j = 0; $j < count($tab_node_childs); $j++) {
                    if ($tab_node_childs[$j]->node_name() == "TabCaption") {
                        $current_caption = $tab_node_childs[$j]->get_content();
                    }
                }
                $captions[] = array("pos" => $k,
                    "caption" => $current_caption, "pc_id" => $pc_id, "hier_id" => $hier_id);
                $k++;
            }
        }
        
        return $captions;
    }

    public function getCaption(
        string $a_hier_id,
        string $a_pc_id
    ) : string {
        $tab_nodes = $this->tabs_node->child_nodes();
        for ($i = 0; $i < count($tab_nodes); $i++) {
            if ($tab_nodes[$i]->node_name() == "Tab") {
                if ($a_pc_id == $tab_nodes[$i]->get_attribute("PCID") &&
                    ($a_hier_id == $tab_nodes[$i]->get_attribute("HierId"))) {
                    $tab_node_childs = $tab_nodes[$i]->child_nodes();
                    for ($j = 0; $j < count($tab_node_childs); $j++) {
                        if ($tab_node_childs[$j]->node_name() == "TabCaption") {
                            return $tab_node_childs[$j]->get_content();
                        }
                    }
                }
            }
        }
        
        return "";
    }

    /**
     * Save positions of tabs
     */
    public function savePositions(
        array $a_pos
    ) : void {
        asort($a_pos);
        
        // File Item
        $childs = $this->tabs_node->child_nodes();
        $nodes = array();
        for ($i = 0; $i < count($childs); $i++) {
            if ($childs[$i]->node_name() == "Tab") {
                $pc_id = $childs[$i]->get_attribute("PCID");
                $hier_id = $childs[$i]->get_attribute("HierId");
                $nodes[$hier_id . ":" . $pc_id] = $childs[$i];
                $childs[$i]->unlink($childs[$i]);
            }
        }
        
        foreach ($a_pos as $k => $v) {
            if (is_object($nodes[$k])) {
                $nodes[$k] = $this->tabs_node->append_child($nodes[$k]);
            }
        }
    }

    public function saveCaptions(array $a_captions) : void
    {
        // iterate all tab nodes
        $tab_nodes = $this->tabs_node->child_nodes();
        for ($i = 0; $i < count($tab_nodes); $i++) {
            if ($tab_nodes[$i]->node_name() == "Tab") {
                $pc_id = $tab_nodes[$i]->get_attribute("PCID");
                $hier_id = $tab_nodes[$i]->get_attribute("HierId");
                $k = $hier_id . ":" . $pc_id;
                // if caption given, set it, otherwise delete caption subitem
                if ($a_captions[$k] != "") {
                    ilDOMUtil::setFirstOptionalElement(
                        $this->dom,
                        $tab_nodes[$i],
                        "TabCaption",
                        array(),
                        $a_captions[$k],
                        array()
                    );
                } else {
                    ilDOMUtil::deleteAllChildsByName($tab_nodes[$i], array("TabCaption"));
                }
            }
        }
    }

    public function deleteTab(
        string $a_hier_id,
        string $a_pc_id
    ) : void {
        // File Item
        $childs = $this->tabs_node->child_nodes();
        $nodes = array();
        for ($i = 0; $i < count($childs); $i++) {
            if ($childs[$i]->node_name() == "Tab") {
                if ($a_pc_id == $childs[$i]->get_attribute("PCID") &&
                    $a_hier_id == $childs[$i]->get_attribute("HierId")) {
                    $childs[$i]->unlink($childs[$i]);
                }
            }
        }
    }

    public function addTab(string $a_caption) : void
    {
        $new_item = $this->dom->create_element("Tab");
        $new_item = $this->tabs_node->append_child($new_item);
        ilDOMUtil::setFirstOptionalElement(
            $this->dom,
            $new_item,
            "TabCaption",
            array(),
            $a_caption,
            array()
        );
    }
    
    public function setTemplate(string $a_template) : void
    {
        $this->setTabsAttribute("Template", $a_template);
    }

    public function getTemplate() : string
    {
        return $this->tabs_node->get_attribute("Template");
    }

    public static function getLangVars() : array
    {
        return array("pc_vacc", "pc_hacc", "pc_carousel");
    }

    public function setAutoTime(?int $a_val) : void
    {
        $this->setTabsAttribute("AutoAnimWait", (string) $a_val);
    }

    public function getAutoTime() : ?int
    {
        $val = $this->tabs_node->get_attribute("AutoAnimWait");
        if ($val) {
            return (int) $val;
        }
        return null;
    }

    public function setRandomStart(bool $a_val) : void
    {
        $this->setTabsAttribute("RandomStart", $a_val);
    }

    public function getRandomStart() : bool
    {
        return (bool) $this->tabs_node->get_attribute("RandomStart");
    }

    public function getJavascriptFiles(string $a_mode) : array
    {
        return ilAccordionGUI::getLocalJavascriptFiles();
    }

    public function getCssFiles(string $a_mode) : array
    {
        return ilAccordionGUI::getLocalCssFiles();
    }
}
