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
 * Class ilPCMap
 * Map content object (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCMap extends ilPageContent
{
    public php4DOMElement $map_node;

    public function init(): void
    {
        $this->setType("map");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->map_node = $a_node->first_child();		// this is the Map node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();

        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->map_node = $this->dom->create_element("Map");
        $this->map_node = $this->node->append_child($this->map_node);
        $this->map_node->set_attribute("Latitude", "0");
        $this->map_node->set_attribute("Longitude", "0");
        $this->map_node->set_attribute("Zoom", "3");
    }

    public function setLatitude(?float $a_lat = null): void
    {
        if (!is_null($a_lat)) {
            $this->map_node->set_attribute("Latitude", (string) $a_lat);
        } else {
            if ($this->map_node->has_attribute("Latitude")) {
                $this->map_node->remove_attribute("Latitude");
            }
        }
    }

    public function getLatitude(): ?float
    {
        if (is_object($this->map_node)) {
            return (float) $this->map_node->get_attribute("Latitude");
        }
        return null;
    }

    public function setLongitude(?float $a_long = null): void
    {
        if (!is_null($a_long)) {
            $this->map_node->set_attribute("Longitude", $a_long);
        } else {
            if ($this->map_node->has_attribute("Longitude")) {
                $this->map_node->remove_attribute("Longitude");
            }
        }
    }

    public function getLongitude(): ?float
    {
        if (is_object($this->map_node)) {
            return (float) $this->map_node->get_attribute("Longitude");
        }
        return null;
    }

    public function setZoom(?int $a_zoom): void
    {
        //if (!empty($a_zoom)) {
        $this->map_node->set_attribute("Zoom", (int) $a_zoom);
        /*} else {
            if ($this->map_node->has_attribute("Zoom")) {
                $this->map_node->remove_attribute("Zoom");
            }
        }*/
    }

    public function getZoom(): ?int
    {
        if (is_object($this->map_node)) {
            return (int) $this->map_node->get_attribute("Zoom");
        }
        return null;
    }

    public function setLayout(
        ?int $a_width,
        ?int $a_height,
        string $a_horizontal_align
    ): void {
        if (is_object($this->map_node)) {
            ilDOMUtil::setFirstOptionalElement(
                $this->dom,
                $this->map_node,
                "Layout",
                array("MapCaption"),
                "",
                array("Width" => (string) $a_width,
                    "Height" => (string) $a_height, "HorizontalAlign" => $a_horizontal_align)
            );
        }
    }

    public function getWidth(): ?int
    {
        if (is_object($this->map_node)) {
            $childs = $this->map_node->child_nodes();
            foreach ($childs as $child) {
                if ($child->node_name() == "Layout") {
                    $w = $child->get_attribute("Width")
                        ? (int) $child->get_attribute("Width")
                        : null;
                    return $w;
                }
            }
        }
        return null;
    }

    public function getHeight(): ?int
    {
        if (is_object($this->map_node)) {
            $childs = $this->map_node->child_nodes();
            foreach ($childs as $child) {
                if ($child->node_name() == "Layout") {
                    $h = $child->get_attribute("Height")
                        ? (int) $child->get_attribute("Height")
                        : null;
                    return $h;
                }
            }
        }
        return null;
    }

    public function getHorizontalAlign(): string
    {
        if (is_object($this->map_node)) {
            $childs = $this->map_node->child_nodes();
            foreach ($childs as $child) {
                if ($child->node_name() == "Layout") {
                    return $child->get_attribute("HorizontalAlign");
                }
            }
        }
        return "";
    }

    public function setCaption(string $a_caption): void
    {
        if (is_object($this->map_node)) {
            ilDOMUtil::setFirstOptionalElement(
                $this->dom,
                $this->map_node,
                "MapCaption",
                array(),
                $a_caption,
                array()
            );
        }
    }

    public function getCaption(): string
    {
        if (is_object($this->map_node)) {
            $childs = $this->map_node->child_nodes();
            foreach ($childs as $child) {
                if ($child->node_name() == "MapCaption") {
                    return $child->get_content();
                }
            }
        }
        return "";
    }

    public static function handleCaptionInput(
        string $a_text
    ): string {
        $a_text = str_replace(chr(13) . chr(10), "<br />", $a_text);
        $a_text = str_replace(chr(13), "<br />", $a_text);
        $a_text = str_replace(chr(10), "<br />", $a_text);

        return $a_text;
    }

    public static function handleCaptionFormOutput(
        string $a_text
    ): string {
        $a_text = str_replace("<br />", "\n", $a_text);
        $a_text = str_replace("<br/>", "\n", $a_text);

        return $a_text;
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ): string {
        $end = 0;
        $start = strpos($a_output, "[[[[[Map;");
        if (is_int($start)) {
            $end = strpos($a_output, "]]]]]", $start);
        }
        $i = 1;
        while ($end > 0) {
            $param = substr($a_output, $start + 9, $end - $start - 9);

            $param = explode(";", $param);
            if (is_numeric($param[0]) && is_numeric($param[1]) && is_numeric($param[2])) {
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
                $h2 = substr($a_output, 0, $start) .
                    $map_gui->getHtml() .
                    substr($a_output, $end + 5);
                $a_output = $h2;
                $i++;
            }
            $start = strpos($a_output, "[[[[[Map;", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_output, "]]]]]", $start);
            }
        }

        return $a_output;
    }
}
