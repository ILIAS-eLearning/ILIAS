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
 * Class ilMediaAliasItem
 * Media Alias Item, component of a media object (file or reference)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaAliasItem
{
    protected DOMDocument $dom_doc;
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;
    protected string $parent_node_name;
    protected string $pcid;
    protected ilLanguage $lng;
    public string $hier_id = "";
    public string $purpose = "";
    public ?DOMNode $item_node = null;

    public function __construct(
        DOMDocument $dom,
        string $a_hier_id,
        string $a_purpose,
        string $a_pc_id = "",
        string $a_parent_node_name = "MediaObject"
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->dom_doc = $dom;
        $this->parent_node_name = $a_parent_node_name;
        $this->hier_id = $a_hier_id;
        $this->purpose = $a_purpose;
        $this->setPcId($a_pc_id);
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
        $this->item_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
    }

    protected function getItemNode(): DOMNode
    {
        return $this->item_node;
    }

    public function getMAItemNode(
        string $a_hier_id,
        string $a_purpose,
        string $a_pc_id = "",
        string $a_sub_element = ""
    ): ?DOMNode {
        if ($a_pc_id != "") {
            $path = "//PageContent[@PCID = '" . $a_pc_id . "']/" . $this->parent_node_name . "/MediaAliasItem[@Purpose='$a_purpose']" . $a_sub_element;
            $nodes = $this->dom_util->path($this->dom_doc, $path);
            if (count($nodes) == 1) {
                return $nodes->item(0);
            }
        }

        $path = "//PageContent[@HierId = '" . $a_hier_id . "']/" . $this->parent_node_name . "/MediaAliasItem[@Purpose='$a_purpose']" . $a_sub_element;
        $nodes = $this->dom_util->path($this->dom_doc, $path);
        if (count($nodes) > 0) {
            return $nodes->item(0);
        }
        return null;
    }

    public function getParameterNodes(
        string $a_hier_id,
        string $a_purpose,
        string $a_pc_id = ""
    ): DOMNodeList {
        if ($a_pc_id != "") {
            $path = "//PageContent[@PCID = '" . $a_pc_id . "']/" . $this->parent_node_name . "/MediaAliasItem[@Purpose='$a_purpose']/Parameter";
            return $this->dom_util->path($this->dom_doc, $path);
        }

        $path = "//PageContent[@HierId = '" . $a_hier_id . "']/" . $this->parent_node_name . "/MediaAliasItem[@Purpose='$a_purpose']/Parameter";
        return $this->dom_util->path($this->dom_doc, $path);
    }

    public function getMapAreaNodes(
        string $a_hier_id,
        string $a_purpose,
        string $a_pc_id = ""
    ): DOMNodeList {
        if ($a_pc_id != "") {
            $path = "//PageContent[@PCID = '" . $a_pc_id . "']/" . $this->parent_node_name . "/MediaAliasItem[@Purpose='$a_purpose']/MapArea";
            return $this->dom_util->path($this->dom_doc, $path);
        }

        $path = "//PageContent[@HierId = '" . $a_hier_id . "']/" . $this->parent_node_name . "/MediaAliasItem[@Purpose='$a_purpose']/MapArea";
        return $this->dom_util->path($this->dom_doc, $path);
    }

    public function setPcId(string $a_pcid): void
    {
        $this->pcid = $a_pcid;
    }

    public function getPcId(): string
    {
        return $this->pcid;
    }

    /**
     * check if item node exists
     */
    public function exists(): bool
    {
        if (is_object($this->item_node)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * inserts new node in dom
     */
    public function insert(): void
    {
        $path = "//PageContent[@HierId = '" . $this->hier_id . "']/" . $this->parent_node_name;
        $nodes = $this->dom_util->path($this->dom_doc, $path);
        if (count($nodes) > 0) {
            $obj_node = $nodes->item(0);
            $item_node = $this->dom_doc->createElement("MediaAliasItem");
            $item_node = $obj_node->appendChild($item_node);
            $item_node->setAttribute("Purpose", $this->purpose);
            $this->item_node = $item_node;
        }
    }

    public function setWidth(string $a_width): void
    {
        $this->dom_util->setFirstOptionalElement(
            $this->getItemNode(),
            "Layout",
            array("Caption", "TextRepresentation", "Parameter", "MapArea"),
            "",
            array("Width" => $a_width),
            false
        );
    }


    public function getWidth(): string
    {
        $layout_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/Layout"
        );
        if (is_object($layout_node)) {
            return $layout_node->getAttribute("Width");
        }
        return "";
    }

    /**
     * check if alias item defines own size or derives size from object
     * @return bool returns true if size is not derived from object
     */
    public function definesSize(): bool
    {
        $layout_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/Layout"
        );
        if (is_object($layout_node)) {
            return $layout_node->hasAttribute("Width");
        }
        return false;
    }

    /**
     * derive size from object (-> width and height attributes are removed from layout element)
     */
    public function deriveSize(): void
    {
        $layout_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/Layout"
        );
        if (is_object($layout_node)) {
            if ($layout_node->hasAttribute("Width")) {
                $layout_node->removeAttribute("Width");
            }
            if ($layout_node->hasAttribute("Height")) {
                $layout_node->removeAttribute("Height");
            }
        }
    }

    public function setHeight(string $a_height): void
    {
        $this->dom_util->setFirstOptionalElement(
            $this->getItemNode(),
            "Layout",
            array("Caption", "TextRepresentation", "Parameter", "MapArea"),
            "",
            array("Height" => $a_height),
            false
        );
    }


    public function getHeight(): string
    {
        $layout_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/Layout"
        );
        if (is_object($layout_node)) {
            return $layout_node->getAttribute("Height");
        }
        return "";
    }

    public function setCaption(string $a_caption): void
    {
        $this->dom_util->setFirstOptionalElement(
            $this->getItemNode(),
            "Caption",
            array("TextRepresentation", "Parameter", "MapArea"),
            $a_caption,
            array("Align" => "bottom")
        );
    }

    public function getCaption(): string
    {
        $caption_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/Caption"
        );
        if (is_object($caption_node)) {
            return $this->dom_util->getContent($caption_node);
        }
        return "";
    }

    /**
     * check if alias item defines own caption or derives caption from object
     * @return bool returns true if caption is not derived from object
     */
    public function definesCaption(): bool
    {
        $caption_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/Caption"
        );
        if (is_object($caption_node)) {
            return true;
        }
        return false;
    }

    /**
     * derive caption from object (-> caption element is removed from media alias item)
     */
    public function deriveCaption(): void
    {
        $caption_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/Caption"
        );
        if (is_object($caption_node)) {
            $caption_node->parentNode->removeChild($caption_node);
        }
    }

    public function setTextRepresentation(
        string $a_text_representation
    ): void {
        $this->dom_util->setFirstOptionalElement(
            $this->getItemNode(),
            "TextRepresentation",
            array("Parameter", "MapArea"),
            $a_text_representation,
            array()
        );
    }

    public function getTextRepresentation(): string
    {
        $text_representation_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/TextRepresentation"
        );
        if (is_object($text_representation_node)) {
            return $this->dom_util->getContent($text_representation_node);
        }
        return "";
    }

    /**
     * check if alias item defines own TextRepresentation or derives TextRepresentation from object
     * @return bool returns true if TextRepresentation is not derived from object
     */
    public function definesTextRepresentation(): bool
    {
        $text_representation_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/TextRepresentation"
        );
        if (is_object($text_representation_node)) {
            return true;
        }
        return false;
    }

    /**
     * derive TextRepresentation from object (-> TextRepresentation element is removed from media alias item)
     */
    public function deriveTextRepresentation(): void
    {
        $text_representation_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/TextRepresentation"
        );
        if (is_object($text_representation_node)) {
            $text_representation_node->parentNode->removeChild($text_representation_node);
        }
    }

    public function setHorizontalAlign(string $a_halign): void
    {
        $this->dom_util->setFirstOptionalElement(
            $this->getItemNode(),
            "Layout",
            array("Caption", "TextRepresentation", "Parameter", "MapArea"),
            "",
            array("HorizontalAlign" => $a_halign),
            false
        );
    }


    public function getHorizontalAlign(): string
    {
        $layout_node = $this->getMAItemNode(
            $this->hier_id,
            $this->purpose,
            $this->getPcId(),
            "/Layout"
        );
        if (is_object($layout_node)) {
            return $layout_node->getAttribute("HorizontalAlign");
        }
        return "";
    }

    public function setParameters(array $a_par_array): void
    {
        $par_nodes = $this->getParameterNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        for ($i = 0; $i < count($par_nodes); $i++) {
            $par_node = $par_nodes[$i];
            $par_node->unlink_node($par_node);
        }

        if (is_array($a_par_array)) {
            foreach ($a_par_array as $par => $val) {
                if (ilMediaItem::checkParameter($par, $val)) {
                    $attributes = array("Name" => $par, "Value" => $val);
                    $this->dom_util->addElementToList(
                        $this->getItemNode(),
                        "Parameter",
                        array("MapArea"),
                        "",
                        $attributes
                    );
                }
            }
        }
    }

    /**
     * Get all parameters as string
     */
    public function getParameterString(): string
    {
        $par_nodes = $this->getParameterNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        $par_arr = array();
        foreach ($par_nodes as $par_node) {
            $par_arr[] = $par_node->getAttribute("Name") . "=\"" . $par_node->getAttribute("Value") . "\"";
        }
        return implode(", ", $par_arr);
    }

    /**
     * Get all parameters as array
     */
    public function getParameters(): array
    {
        $par_nodes = $this->getParameterNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        $par_arr = array();
        for ($i = 0; $i < count($par_nodes); $i++) {
            $par_node = $par_nodes[$i];
            $par_arr[$par_node->getAttribute("Name")] =
                $par_node->getAttribute("Value");
        }
        return $par_arr;
    }

    /**
     * check if alias item defines own parameters or derives parameters from object
     * @return bool returns true if parameters are not derived from object
     */
    public function definesParameters(): bool
    {
        $par_nodes = $this->getParameterNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (count($par_nodes) > 0) {
            return true;
        }
        return false;
    }

    /**
     * derive parameters from object (-> all parameter elements are removed from media alias item)
     */
    public function deriveParameters(): void
    {
        $par_nodes = $this->getParameterNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        foreach ($par_nodes as $par_node) {
            $par_node->parentNode->removeChild($par_node);
        }
    }


    /**
     * Get all map areas
     */
    public function getMapAreas(): array
    {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        $maparea_arr = array();
        $i = 0;
        foreach ($ma_nodes as $maparea_node) {
            $childs = $maparea_node->childNodes;
            $link = array();
            $first = $childs->item(0);
            if ($first->nodeName == "ExtLink") {
                $link = array("LinkType" => "ExtLink",
                    "Href" => $first->getAttribute("Href"),
                    "Title" => $this->dom_util->getContent($first));
            }
            if ($first->nodeName == "IntLink") {
                $link = array("LinkType" => "IntLink",
                    "Target" => $first->getAttribute("Target"),
                    "Type" => $first->getAttribute("Type"),
                    "TargetFrame" => $first->getAttribute("TargetFame"),
                    "Title" => $this->dom_util->getContent($first));
            }
            $maparea_arr[] = array(
                "Nr" => $i + 1,
                "Shape" => $maparea_node->getAttribute("Shape"),
                "Coords" => $maparea_node->getAttribute("Coords"),
                "HighlightMode" => $maparea_node->getAttribute("HighlightMode"),
                "HighlightClass" => $maparea_node->getAttribute("HighlightClass"),
                "Id" => $maparea_node->getAttribute("Id"),
                "Link" => $link);
            $i++;
        }

        return $maparea_arr;
    }

    public function setAreaTitle(
        int $a_nr,
        string $a_title
    ): void {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes[$a_nr - 1])) {
            $childs = $ma_nodes[$a_nr - 1]->child_nodes();
            if (is_object($childs[0]) &&
                ($childs[0]->node_name() == "IntLink" || $childs[0]->node_name() == "ExtLink")) {
                $childs[0]->set_content($a_title);
            }
        }
    }

    /**
     * Set link of area to an internal one
     */
    public function setAreaIntLink(
        int $a_nr,
        string $a_type,
        string $a_target,
        string $a_target_frame
    ): void {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes[$a_nr - 1])) {
            $title = $this->getTitleOfArea($a_nr);
            $this->dom_util->deleteAllChildsByName($ma_nodes[$a_nr - 1]->myDOMNode, array("IntLink", "ExtLink"));
            $attributes = array("Type" => $a_type, "Target" => $a_target,
                "TargetFrame" => $a_target_frame);
            $this->dom_util->setFirstOptionalElement(
                $ma_nodes[$a_nr - 1]->myDOMNode,
                "IntLink",
                array(""),
                $title,
                $attributes
            );
        }
    }

    /**
     * Set link of area to an external one
     */
    public function setAreaExtLink(
        int $a_nr,
        string $a_href
    ): void {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes[$a_nr - 1])) {
            $title = $this->getTitleOfArea($a_nr);
            $this->dom_util->deleteAllChildsByName($ma_nodes[$a_nr - 1]->myDOMNode, array("IntLink", "ExtLink"));
            $attributes = array("Href" => $a_href);
            $this->dom_util->setFirstOptionalElement(
                $ma_nodes[$a_nr - 1]->myDOMNode,
                "ExtLink",
                array(""),
                $title,
                $attributes
            );
        }
    }

    /**
     * Set shape and coords of single area
     */
    public function setShape(
        int $a_nr,
        string $a_shape_type,
        string $a_coords
    ): void {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes[$a_nr - 1])) {
            $ma_nodes[$a_nr - 1]->set_attribute("Shape", $a_shape_type);
            $ma_nodes[$a_nr - 1]->set_attribute("Coords", $a_coords);
        }
    }

    /**
     * Set highlight mode single area
     */
    public function setAreaHighlightMode(
        int $a_nr,
        string $a_mode
    ): void {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes[$a_nr - 1])) {
            $ma_nodes[$a_nr - 1]->set_attribute("HighlightMode", $a_mode);
        }
    }

    /**
     * Set highlight class single area
     */
    public function setAreaHighlightClass(
        int $a_nr,
        string $a_class
    ): void {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes[$a_nr - 1])) {
            $ma_nodes[$a_nr - 1]->set_attribute("HighlightClass", $a_class);
        }
    }

    /**
     * Add a new area to the map
     */
    public function addMapArea(
        string $a_shape_type,
        string $a_coords,
        string $a_title,
        array $a_link,
        string $a_id = ""
    ): void {
        $attributes = array("Shape" => $a_shape_type,
            "Coords" => $a_coords, "Id" => $a_id);

        $ma_node = $this->dom_util->addElementToList(
            $this->getItemNode(),
            "MapArea",
            array(),
            "",
            $attributes
        );

        if ($a_link["LinkType"] == "int" || $a_link["LinkType"] == "IntLink") {
            $attributes = array("Type" => $a_link["Type"],
                "TargetFrame" => $a_link["TargetFrame"],
                "Target" => $a_link["Target"]);
            $this->dom_util->setFirstOptionalElement(
                $ma_node,
                "IntLink",
                array(""),
                $a_title,
                $attributes
            );
        }
        if ($a_link["LinkType"] == "ext" || $a_link["LinkType"] == "ExtLink") {
            $attributes = array("Href" => $a_link["Href"]);
            $this->dom_util->setFirstOptionalElement(
                $ma_node,
                "ExtLink",
                array(""),
                $a_title,
                $attributes
            );
        }
    }

    /**
     * Delete a sinlge map area
     */
    public function deleteMapArea(int $a_nr): void
    {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );

        if (is_object($ma_nodes->item($a_nr - 1))) {
            $node = $ma_nodes->item($a_nr - 1);
            $node->parentNode->removeChild($node);
        }
    }

    /**
     * Delete map areas by id
     */
    public function deleteMapAreaById(
        string $a_id
    ): void {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        foreach ($ma_nodes as $node) {
            if ($node->getAttribute("Id") == $a_id) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    /**
     * Delete all map areas
     */
    public function deleteAllMapAreas(): void
    {
        $path = "//PageContent[@HierId = '" . $this->hier_id . "']/" . $this->parent_node_name . "/MediaAliasItem[@Purpose='" . $this->purpose . "']/MapArea";
        $nodes = $this->dom_util->path($this->dom_doc, $path);
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    /**
     * Get link type
     */
    public function getLinkTypeOfArea(int $a_nr): string
    {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes->item($a_nr - 1))) {
            $childs = $ma_nodes->item($a_nr - 1)->childNodes;
            if ($childs->item(0)->nodeName == "IntLink") {
                return "int";
            }
            if ($childs->item(0)->nodeName == "ExtLink") {
                return "ext";
            }
        }
        return "";
    }

    /**
     * Get type (only internal link)
     */
    public function getTypeOfArea(int $a_nr): string
    {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes->item($a_nr - 1))) {
            $childs = $ma_nodes->item($a_nr - 1)->childNodes;
            return $childs->item(0)->getAttribute("Type");
        }
        return "";
    }

    /**
     * Get target (only internal link)
     */
    public function getTargetOfArea(int $a_nr): string
    {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes->item($a_nr - 1))) {
            $childs = $ma_nodes->item($a_nr - 1)->childNodes;
            return $childs->item(0)->getAttribute("Target");
        }
        return "";
    }

    /**
     * Get target frame (only internal link)
     */
    public function getTargetFrameOfArea(int $a_nr): string
    {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes->item($a_nr - 1))) {
            $childs = $ma_nodes->item($a_nr - 1)->childNodes;
            return $childs->item(0)->getAttribute("TargetFrame");
        }
        return "";
    }

    /**
     * Get href (only external link)
     */
    public function getHrefOfArea(int $a_nr): string
    {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes->item($a_nr - 1))) {
            $childs = $ma_nodes->item($a_nr - 1)->childNodes;
            return $childs->item(0)->getAttribute("Href");
        }
        return "";
    }

    /**
     * Get title
     */
    public function getTitleOfArea(int $a_nr): string
    {
        $ma_nodes = $this->getMapAreaNodes(
            $this->hier_id,
            $this->purpose,
            $this->getPcId()
        );
        if (is_object($ma_nodes->item($a_nr - 1))) {
            $childs = $ma_nodes->item($a_nr - 1)->childNodes;
            return $this->dom_util->getContent($childs->item(0));
        }
        return "";
    }

    /**
     * delete full item node from dom
     */
    public function delete(): void
    {
        if (is_object($this->item_node)) {
            $this->item_node->parentNode->removeChild($this->item_node);
        }
    }

    /**
     * make map work copy of image
     * @param int $a_area_nr draw area $a_area_nr only
     * @param bool $a_exclude true: draw all areas but area $a_area_nr
     */
    public function makeMapWorkCopy(
        ilMediaItem $a_st_item,
        int $a_area_nr,
        bool $a_exclude,
        bool $a_output_new_area,
        string $a_area_type,
        string $a_coords
    ): bool {
        $a_st_item->copyOriginal();
        $a_st_item->buildMapWorkImage();

        // determine ratios (first see whether the instance has w/h defined)
        $width = $this->getWidth();
        $height = $this->getHeight();

        // if instance has no size, use object w/h
        if ($width == 0 && $height == 0) {
            $width = $a_st_item->getWidth();
            $height = $a_st_item->getHeight();
        }
        $size = getimagesize($a_st_item->getMapWorkCopyName());
        $x_ratio = 1;
        if ($size[0] > 0 && $width > 0) {
            $x_ratio = $width / $size[0];
        }
        $y_ratio = 1;
        if ($size[1] > 0 && $height > 0) {
            $y_ratio = $height / $size[1];
        }

        // draw map areas
        $areas = $this->getMapAreas();
        for ($i = 0; $i < count($areas); $i++) {
            if (((($i + 1) == $a_area_nr) && !$a_exclude) ||
                    ((($i + 1) != $a_area_nr) && $a_exclude) ||
                    ($a_area_nr == 0)
            ) {
                $area = new ilMapArea();
                $area->setShape($areas[$i]["Shape"]);
                $area->setCoords($areas[$i]["Coords"]);
                $area->draw(
                    $a_st_item->getMapWorkImage(),
                    $a_st_item->color1,
                    $a_st_item->color2,
                    true,
                    $x_ratio,
                    $y_ratio
                );
            }
        }

        if ($a_output_new_area) {
            $area = new ilMapArea();
            $area->setShape($a_area_type);
            $area->setCoords($a_coords);
            $area->draw(
                $a_st_item->getMapWorkImage(),
                $a_st_item->color1,
                $a_st_item->color2,
                false,
                $x_ratio,
                $y_ratio
            );
        }

        $a_st_item->saveMapWorkImage();
        return true;
    }

    /**
     * Has the alias any properties set?
     */
    public function hasAnyPropertiesSet(): bool
    {
        return ($this->definesSize() ||
            $this->definesCaption() ||
            $this->definesTextRepresentation() ||
            $this->definesParameters());
    }

    public function getModel(): ?stdClass
    {
        $model = new \stdClass();
        $model->areas = $this->getMapAreas();
        $model->caption = $this->getCaption();
        return $model;
    }
}
