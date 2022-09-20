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
 * Interactive image.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCInteractiveImage extends ilPageContent
{
    public const AREA = "Area";
    public const MARKER = "Marker";
    protected php4DOMElement $mal_node;
    protected php4DOMElement $med_alias_node;

    protected ilMediaAliasItem $std_alias_item;
    protected ilObjMediaObject $mediaobject;
    protected ilLanguage $lng;
    public php4DOMElement $iim_node;

    public function init(): void
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setType("iim");
    }

    public function readMediaObject(int $a_mob_id = 0): void
    {
        if ($a_mob_id > 0) {
            $mob = new ilObjMediaObject($a_mob_id);
            $this->setMediaObject($mob);
        }
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->iim_node = $a_node->first_child();
        if (isset($this->iim_node->myDOMNode)) {
            $this->med_alias_node = $this->iim_node->first_child();
        }
        if (isset($this->med_alias_node) && $this->med_alias_node->myDOMNode != null) {
            $id = $this->med_alias_node->get_attribute("OriginId");
            $mob_id = ilInternalLink::_extractObjIdOfTarget($id);
            if (ilObject::_lookupType($mob_id) == "mob") {
                $this->setMediaObject(new ilObjMediaObject($mob_id));
            }
        }
        $this->std_alias_item = new ilMediaAliasItem(
            $this->dom,
            $this->readHierId(),
            "Standard",
            $this->readPCId(),
            "InteractiveImage"
        );
    }

    public function setDom(php4DOMDocument $a_dom): void
    {
        $this->dom = $a_dom;
    }

    public function setMediaObject(ilObjMediaObject $a_mediaobject): void
    {
        $this->mediaobject = $a_mediaobject;
    }

    public function getMediaObject(): ilObjMediaObject
    {
        return $this->mediaobject;
    }

    public function createMediaObject(): void
    {
        $this->setMediaObject(new ilObjMediaObject());
    }

    public function create(): void
    {
        $this->node = $this->createPageContentNode();
    }

    public function getStandardMediaItem(): ilMediaItem
    {
        return $this->getMediaObject()->getMediaItem("Standard");
    }

    public function getStandardAliasItem(): ilMediaAliasItem
    {
        return $this->std_alias_item;
    }

    public function getBaseThumbnailTarget(): string
    {
        return $this->getMediaObject()->getMediaItem("Standard")->getThumbnailTarget();
    }


    public function createAlias(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
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

    public function dumpXML(): string
    {
        $xml = $this->dom->dump_node($this->node);
        return $xml;
    }


    ////
    //// Content popups
    ////


    /**
     * Add a tab
     */
    public function addContentPopup(): void
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
    public function getPopups(): array
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
    public function savePopups(array $a_popups): void
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
    public function deletePopup(
        string $a_hier_id,
        string $a_pc_id
    ): void {
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

    ////
    //// Trigger
    ////

    /**
     * Add a new trigger
     */
    public function addTriggerArea(
        ilMediaAliasItem $a_alias_item,
        string $a_shape_type,
        string $a_coords,
        string $a_title
    ): void {
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
    public function addTriggerMarker(): void
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

    public function getTriggerNodes(
        string $a_hier_id,
        string $a_pc_id = ""
    ): array {
        $xpc = xpath_new_context($this->dom);
        if ($a_pc_id != "") {
            $path = "//PageContent[@PCID = '" . $a_pc_id . "']/InteractiveImage/Trigger";
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) > 0) {
                return $res->nodeset;
            }
            return [];
        }

        $path = "//PageContent[@HierId = '" . $a_hier_id . "']/InteractiveImage/Trigger";
        $res = xpath_eval($xpc, $path);
        if (count($res->nodeset) > 0) {
            return $res->nodeset;
        }
        return [];
    }

    public function getTriggers(): array
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
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
    public function deleteTrigger(
        ilMediaAliasItem $a_alias_item,
        int $a_nr
    ): void {
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
     * @param array $a_ovs array of strings (representing the overlays for the trigger)
     */
    public function setTriggerOverlays(
        array $a_ovs
    ): void {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
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
     * @param array $a_pos array of strings (representing the overlays for the trigger)
     */
    public function setTriggerOverlayPositions(
        array $a_pos
    ): void {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
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
     * @param array $a_pos array of strings (representing the marker positions for the trigger)
     */
    public function setTriggerMarkerPositions(
        array $a_pos
    ): void {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
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
     * @param array $a_pos array of strings (representing the popup positions for the trigger)
     */
    public function setTriggerPopupPositions(
        array $a_pos
    ): void {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
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
     * @param array $a_size array of strings (representing the popup sizes for the trigger)
     */
    public function setTriggerPopupSize(
        array $a_size
    ): void {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
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
     * @param array $a_pops array of strings (representing the popups for the trigger)
     */
    public function setTriggerPopups(
        array $a_pops
    ): void {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            if (isset($a_pops[(string) $tr_node->get_attribute("Nr")])) {
                $pop = $a_pops[(string) $tr_node->get_attribute("Nr")];
                $tr_node->set_attribute("PopupNr", $pop);
            }
        }
    }

    /**
     * Set trigger titles
     * @param array $a_titles array of strings (representing the titles for the trigger)
     */
    public function setTriggerTitles(
        array $a_titles
    ): void {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
        for ($i = 0; $i < count($tr_nodes); $i++) {
            $tr_node = $tr_nodes[$i];
            if (isset($a_titles[(string) $tr_node->get_attribute("Nr")])) {
                $tr_node->set_attribute(
                    "Title",
                    $a_titles[(string) $tr_node->get_attribute("Nr")]
                );
                $this->setExtLinkTitle(
                    $tr_node->get_attribute("Nr"),
                    $a_titles[(string) $tr_node->get_attribute("Nr")]
                );
            }
        }
    }

    public function setExtLinkTitle(
        int $a_nr,
        string $a_title
    ): void {
        if ($this->getPCId() != "") {
            $xpc = xpath_new_context($this->dom);
            $path = "//PageContent[@PCID = '" . $this->getPCId() . "']/InteractiveImage/MediaAliasItem/MapArea[@Id='" . $a_nr . "']/ExtLink";
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
