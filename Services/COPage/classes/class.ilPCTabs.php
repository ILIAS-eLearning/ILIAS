<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCTabs
*
* Tabbed contents (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTabs extends ilPageContent
{
    public $tabs_node;
    const ACCORDION_HOR = "HorizontalAccordion";
    const ACCORDION_VER = "VerticalAccordion";
    const CAROUSEL = "Carousel";

    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("tabs");
    }

    /**
    * Set content node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->tabs_node = $a_node->first_child();		// this is the Tabs node
    }

    /**
    * Create new Tabs node
    */
    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->tabs_node = $this->dom->create_element("Tabs");
        $this->tabs_node = $this->node->append_child($this->tabs_node);
    }

    /**
    * Set attribute of tabs tag
    *
    * @param	string		attribute name
    * @param	string		attribute value
    */
    protected function setTabsAttribute($a_attr, $a_value)
    {
        if (!empty($a_value)) {
            $this->tabs_node->set_attribute($a_attr, $a_value);
        } else {
            if ($this->tabs_node->has_attribute($a_attr)) {
                $this->tabs_node->remove_attribute($a_attr);
            }
        }
    }

    /**
    * Set type of tabs
    *
    * @param	string		$a_type		("HorizontalTabs" | "Accordion")
    */
    public function setTabType($a_type = "HorizontalTabs")
    {
        switch ($a_type) {
            case ilPCTabs::ACCORDION_VER:
            case ilPCTabs::ACCORDION_HOR:
            case ilPCTabs::CAROUSEL:
                $this->tabs_node->set_attribute("Type", $a_type);
                break;
        }
    }

    /**
    * Get type of tabs
    */
    public function getTabType()
    {
        return $this->tabs_node->get_attribute("Type");
    }
    
    /**
    * Set content width
    *
    * @param	int		content width
    */
    public function setContentWidth($a_val)
    {
        $this->setTabsAttribute("ContentWidth", $a_val);
    }
    
    /**
    * Get content width
    *
    * @return	int		content width
    */
    public function getContentWidth()
    {
        return $this->tabs_node->get_attribute("ContentWidth");
    }
    
    /**
    * Set content height
    *
    * @param	int		content height
    */
    public function setContentHeight($a_val)
    {
        $this->setTabsAttribute("ContentHeight", $a_val);
    }
    
    /**
    * Get content height
    *
    * @return	int		content height
    */
    public function getContentHeight()
    {
        return $this->tabs_node->get_attribute("ContentHeight");
    }

    /**
    * Set horizontal align
    *
    * @param	string		horizontal align
    */
    public function setHorizontalAlign($a_val)
    {
        $this->setTabsAttribute("HorizontalAlign", $a_val);
    }
    
    /**
    * Get horizontal align
    *
    * @return	string		horizontal align
    */
    public function getHorizontalAlign()
    {
        return $this->tabs_node->get_attribute("HorizontalAlign");
    }

    /**
    * Set behavior
    *
    * @param	string		behavior
    */
    public function setBehavior($a_val)
    {
        $this->setTabsAttribute("Behavior", $a_val);
    }
    
    /**
    * Get behavior
    *
    * @return	string	behavior
    */
    public function getBehavior()
    {
        return $this->tabs_node->get_attribute("Behavior");
    }
    
    /**
    * Get captions
    */
    public function getCaptions()
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

    /**
    * Get caption
    */
    public function getCaption($a_hier_id, $a_pc_id)
    {
        $captions = array();
        $tab_nodes = $this->tabs_node->child_nodes();
        $k = 0;
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
    public function savePositions($a_pos)
    {
        asort($a_pos);
        
        // File Item
        $childs = $this->tabs_node->child_nodes();
        $nodes = array();
        for ($i=0; $i<count($childs); $i++) {
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

    /**
    * Add Tab items
    */
    public function saveCaptions($a_captions)
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

    /**
    * Save positions of tabs
    */
    public function deleteTab($a_hier_id, $a_pc_id)
    {
        // File Item
        $childs = $this->tabs_node->child_nodes();
        $nodes = array();
        for ($i=0; $i<count($childs); $i++) {
            if ($childs[$i]->node_name() == "Tab") {
                if ($a_pc_id == $childs[$i]->get_attribute("PCID") &&
                    $a_hier_id == $childs[$i]->get_attribute("HierId")) {
                    $childs[$i]->unlink($childs[$i]);
                }
            }
        }
    }

    /**
    * Add a tab
    */
    public function addTab($a_caption)
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
    
    /**
    * Set template
    *
    * @param	string	$a_template		template
    */
    public function setTemplate($a_template)
    {
        $this->setTabsAttribute("Template", $a_template);
    }

    /**
    * Get template
    *
    * @return	string		template
    */
    public function getTemplate()
    {
        return $this->tabs_node->get_attribute("Template");
    }

    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("pc_vacc", "pc_hacc", "pc_carousel");
    }


    /**
      * Set auto animation waiting time
      *
      * @param int $a_val auto animation time
      */
    public function setAutoTime($a_val)
    {
        $this->setTabsAttribute("AutoAnimWait", $a_val);
    }

    /**
     * Get auto animation waiting time
     *
     * @return int auto animation time
     */
    public function getAutoTime()
    {
        return $this->tabs_node->get_attribute("AutoAnimWait");
    }

    /**
     * Set random start
     *
     * @param bool $a_val random start
     */
    public function setRandomStart($a_val)
    {
        $this->setTabsAttribute("RandomStart", $a_val);
    }

    /**
     * Get random start
     *
     * @return bool random start
     */
    public function getRandomStart()
    {
        return $this->tabs_node->get_attribute("RandomStart");
    }

    /**
     * Get Javascript files
     */
    public function getJavascriptFiles($a_mode)
    {
        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
        return ilAccordionGUI::getLocalJavascriptFiles();
    }

    /**
     * Get Javascript files
     */
    public function getCssFiles($a_mode)
    {
        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
        return ilAccordionGUI::getLocalCssFiles();
    }
}
