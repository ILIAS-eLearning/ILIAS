<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
 * Interactive image.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCInteractiveImage extends ilPageContent
{
    /**
     * @var ilLanguage
     */
    protected $lng;


    public $dom;
    public $iim_node;
    
    const AREA = "Area";
    const MARKER = "Marker";
    
    /**
     * Init page content component.
     */
    public function init()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setType("iim");
    }

    /**
    * Read/get Media Object
    *
    * @param	int		media object ID
    */
    public function readMediaObject($a_mob_id = 0)
    {
        if ($a_mob_id > 0) {
            $mob = new ilObjMediaObject($a_mob_id);
            $this->setMediaObject($mob);
        }
    }
    
    /**
     * Set node (and media object node)
     */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->iim_node = $a_node->first_child();
        $this->med_alias_node = $this->iim_node->first_child();
        if (is_object($this->med_alias_node) && $this->med_alias_node->myDOMNode != null) {
            $id = $this->med_alias_node->get_attribute("OriginId");
            $mob_id = ilInternalLink::_extractObjIdOfTarget($id);
            if (ilObject::_lookupType($mob_id) == "mob") {
                $this->setMediaObject(new ilObjMediaObject($mob_id));
            }
        }
        include_once("./Services/COPage/classes/class.ilMediaAliasItem.php");
        $this->std_alias_item = new ilMediaAliasItem(
            $this->dom,
            $this->readHierId(),
            "Standard",
            $this->readPCId(),
            "InteractiveImage"
        );
    }

    /**
     * Set dom object
     */
    public function setDom(&$a_dom)
    {
        $this->dom = $a_dom;
    }

    /**
     * Set Media Object.
     *
     * @param	object	$a_mediaobject	Media Object
     */
    public function setMediaObject($a_mediaobject)
    {
        $this->mediaobject = $a_mediaobject;
    }

    /**
     * Get Media Object.
     *
     * @return	object	Media Object
     */
    public function getMediaObject()
    {
        return $this->mediaobject;
    }
    
    /**
     * Create new media object
     */
    public function createMediaObject()
    {
        $this->setMediaObject(new ilObjMediaObject());
    }

    /**
     * Create pc media object
     */
    public function create($a_pg_obj, $a_hier_id)
    {
        $this->node = $this->createPageContentNode();
    }
    
    /**
     * Get standard media item
     *
     * @return
     */
    public function getStandardMediaItem()
    {
        return $this->getMediaObject()->getMediaItem("Standard");
    }
    
    /**
     * Get standard alias item
     */
    public function getStandardAliasItem()
    {
        return $this->std_alias_item;
    }
    
    
    /**
     * Get base thumbnail target
     *
     * @return string base thumbnail target
     */
    public function getBaseThumbnailTarget()
    {
        return $this->getMediaObject()->getMediaItem("Standard")->getThumbnailTarget();
    }
    
    
    /**
     * Create an media alias in page
     *
     * @param	object	$a_pg_obj		page object
     * @param	string	$a_hier_id		hierarchical ID
     */
    public function createAlias(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->dom->create_element("PageContent");
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->iim_node = $this->dom->create_element("InteractiveImage");
        $this->iim_node = $this->node->append_child($this->iim_node);
        $this->mal_node = $this->dom->create_element("MediaAlias");
        $this->mal_node = $this->iim_node->append_child($this->mal_node);
        $this->mal_node->set_attribute("OriginId", "il__mob_" . $this->getMediaObject()->getId());

        // standard view
        $item_node = $this->dom->create_element("MediaAliasItem");
        $item_node = $this->iim_node->append_child($item_node);
        $item_node->set_attribute("Purpose", "Standard");
        $media_item = $this->getMediaObject()->getMediaItem("Standard");

        $layout_node = $this->dom->create_element("Layout");
        $layout_node = $item_node->append_child($layout_node);
        if ($media_item->getWidth() > 0) {
            //$layout_node->set_attribute("Width", $media_item->getWidth());
        }
        if ($media_item->getHeight() > 0) {
            //$layout_node->set_attribute("Height", $media_item->getHeight());
        }
        $layout_node->set_attribute("HorizontalAlign", "Left");

        // caption
        if ($media_item->getCaption() != "") {
            $cap_node = $this->dom->create_element("Caption");
            $cap_node = $item_node->append_child($cap_node);
            $cap_node->set_attribute("Align", "bottom");
            $cap_node->set_content($media_item->getCaption());
        }

        // text representation
        if ($media_item->getTextRepresentation() != "") {
            $tr_node = $this->dom->create_element("TextRepresentation");
            $tr_node = $item_node->append_child($tr_node);
            $tr_node->set_content($media_item->getTextRepresentation());
        }
    }

    /**
     * Dump node xml
     */
    public function dumpXML()
    {
        $xml = $this->dom->dump_node($this->node);
        return $xml;
    }
    
    /**
     * Set style class
     *
     * @param string $a_class style class
     */
    public function setStyleClass($a_class)
    {
        // check this
        die("pcinteractiveimage: setstyleclass");
        if (is_object($this->iim_node)) {
            $mal_node = $this->iim_node->first_child();
            if (is_object($mal_node)) {
                if (!empty($a_class)) {
                    $mal_node->set_attribute("Class", $a_class);
                } else {
                    if ($mal_node->has_attribute("Class")) {
                        $mal_node->remove_attribute("Class");
                    }
                }
            }
        }
    }

    /**
     * Get style class
     *
     * @return string style class
     */
    public function getStyleClass()
    {
        if (is_object($this->iim_node)) {
            $mal_node = $this->iim_node->first_child();
            if (is_object($mal_node)) {
                $class = $mal_node->get_attribute("Class");
                return $class;
            }
        }
    }

    
    ////
    //// Content popups
    ////


    /**
     * Add a tab
     */
    public function addContentPopup()
    {
        $lng = $this->lng;
        
        $max = 0;
        $popups = $this->getPopups();
        foreach ($popups as $p) {
            $max = max($max, (int) $p["nr"]);
        }
        
        $new_item = $this->dom->create_element("ContentPopup");
        $new_item->set_attribute("Title", $lng->txt("cont_new_popup"));
        $new_item->set_attribute("Nr", $max + 1);
        $new_item = $this->iim_node->append_child($new_item);
    }
    
    /**
     * Get popup captions
     */
    public function getPopups()
    {
        $titles = array();
        $childs = $this->iim_node->child_nodes();
        $k = 0;
        for ($i = 0; $i < count($childs); $i++) {
            if ($childs[$i]->node_name() == "ContentPopup") {
                $pc_id = $childs[$i]->get_attribute("PCID");
                $hier_id = $childs[$i]->get_attribute("HierId");
                $title = $childs[$i]->get_attribute("Title");
                $nr = $childs[$i]->get_attribute("Nr");
                
                $titles[] = array("title" => $title, "nr" => $nr,
                    "pc_id" => $pc_id, "hier_id" => $hier_id);
                $k++;
            }
        }
        return $titles;
    }

    /**
     * Save popups
     */
    public function savePopups($a_popups)
    {
        $childs = $this->iim_node->child_nodes();
        for ($i = 0; $i < count($childs); $i++) {
            if ($childs[$i]->node_name() == "ContentPopup") {
                $pc_id = $childs[$i]->get_attribute("PCID");
                $hier_id = $childs[$i]->get_attribute("HierId");
                $k = $hier_id . ":" . $pc_id;
                $childs[$i]->set_attribute("Title", $a_popups[$k]);
            }
        }
    }

    /**
     * Delete popup
     */
    public function deletePopup($a_hier_id, $a_pc_id)
    {
        // File Item
        $childs = $this->iim_node->child_nodes();
        $nodes = array();
        for ($i = 0; $i < count($childs); $i++) {
            if ($childs[$i]->node_name() == "ContentPopup") {
                if ($a_pc_id == $childs[$i]->get_attribute("PCID") &&
                    $a_hier_id == $childs[$i]->get_attribute("HierId")) {
                    $childs[$i]->unlink($childs[$i]);
                }
            }
        }
    }

    /**
     * Get caption
     */
    /*
        function getCaption($a_hier_id, $a_pc_id)
        {
            $captions = array();
            $tab_nodes = $this->tabs_node->child_nodes();
            $k = 0;
            for($i = 0; $i < count($tab_nodes); $i++)
            {
                if ($tab_nodes[$i]->node_name() == "Tab")
                {
                    if ($a_pc_id == $tab_nodes[$i]->get_attribute("PCID") &&
                        ($a_hier_id == $tab_nodes[$i]->get_attribute("HierId")))
                    {
                        $tab_node_childs = $tab_nodes[$i]->child_nodes();
                        for($j = 0; $j < count($tab_node_childs); $j++)
                        {
                            if ($tab_node_childs[$j]->node_name() == "TabCaption")
                            {
                                return $tab_node_childs[$j]->get_content();
                            }
                        }
                    }
                }
            }

            return "";
        }
    */

    /**
     * Save positions of tabs
     */
    /*
        function savePositions($a_pos)
        {
            asort($a_pos);

            // File Item
            $childs = $this->tabs_node->child_nodes();
            $nodes = array();
            for ($i=0; $i<count($childs); $i++)
            {
                if ($childs[$i]->node_name() == "Tab")
                {
                    $pc_id = $childs[$i]->get_attribute("PCID");
                    $hier_id = $childs[$i]->get_attribute("HierId");
                    $nodes[$hier_id.":".$pc_id] = $childs[$i];
                    $childs[$i]->unlink($childs[$i]);
                }
            }

            foreach($a_pos as $k => $v)
            {
                if (is_object($nodes[$k]))
                {
                    $nodes[$k] = $this->tabs_node->append_child($nodes[$k]);
                }
            }
        }
    */

    /**
    * Save positions of tabs
    */
    /*
        function deleteTab($a_hier_id, $a_pc_id)
        {
            // File Item
            $childs = $this->tabs_node->child_nodes();
            $nodes = array();
            for ($i=0; $i<count($childs); $i++)
            {
                if ($childs[$i]->node_name() == "Tab")
                {
                    if ($a_pc_id == $childs[$i]->get_attribute("PCID") &&
                        $a_hier_id == $childs[$i]->get_attribute("HierId"))
                    {
                        $childs[$i]->unlink($childs[$i]);
                    }
                }
            }
        }
    */

    ////
    //// Trigger
    ////
    
    /**
     * Add a new trigger
     */
    public function addTriggerArea(
        $a_alias_item,
        $a_shape_type,
        $a_coords,
        $a_title,
        $a_link
    ) {
        $max = 0;
        $triggers = $this->getTriggers();
        foreach ($triggers as $t) {
            $max = max($max, (int) $t["Nr"]);
        }

        $link = array(
            "LinkType" => IL_EXT_LINK,
            "Href" => ilUtil::stripSlashes("#"));

        $a_alias_item->addMapArea(
            $a_shape_type,
            $a_coords,
            ilUtil::stripSlashes($a_title),
            $link,
            $max + 1
        );
        
        $attributes = array("Type" => self::AREA,
            "Title" => ilUtil::stripSlashes($a_title),
            "Nr" => $max + 1,
            "OverlayX" => "0", "OverlayY" => "0", "Overlay" => "", "PopupNr" => "",
            "PopupX" => "0", "PopupY" => "0", "PopupWidth" => "150", "PopupHeight" => "200");
        $ma_node = ilDOMUtil::addElementToList(
            $this->dom,
            $this->iim_node,
            "Trigger",
            array("ContentPopup"),
            "",
            $attributes
        );
    }

    /**
     * Add a new trigger marker
     */
    public function addTriggerMarker()
    {
        $lng = $this->lng;
        
        $max = 0;
        $triggers = $this->getTriggers();
        foreach ($triggers as $t) {
            $max = max($max, (int) $t["Nr"]);
        }
        
        $attributes = array("Type" => self::MARKER,
            "Title" => $lng->txt("cont_new_marker"),
            "Nr" => $max + 1, "OverlayX" => "0", "OverlayY" => "0",
            "MarkerX" => "0", "MarkerY" => "0", "PopupNr" => "",
            "PopupX" => "0", "PopupY" => "0", "PopupWidth" => "150", "PopupHeight" => "200");
        $ma_node = ilDOMUtil::addElementToList(
            $this->dom,
            $this->iim_node,
            "Trigger",
            array("ContentPopup"),
            "",
            $attributes
        );
    }

    /**
     * Get trigger nodes
     */
    public function getTriggerNodes($a_hier_id, $a_pc_id = "")
    {
        if ($a_pc_id != "") {
            $xpc = xpath_new_context($this->dom);
            $path = "//PageContent[@PCID = '" . $a_pc_id . "']/InteractiveImage/Trigger";
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) > 0) {
                return $res->nodeset;
            }
            return array();
        }
        
        $xpc = xpath_new_context($this->dom);
        $path = "//PageContent[@HierId = '" . $a_hier_id . "']/InteractiveImage/Trigger";
        $res = xpath_eval($xpc, $path);
        if (count($res->nodeset) > 0) {
            return $res->nodeset;
        }
    }

    
    /**
     * Get triggers
     */
    public function getTriggers()
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPcId());
        $trigger_arr = array();
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            $childs = $tr_node->child_nodes();
            $trigger_arr[] = array(
                "Nr" => $tr_node->get_attribute("Nr"),
                "Type" => $tr_node->get_attribute("Type"),
                "Title" => $tr_node->get_attribute("Title"),
                "OverlayX" => $tr_node->get_attribute("OverlayX"),
                "OverlayY" => $tr_node->get_attribute("OverlayY"),
                "MarkerX" => $tr_node->get_attribute("MarkerX"),
                "MarkerY" => $tr_node->get_attribute("MarkerY"),
                "Overlay" => $tr_node->get_attribute("Overlay"),
                "PopupNr" => $tr_node->get_attribute("PopupNr"),
                "PopupX" => $tr_node->get_attribute("PopupX"),
                "PopupY" => $tr_node->get_attribute("PopupY"),
                "PopupWidth" => $tr_node->get_attribute("PopupWidth"),
                "PopupHeight" => $tr_node->get_attribute("PopupHeight")
                );
        }
        
        return $trigger_arr;
    }
    
    /**
     * Delete Trigger
     */
    public function deleteTrigger($a_alias_item, $a_nr)
    {
        // File Item
        $childs = $this->iim_node->child_nodes();
        $nodes = array();
        for ($i = 0; $i < count($childs); $i++) {
            if ($childs[$i]->node_name() == "Trigger") {
                if ($a_nr == $childs[$i]->get_attribute("Nr")) {
                    $childs[$i]->unlink($childs[$i]);
                }
            }
        }
        $a_alias_item->deleteMapAreaById($a_nr);
    }

    
    /**
     * Set trigger overlays
     *
     * @param array array of strings (representing the overlays for the trigger)
     */
    public function setTriggerOverlays($a_ovs)
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPcId());
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            if (isset($a_ovs["" . $tr_node->get_attribute("Nr")])) {
                $tr_node->set_attribute(
                    "Overlay",
                    $a_ovs["" . $tr_node->get_attribute("Nr")]
                );
            }
        }
    }
    
    /**
     * Set trigger overlay position
     *
     * @param array array of strings (representing the overlays for the trigger)
     */
    public function setTriggerOverlayPositions($a_pos)
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPcId());
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            if (isset($a_pos["" . $tr_node->get_attribute("Nr")])) {
                $pos = explode(",", $a_pos["" . $tr_node->get_attribute("Nr")]);
                $tr_node->set_attribute("OverlayX", (int) $pos[0]);
                $tr_node->set_attribute("OverlayY", (int) $pos[1]);
            }
        }
    }

    /**
     * Set trigger marker position
     *
     * @param array array of strings (representing the marker positions for the trigger)
     */
    public function setTriggerMarkerPositions($a_pos)
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPcId());
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            if ($tr_node->get_attribute("Type") == self::MARKER) {
                if (isset($a_pos["" . $tr_node->get_attribute("Nr")])) {
                    $pos = explode(",", $a_pos["" . $tr_node->get_attribute("Nr")]);
                    $tr_node->set_attribute("MarkerX", (int) $pos[0]);
                    $tr_node->set_attribute("MarkerY", (int) $pos[1]);
                }
            }
        }
    }

    /**
     * Set trigger popup position
     *
     * @param array array of strings (representing the popup positions for the trigger)
     */
    public function setTriggerPopupPositions($a_pos)
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPcId());
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            if (isset($a_pos["" . $tr_node->get_attribute("Nr")])) {
                $pos = explode(",", $a_pos["" . $tr_node->get_attribute("Nr")]);
                $tr_node->set_attribute("PopupX", (int) $pos[0]);
                $tr_node->set_attribute("PopupY", (int) $pos[1]);
            }
        }
    }
    
    /**
     * Set trigger popup size
     *
     * @param array array of strings (representing the popup sizes for the trigger)
     */
    public function setTriggerPopupSize($a_size)
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPcId());
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            if (isset($a_size["" . $tr_node->get_attribute("Nr")])) {
                $size = explode(",", $a_size["" . $tr_node->get_attribute("Nr")]);
                $tr_node->set_attribute("PopupWidth", (int) $size[0]);
                $tr_node->set_attribute("PopupHeight", (int) $size[1]);
            }
        }
    }

    /**
     * Set trigger popups
     *
     * @param array array of strings (representing the popups for the trigger)
     */
    public function setTriggerPopups($a_pops)
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPcId());
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            if (isset($a_pops["" . $tr_node->get_attribute("Nr")])) {
                $pop = $a_pops["" . $tr_node->get_attribute("Nr")];
                $tr_node->set_attribute("PopupNr", $pop);
            }
        }
    }

    /**
     * Set trigger titles
     *
     * @param array array of strings (representing the titles for the trigger)
     */
    public function setTriggerTitles($a_titles)
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPcId());
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            if (isset($a_titles["" . $tr_node->get_attribute("Nr")])) {
                $tr_node->set_attribute(
                    "Title",
                    $a_titles["" . $tr_node->get_attribute("Nr")]
                );
                $this->setExtLinkTitle(
                    $tr_node->get_attribute("Nr"),
                    $a_titles["" . $tr_node->get_attribute("Nr")]
                );
            }
        }
    }

    /**
     * Set ExtLink Title
     *
     * @param
     * @return
     */
    public function setExtLinkTitle($a_nr, $a_title)
    {
        if ($this->getPcId() != "") {
            $xpc = xpath_new_context($this->dom);
            $path = "//PageContent[@PCID = '" . $this->getPcId() . "']/InteractiveImage/MediaAliasItem/MapArea[@Id='" . $a_nr . "']/ExtLink";
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) > 0) {
                $res->nodeset[0]->set_content($a_title);
            }
            return;
        }

        $xpc = xpath_new_context($this->dom);
        $path = "//PageContent[@HierId = '" . $this->hier_id . "']/InteractiveImage/MediaAliasItem/MapArea[@Id='" . $a_nr . "']/ExtLink";
        $res = xpath_eval($xpc, $path);
        if (count($res->nodeset) > 0) {
            $res->nodeset[0]->set_content($a_title);
        }
    }
}
