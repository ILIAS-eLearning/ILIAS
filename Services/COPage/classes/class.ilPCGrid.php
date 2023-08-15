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
 * Grid element
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCGrid extends ilPageContent
{
    /**
     * Init page content component.
     */
    public function init(): void
    {
        $this->setType("grid");
    }

    /**
     * Get sizes
     *
     *  Note that these are mapped to (BS3):
     *  s > .col-xs, m > .col-sm, l > .col-md, xl > .col-lg
     */
    public static function getSizes(): array
    {
        return array("s" => "s", "m" => "m", "l" => "l", "xl" => "xl");
    }

    /**
     * Get widths
     */
    public static function getWidths(): array
    {
        return array(
            "1" => "1/12", "2" => "2/12", "3" => "3/12",
            "4" => "4/12", "5" => "5/12", "6" => "6/12",
            "7" => "7/12", "8" => "8/12", "9" => "9/12",
            "10" => "10/12", "11" => "11/12", "12" => "12/12"
        );
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "Grid");
    }

    public function applyTemplate(
        int $post_layout_template,
        int $number_of_cells,
        int $s,
        int $m,
        int $l,
        int $xl
    ): void {
        switch ($post_layout_template) {
            case ilPCGridGUI::TEMPLATE_TWO_COLUMN:
                $this->addGridCell(12, 6, 6, 6);
                $this->addGridCell(12, 6, 6, 6);
                break;

            case ilPCGridGUI::TEMPLATE_THREE_COLUMN:
                $this->addGridCell(12, 4, 4, 4);
                $this->addGridCell(12, 4, 4, 4);
                $this->addGridCell(12, 4, 4, 4);
                break;

            case ilPCGridGUI::TEMPLATE_MAIN_SIDE:
                $this->addGridCell(12, 6, 8, 9);
                $this->addGridCell(12, 6, 4, 3);
                break;

            case ilPCGridGUI::TEMPLATE_TWO_BY_TWO:
                $this->addGridCell(12, 6, 6, 3);
                $this->addGridCell(12, 6, 6, 3);
                $this->addGridCell(12, 6, 6, 3);
                $this->addGridCell(12, 6, 6, 3);
                break;


            case ilPCGridGUI::TEMPLATE_MANUAL:
                for ($i = 0; $i < $number_of_cells; $i++) {
                    $this->addGridCell($s, $m, $l, $xl);
                }
                break;
        }
    }

    /**
     * Save positions of grid cells
     */
    public function savePositions(array $a_pos): void
    {
        asort($a_pos);

        $nodes = array();
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "GridCell") {
                $pc_id = $c->getAttribute("PCID");
                $hier_id = $c->getAttribute("HierId");
                $nodes[$hier_id . ":" . $pc_id] = $c;
            }
        }
        $this->dom_util->deleteAllChildsByName($this->getChildNode(), ["GridCell"]);

        foreach ($a_pos as $k => $v) {
            if (is_object($nodes[$k])) {
                $nodes[$k] = $this->getChildNode()->appendChild($nodes[$k]);
            }
        }
    }

    /**
     * Save widths of cells
     */
    public function saveWidths(
        array $a_width_s,
        array $a_width_m,
        array $a_width_l,
        array $a_width_xl
    ): void {
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "GridCell") {
                $pc_id = $c->getAttribute("PCID");
                $hier_id = $c->getAttribute("HierId");
                $k = $hier_id . ":" . $pc_id;
                $c->setAttribute("WIDTH_XS", "");
                $c->setAttribute("WIDTH_S", $a_width_s[$k]);
                $c->setAttribute("WIDTH_M", $a_width_m[$k]);
                $c->setAttribute("WIDTH_L", $a_width_l[$k]);
                $c->setAttribute("WIDTH_XL", $a_width_xl[$k]);
            }
        }
    }


    /**
     * Delete grid cell
     */
    public function deleteGridCell(
        string $a_hier_id,
        string $a_pc_id
    ): void {
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "GridCell") {
                if ($a_pc_id === $c->getAttribute("PCID") &&
                    $a_hier_id === $c->getAttribute("HierId")) {
                    $c->parentNode->removeChild($c);
                }
            }
        }
    }

    /**
     * Add grid cell
     */
    public function addGridCell(
        int $a_s,
        int $a_m,
        int $a_l,
        int $a_xl
    ): void {
        $new_item = $this->dom_doc->createElement("GridCell");
        $new_item = $this->getChildNode()->appendChild($new_item);
        //$new_item->set_attribute("xs", $a_xs);
        $new_item->setAttribute("WIDTH_XS", "");
        $new_item->setAttribute("WIDTH_S", $a_s);
        $new_item->setAttribute("WIDTH_M", $a_m);
        $new_item->setAttribute("WIDTH_L", $a_l);
        $new_item->setAttribute("WIDTH_XL", $a_xl);
    }

    /**
     * Add a cell
     */
    public function addCell(): void
    {
        $new_item = $this->dom_doc->createElement("GridCell");
        $new_item->setAttribute("WIDTH_XS", "");
        $new_item->setAttribute("WIDTH_S", "");
        $new_item->setAttribute("WIDTH_M", "");
        $new_item->setAttribute("WIDTH_L", "");
        $new_item->setAttribute("WIDTH_XL", "");
        $this->getChildNode()->appendChild($new_item);
    }

    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars(): array
    {
        return array("pc_grid", "pc_grid_cell", "ed_delete_cell", "ed_cell_left", "ed_cell_right");
    }

    public function getJavascriptFiles(string $a_mode): array
    {
        return parent::getJavascriptFiles($a_mode);
    }

    public function getCssFiles(string $a_mode): array
    {
        return parent::getCssFiles($a_mode);
    }

    public function getCellData(): array
    {
        $cells = array();
        $k = 0;
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "GridCell") {
                $pc_id = $c->getAttribute("PCID");
                $hier_id = $c->getAttribute("HierId");
                $cells[] = array("pos" => $k,
                    "xs" => $c->getAttribute("WIDTH_XS"),
                    "s" => $c->getAttribute("WIDTH_S"),
                    "m" => $c->getAttribute("WIDTH_M"),
                    "l" => $c->getAttribute("WIDTH_L"),
                    "xl" => $c->getAttribute("WIDTH_XL"),
                    "pc_id" => $pc_id, "hier_id" => $hier_id);
                $k++;
            }
        }

        return $cells;
    }
}
