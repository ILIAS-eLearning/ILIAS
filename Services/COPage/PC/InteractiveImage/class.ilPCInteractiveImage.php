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
    protected DOMNode $mal_node;
    protected DOMNode $med_alias_node;
    protected \ILIAS\DI\UIServices $ui;
    protected \ILIAS\COPage\Html\TransformUtil $htmlTransform;
    protected \ILIAS\COPage\PC\InteractiveImage\IIMManager $manager;

    protected ilMediaAliasItem $std_alias_item;
    protected ilObjMediaObject $mediaobject;
    protected ilLanguage $lng;
    public ?DOMNode $iim_node;

    public function init(): void
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setType("iim");
        $this->manager = $DIC->copage()->internal()->domain()->pc()->interactiveImage();
        $this->htmlTransform = $DIC->copage()->internal()->domain()->htmlTransformUtil();
        $this->ui = $DIC->copage()->internal()->gui()->ui();
    }

    public function readMediaObject(int $a_mob_id = 0): void
    {
        if ($a_mob_id > 0) {
            $mob = new ilObjMediaObject($a_mob_id);
            $this->setMediaObject($mob);
        }
    }

    public function setDomNode(DOMNode $dom_node): void
    {
        parent::setDomNode($dom_node);		// this is the PageContent node
        $this->iim_node = $dom_node->firstChild;
        if (isset($this->iim_node)) {
            $this->med_alias_node = $this->iim_node->firstChild;
        }
        if (isset($this->med_alias_node)) {
            $id = $this->med_alias_node->getAttribute("OriginId");
            $mob_id = ilInternalLink::_extractObjIdOfTarget($id);
            if ($this->object->getTypeForObjId($mob_id) == "mob") {
                $this->setMediaObject(new ilObjMediaObject($mob_id));
            }
        }
        $this->std_alias_item = new ilMediaAliasItem(
            $this->getDomDoc(),
            $this->readHierId(),
            "Standard",
            $this->readPCId(),
            "InteractiveImage"
        );
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
        $this->createPageContentNode();
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
        $this->setDomNode($this->dom_doc->createElement("PageContent"));
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->iim_node = $this->dom_doc->createElement("InteractiveImage");
        $this->iim_node = $this->getDomNode()->appendChild($this->iim_node);
        $this->mal_node = $this->dom_doc->createElement("MediaAlias");
        $this->mal_node = $this->iim_node->appendChild($this->mal_node);
        $this->mal_node->setAttribute("OriginId", "il__mob_" . $this->getMediaObject()->getId());

        // standard view
        $item_node = $this->dom_doc->createElement("MediaAliasItem");
        $item_node = $this->iim_node->appendChild($item_node);
        $item_node->setAttribute("Purpose", "Standard");
        $media_item = $this->getMediaObject()->getMediaItem("Standard");

        $layout_node = $this->dom_doc->createElement("Layout");
        $layout_node = $item_node->appendChild($layout_node);
        $layout_node->setAttribute("HorizontalAlign", "Left");

        // caption
        if ($media_item->getCaption() != "") {
            $cap_node = $this->dom_doc->createElement("Caption");
            $cap_node = $item_node->appendChild($cap_node);
            $cap_node->setAttribute("Align", "bottom");
            $this->dom_util->setContent($cap_node, $media_item->getCaption());
        }

        // text representation
        if ($media_item->getTextRepresentation() != "") {
            $tr_node = $this->dom_doc->createElement("TextRepresentation");
            $tr_node = $item_node->appendChild($tr_node);
            $this->dom_util->setContent($tr_node, $media_item->getTextRepresentation());
        }
    }

    public function dumpXML(): string
    {
        $xml = $this->dom_util->dump($this->getDomNode());
        return $xml;
    }


    ////
    //// Content popups
    ////

    /**
     * Add a tab
     */
    public function addContentPopup(?string $title = null): void
    {
        $lng = $this->lng;

        if ($title === null) {
            $title = $lng->txt("cont_new_popup");
        }
        $max = 0;
        $popups = $this->getPopups();
        foreach ($popups as $p) {
            $max = max($max, (int) $p["nr"]);
        }

        $new_item = $this->dom_doc->createElement("ContentPopup");
        $new_item->setAttribute("Title", $lng->txt("cont_new_popup"));
        $new_item->setAttribute("Nr", $max + 1);
        $new_item = $this->iim_node->appendChild($new_item);
    }

    /**
     * Get popup captions
     */
    public function getPopups(): array
    {
        $titles = array();
        $k = 0;
        foreach ($this->iim_node->childNodes as $c) {
            if ($c->nodeName == "ContentPopup") {
                $pc_id = $c->getAttribute("PCID");
                $hier_id = $c->getAttribute("HierId");
                $title = $c->getAttribute("Title");
                $nr = $c->getAttribute("Nr");

                $titles[] = array("title" => $title,
                                  "nr" => $nr,
                                  "pc_id" => $pc_id,
                                  "hier_id" => $hier_id
                );
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
        foreach ($this->iim_node->childNodes as $c) {
            if ($c->nodeName == "ContentPopup") {
                $pc_id = $c->getAttribute("PCID");
                $hier_id = $c->getAttribute("HierId");
                $k = $hier_id . ":" . $pc_id;
                $c->setAttribute("Title", $a_popups[$k]);
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
        foreach ($this->iim_node->childNodes as $c) {
            if ($c->nodeName === "ContentPopup") {
                if ($a_pc_id == $c->getAttribute("PCID") &&
                    $a_hier_id == $c->getAttribute("HierId")) {
                    $c->parentNode->removeChild($c);
                }
            }
        }
    }

    public function saveContentPopupTitle(string $nr, string $title): void
    {
        foreach ($this->iim_node->childNodes as $c) {
            if ($c->nodeName === "ContentPopup") {
                if ($nr === $c->getAttribute("Nr")) {
                    $c->setAttribute("Title", $title);
                }
            }
        }
    }

    public function deletePopupByNr(
        string $nr
    ): void {
        foreach ($this->iim_node->childNodes as $c) {
            if ($c->nodeName === "ContentPopup") {
                if ($nr === $c->getAttribute("Nr")) {
                    $c->parentNode->removeChild($c);
                }
            }
        }
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
        foreach ($tr_nodes as $tr_node) {
            if ($tr_node->getAttribute("PopupNr") === $nr) {
                $tr_node->removeAttribute("PopupNr");
            }
        }
    }

    ////
    //// Trigger
    ////

    protected function setMapAreaProperties(
        ilMediaAliasItem $a_alias_item,
        string $a_shape_type,
        string $a_coords,
        string $a_title,
        string $a_id
    ) {
        $link = array(
            "LinkType" => IL_EXT_LINK,
            "Href" => ilUtil::stripSlashes("#")
        );

        $a_alias_item->deleteMapAreaById($a_id);
        $a_alias_item->addMapArea(
            $a_shape_type,
            $a_coords,
            ilUtil::stripSlashes($a_title),
            $link,
            $a_id
        );
    }

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

        $this->setMapAreaProperties(
            $a_alias_item,
            $a_shape_type,
            $a_coords,
            ilUtil::stripSlashes($a_title),
            (string) ($max + 1)
        );

        $attributes = array("Type" => self::AREA,
                            "Title" => ilUtil::stripSlashes($a_title),
                            "Nr" => $max + 1,
                            "OverlayX" => "0",
                            "OverlayY" => "0",
                            "Overlay" => "",
                            "PopupNr" => "",
                            "PopupX" => "0",
                            "PopupY" => "0",
                            "PopupWidth" => "150",
                            "PopupHeight" => "200"
                            );
        $ma_node = $this->dom_util->addElementToList(
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
                            "Nr" => $max + 1,
                            "OverlayX" => "0",
                            "OverlayY" => "0",
                            "MarkerX" => "0",
                            "MarkerY" => "0",
                            "PopupNr" => "",
                            "PopupX" => "0",
                            "PopupY" => "0",
                            "PopupWidth" => "150",
                            "PopupHeight" => "200"
        );
        $ma_node = $this->dom_util->addElementToList(
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
    ): DOMNodeList {
        if ($a_pc_id != "") {
            $path = "//PageContent[@PCID = '" . $a_pc_id . "']/InteractiveImage/Trigger";
            return $this->dom_util->path($this->dom_doc, $path);
        }

        $path = "//PageContent[@HierId = '" . $a_hier_id . "']/InteractiveImage/Trigger";
        return $this->dom_util->path($this->dom_doc, $path);
    }

    public function getTriggers(): array
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
        $trigger_arr = array();
        foreach ($tr_nodes as $tr_node) {
            $childs = $tr_node->childNodes;
            $trigger_arr[] = array(
                "Nr" => $tr_node->getAttribute("Nr"),
                "Type" => $tr_node->getAttribute("Type"),
                "Title" => $tr_node->getAttribute("Title"),
                "OverlayX" => $tr_node->getAttribute("OverlayX"),
                "OverlayY" => $tr_node->getAttribute("OverlayY"),
                "MarkerX" => $tr_node->getAttribute("MarkerX"),
                "MarkerY" => $tr_node->getAttribute("MarkerY"),
                "Overlay" => $tr_node->getAttribute("Overlay"),
                "PopupNr" => $tr_node->getAttribute("PopupNr"),
                "PopupX" => $tr_node->getAttribute("PopupX"),
                "PopupY" => $tr_node->getAttribute("PopupY"),
                "PopupWidth" => $tr_node->getAttribute("PopupWidth"),
                "PopupHeight" => $tr_node->getAttribute("PopupHeight"),
                "PopupPosition" => $tr_node->getAttribute("PopupPosition"),
                "PopupSize" => $tr_node->getAttribute("PopupSize")
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
        foreach ($this->iim_node->childNodes as $c) {
            if ($c->nodeName === "Trigger") {
                if ($a_nr === (int) $c->getAttribute("Nr")) {
                    $c->parentNode->removeChild($c);
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
        foreach ($tr_nodes as $tr_node) {
            if (isset($a_ovs["" . $tr_node->getAttribute("Nr")])) {
                $tr_node->setAttribute(
                    "Overlay",
                    $a_ovs["" . $tr_node->getAttribute("Nr")]
                );
            }
        }
    }

    public function deleteOverlay(string $file): void
    {
        $file = str_replace("..", "", ilUtil::stripSlashes($file));
        $this->getMediaObject()
             ->removeAdditionalFile("overlays/" . $file);
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
        foreach ($tr_nodes as $tr_node) {
            if ($tr_node->getAttribute("Overlay") === $file) {
                $tr_node->removeAttribute("Overlay");
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
        foreach ($tr_nodes as $tr_node) {
            if (isset($a_pos["" . $tr_node->getAttribute("Nr")])) {
                $pos = explode(",", $a_pos["" . $tr_node->getAttribute("Nr")]);
                $tr_node->setAttribute("OverlayX", (int) $pos[0]);
                $tr_node->setAttribute("OverlayY", (int) $pos[1]);
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
        foreach ($tr_nodes as $tr_node) {
            if ($tr_node->getAttribute("Type") == self::MARKER) {
                if (isset($a_pos["" . $tr_node->getAttribute("Nr")])) {
                    $pos = explode(",", $a_pos["" . $tr_node->getAttribute("Nr")]);
                    $tr_node->setAttribute("MarkerX", (int) $pos[0]);
                    $tr_node->setAttribute("MarkerY", (int) $pos[1]);
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
        foreach ($tr_nodes as $tr_node) {
            if (isset($a_pos["" . $tr_node->getAttribute("Nr")])) {
                $pos = explode(",", $a_pos["" . $tr_node->getAttribute("Nr")]);
                $tr_node->setAttribute("PopupX", (int) $pos[0]);
                $tr_node->setAttribute("PopupY", (int) $pos[1]);
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
        foreach ($tr_nodes as $tr_node) {
            if (isset($a_size["" . $tr_node->getAttribute("Nr")])) {
                $size = explode(",", $a_size["" . $tr_node->getAttribute("Nr")]);
                $tr_node->setAttribute("PopupWidth", (int) $size[0]);
                $tr_node->setAttribute("PopupHeight", (int) $size[1]);
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
        foreach ($tr_nodes as $tr_node) {
            if (isset($a_pops[(string) $tr_node->getAttribute("Nr")])) {
                $pop = $a_pops[(string) $tr_node->getAttribute("Nr")];
                $tr_node->setAttribute("PopupNr", $pop);
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
        foreach ($tr_nodes as $tr_node) {
            if (isset($a_titles[(string) $tr_node->getAttribute("Nr")])) {
                $tr_node->setAttribute(
                    "Title",
                    $a_titles[(string) $tr_node->getAttribute("Nr")]
                );
                $this->setExtLinkTitle(
                    $tr_node->getAttribute("Nr"),
                    $a_titles[(string) $tr_node->getAttribute("Nr")]
                );
            }
        }
    }

    public function setExtLinkTitle(
        int $a_nr,
        string $a_title
    ): void {
        if ($this->getPCId() != "") {
            $path = "//PageContent[@PCID = '" . $this->getPCId() . "']/InteractiveImage/MediaAliasItem/MapArea[@Id='" . $a_nr . "']/ExtLink";
            $res = $this->dom_util->path($this->dom_doc, $path);
            if (count($res) > 0) {
                $this->dom_util->setContent($res->item(0), $a_title);
            }
            return;
        }

        $path = "//PageContent[@HierId = '" . $this->hier_id . "']/InteractiveImage/MediaAliasItem/MapArea[@Id='" . $a_nr . "']/ExtLink";
        $res = $this->dom_util->path($this->dom_doc, $path);
        if (count($res) > 0) {
            $this->dom_util->setContent($res->item(0), $a_title);
        }
    }

    public static function handleCopiedContent(
        DOMDocument $a_domdoc,
        bool $a_self_ass = true,
        bool $a_clone_mobs = false,
        int $new_parent_id = 0,
        int $obj_copy_id = 0
    ): void {
        global $DIC;

        $dom_util = $DIC->copage()->internal()->domain()->domUtil();
        $path = "//InteractiveImage/MediaAlias";
        $nodes = $dom_util->path($a_domdoc, $path);
        foreach ($nodes as $node) {
            $or_id = $node->getAttribute("OriginId");
            $inst_id = ilInternalLink::_extractInstOfTarget($or_id);
            $mob_id = ilInternalLink::_extractObjIdOfTarget($or_id);
            if (!($inst_id > 0)) {
                if ($mob_id > 0) {
                    $media_object = new ilObjMediaObject($mob_id);
                    $new_mob = $media_object->duplicate();
                    $node->setAttribute("OriginId", "il__mob_" . $new_mob->getId());
                }
            }
        }
    }

    public function createFromMobId(
        \ilPageObject $page,
        int $mob_id,
        string $hier_id,
        string $pc_id
    ): void {
        $this->setMediaObject(new ilObjMediaObject($mob_id));
        $this->createAlias($page, $hier_id, $pc_id);
    }

    public function getIIMModel(): ?stdClass
    {
        $alias_item = $this->getStandardAliasItem();
        $model = new \stdClass();
        $model->triggers = $this->getTriggers();
        $model->popups = $this->getPopups();
        $model->media_item = $alias_item->getModel();
        $model->overlays = $this->manager->getOverlays($this->getMediaObject());
        return $model;
    }

    protected function getTriggerNode(string $nr)
    {
        $tr_nodes = $this->getTriggerNodes($this->hier_id, $this->getPCId());
        foreach ($tr_nodes as $tr_node) {
            if ($tr_node->getAttribute("Nr") === $nr) {
                return $tr_node;
            }
        }
        return null;
    }

    public function setTriggerProperties(string $nr, string $title, string $shape_type, string $coords): void
    {
        $tr_node = $this->getTriggerNode($nr);

        if ($shape_type === "Marker") {

            // set marker properties
            $tr_node->setAttribute("Type", "Marker");
            $tr_node->setAttribute(
                "Title",
                $title
            );
            $coord_parts = explode(",", $coords);
            $tr_node->setAttribute("MarkerX", ($coord_parts[0] ?? "0"));
            $tr_node->setAttribute("MarkerY", ($coord_parts[1] ?? "0"));

            // remove area
            $path = "//PageContent[@HierId = '" . $this->hier_id . "']/InteractiveImage/MediaAliasItem/MapArea[@Id='" . $nr . "']";
            $res = $this->dom_util->path($this->dom_doc, $path);
            if ($child = $res->item(0)) {
                $child->parentNode->removeChild($child);
            }
            return;
        }

        if ($tr_node) {
            $tr_node->setAttribute("Type", "Area");
            $tr_node->removeAttribute("MarkerX");
            $tr_node->removeAttribute("MarkerY");

            $this->setMapAreaProperties(
                $this->getStandardAliasItem(),
                $shape_type,
                $coords,
                ilUtil::stripSlashes($title),
                $nr
            );
        } else {
            $this->addTriggerArea(
                $this->getStandardAliasItem(),
                $shape_type,
                $coords,
                $title
            );
        }
    }

    public function setTriggerOverlay(string $nr, string $overlay, string $coords): void
    {
        $tr_node = $this->getTriggerNode($nr);
        if ($tr_node) {
            $c = explode(",", $coords);
            $x = (int) ($c[0] ?? 0);
            $y = (int) ($c[1] ?? 0);
            $tr_node->setAttribute("Overlay", $overlay);
            $tr_node->setAttribute("OverlayX", $x);
            $tr_node->setAttribute("OverlayY", $y);
        }
    }

    public function setTriggerPopup(string $nr, string $popup, string $position, string $size): void
    {
        $tr_node = $this->getTriggerNode($nr);
        if ($tr_node) {
            $tr_node->setAttribute("PopupNr", $popup);
            $tr_node->setAttribute("PopupPosition", $position);
            $tr_node->setAttribute("PopupSize", $size);
        }
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ): string {
        $keep_original = false;
        if (in_array($a_mode, [ilPageObjectGUI::EDIT], true)) {
            $keep_original = true;
        }
        $trans = $this->htmlTransform;
        while (!is_null($params = $trans->getPlaceholderParams($a_output, "InteractiveImage;PopupStart"))) {
            $params = $trans->getPlaceholderParams($a_output, "InteractiveImage;PopupStart");
            $par_page = $params[2] ?? 0;
            $par_pop_nr = $params[4] ?? 0;
            $inner = $trans->getInnerContentOfPlaceholders(
                $a_output,
                "InteractiveImage;PopupStart",
                "InteractiveImage;PopupEnd"
            );
            if ($keep_original) {
                $new_inner = $inner;
            } else {
                $pop = $this->ui->factory()->popover()->standard(
                    $this->ui->factory()->legacy("#####popovercontent#####")
                );
                $signal_id = $pop->getShowSignal()->getId();
                //$new_inner = $this->ui->renderer()->render($pop);
                $new_inner = "#####popovercontent#####";
                // we need a position relative around the absolute inner div, to make 100% the current available space
                $new_inner = str_replace(
                    "#####popovercontent#####",
                    "<div style='position:relative'><div class='copg-iim-popup copg-iim-popup-md' style='display:none;' data-copg-cont-type='iim-popup' data-signal-id='$signal_id' data-copg-page='$par_page' data-copg-popup-nr='$par_pop_nr'>" . $inner . "</div></div>",
                    $new_inner
                );
            }
            $html = $trans->replaceInnerContentAndPlaceholders(
                $a_output,
                "InteractiveImage;PopupStart",
                "InteractiveImage;PopupEnd",
                $new_inner
            );
            if (is_null($html)) {
                break;
            } else {
                $a_output = $html;
            }
        }
        return $a_output .
'<script type="module" src="./Services/COPage/PC/InteractiveImage/js/presentation/src/presentation.js"></script>';
    }

    public function getPopupDummy(): string
    {
        $content = <<<EOT
<div style='position:relative'><div class='copg-iim-popup copg-iim-popup-md' data-copg-cont-type='iim-popup'>
<div class="ilc_iim_ContentPopup" data-copg-iim-data-type="popup"><div class="ilc_Paragraph ilc_text_block_Standard">
###content###
</div></div></div></div>

EOT;
        return $content;
    }

    public function getBackgroundImage(): string
    {
        $mob = $this->getMediaObject();

        //$ilCtrl = $this->ctrl;

        $st_item = $mob->getMediaItem("Standard");

        // output image map
        $xml = "<dummy>";
        $xml .= $mob->getXML(IL_MODE_ALIAS);
        $xml .= $mob->getXML(IL_MODE_OUTPUT);
        $xml .= "</dummy>";
        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        //echo htmlentities($xml); exit;
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();
        $wb_path = \ilFileUtils::getWebspaceDir("output") . "/";
        $mode = "media";
        //echo htmlentities($ilCtrl->getLinkTarget($this, "showImageMap"));

        $random = new \ilRandom();
        $params = array(
            'media_mode' => 'enable',
            'pg_frame' => "",
            'enlarge_path' => \ilUtil::getImagePath("enlarge.svg"),
            'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        xslt_error($xh);
        xslt_free($xh);

        return $output;
    }

}
