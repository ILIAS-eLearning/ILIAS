<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCMap
*
* Map content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCMap extends ilPageContent
{
    public $map_node;

    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("map");
    }

    /**
    * Set node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->map_node = $a_node->first_child();		// this is the Map node
    }

    /**
    * Create map node in xml.
    *
    * @param	object	$a_pg_obj		Page Object
    * @param	string	$a_hier_id		Hierarchical ID
    */
    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();

        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->map_node = $this->dom->create_element("Map");
        $this->map_node = $this->node->append_child($this->map_node);
        $this->map_node->set_attribute("Latitude", "0");
        $this->map_node->set_attribute("Longitude", "0");
        $this->map_node->set_attribute("Zoom", "3");
    }

    /**
    * Set latitude of map
    *
    * @param	string	$a_lat		latitude
    */
    public function setLatitude($a_lat)
    {
        if (!empty($a_lat)) {
            $this->map_node->set_attribute("Latitude", $a_lat);
        } else {
            if ($this->map_node->has_attribute("Latitude")) {
                $this->map_node->remove_attribute("Latitude");
            }
        }
    }

    /**
    * Get latitude of map.
    *
    * @return	string		latitude
    */
    public function getLatitude()
    {
        if (is_object($this->map_node)) {
            return $this->map_node->get_attribute("Latitude");
        }
    }

    /**
    * Set longitude of map
    *
    * @param	string	$a_long		longitude
    */
    public function setLongitude($a_long)
    {
        if (!empty($a_long)) {
            $this->map_node->set_attribute("Longitude", $a_long);
        } else {
            if ($this->map_node->has_attribute("Longitude")) {
                $this->map_node->remove_attribute("Longitude");
            }
        }
    }

    /**
    * Get longitude of map.
    *
    * @return	string		longitude
    */
    public function getLongitude()
    {
        if (is_object($this->map_node)) {
            return $this->map_node->get_attribute("Longitude");
        }
    }

    /**
    * Set zoom of map
    *
    * @param	string	$a_zoom		zoom
    */
    public function setZoom($a_zoom)
    {
        if (!empty($a_zoom)) {
            $this->map_node->set_attribute("Zoom", $a_zoom);
        } else {
            if ($this->map_node->has_attribute("Zoom")) {
                $this->map_node->remove_attribute("Zoom");
            }
        }
    }

    /**
    * Get zoom of map.
    *
    * @return	string		zoom
    */
    public function getZoom()
    {
        if (is_object($this->map_node)) {
            return $this->map_node->get_attribute("Zoom");
        }
    }
    
    /**
    * Set Layout
    *
    * @param	integer	$a_width			Width
    * @param	integer	$a_height			Height
    * @param	integer	$a_horizonal_align	Horizontal Alignment
    */
    public function setLayout($a_width, $a_height, $a_horizontal_align)
    {
        if (is_object($this->map_node)) {
            ilDomUtil::setFirstOptionalElement(
                $this->dom,
                $this->map_node,
                "Layout",
                array("MapCaption"),
                "",
                array("Width" => $a_width,
                    "Height" => $a_height, "HorizontalAlign" => $a_horizontal_align)
            );
        }
    }

    /**
    * Get Width.
    *
    * @return	integer	Width
    */
    public function getWidth()
    {
        if (is_object($this->map_node)) {
            $childs = $this->map_node->child_nodes();
            foreach ($childs as $child) {
                if ($child->node_name() == "Layout") {
                    return $child->get_attribute("Width");
                }
            }
        }
    }

    /**
    * Get Height.
    *
    * @return	integer	Height
    */
    public function getHeight()
    {
        if (is_object($this->map_node)) {
            $childs = $this->map_node->child_nodes();
            foreach ($childs as $child) {
                if ($child->node_name() == "Layout") {
                    return $child->get_attribute("Height");
                }
            }
        }
    }

    /**
    * Get Horizontal Alignment.
    *
    * @return	string	Horizontal Alignment
    */
    public function getHorizontalAlign()
    {
        if (is_object($this->map_node)) {
            $childs = $this->map_node->child_nodes();
            foreach ($childs as $child) {
                if ($child->node_name() == "Layout") {
                    return $child->get_attribute("HorizontalAlign");
                }
            }
        }
    }

    /**
    * Set Caption.
    *
    * @param	string	$a_caption	Caption
    */
    public function setCaption($a_caption)
    {
        if (is_object($this->map_node)) {
            ilDomUtil::setFirstOptionalElement(
                $this->dom,
                $this->map_node,
                "MapCaption",
                array(),
                $a_caption,
                array()
            );
        }
    }

    /**
    * Get Caption.
    *
    * @return	string	Caption
    */
    public function getCaption()
    {
        if (is_object($this->map_node)) {
            $childs = $this->map_node->child_nodes();
            foreach ($childs as $child) {
                if ($child->node_name() == "MapCaption") {
                    return $child->get_content();
                }
            }
        }
    }

    public static function handleCaptionInput($a_text)
    {
        $a_text = str_replace(chr(13) . chr(10), "<br />", $a_text);
        $a_text = str_replace(chr(13), "<br />", $a_text);
        $a_text = str_replace(chr(10), "<br />", $a_text);
        
        return $a_text;
    }
    
    public static function handleCaptionFormOutput($a_text)
    {
        $a_text = str_replace("<br />", "\n", $a_text);
        $a_text = str_replace("<br/>", "\n", $a_text);
        
        return $a_text;
    }

    /**
     * Modify page content after xsl
     *
     * @param string $a_output
     * @return string
     */
    public function modifyPageContentPostXsl($a_html, $a_mode)
    {
        $c_pos = 0;
        $start = strpos($a_html, "[[[[[Map;");
        if (is_int($start)) {
            $end = strpos($a_html, "]]]]]", $start);
        }
        $i = 1;
        while ($end > 0) {
            $param = substr($a_html, $start + 9, $end - $start - 9);
            
            $param = explode(";", $param);
            if (is_numeric($param[0]) && is_numeric($param[1]) && is_numeric($param[2])) {
                include_once("./Services/Maps/classes/class.ilMapUtil.php");
                $map_gui = ilMapUtil::getMapGUI();
                $map_gui->setMapId("map_" . $i)
                        ->setLatitude($param[0])
                        ->setLongitude($param[1])
                        ->setZoom($param[2])
                        ->setWidth($param[3] . "px")
                        ->setHeight($param[4] . "px")
                        ->setEnableTypeControl(true)
                        ->setEnableNavigationControl(true)
                        ->setEnableCentralMarker(true);
                $h2 = substr($a_html, 0, $start) .
                    $map_gui->getHtml() .
                    substr($a_html, $end + 5);
                $a_html = $h2;
                $i++;
            }
            $start = strpos($a_html, "[[[[[Map;", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_html, "]]]]]", $start);
            }
        }
                
        return $a_html;
    }
}
