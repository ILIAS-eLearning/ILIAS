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
 * Grid element
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ServicesCOPage
 */
class ilPCGrid extends ilPageContent
{
    protected $grid_node;

    /**
     * Init page content component.
     */
    public function init()
    {
        $this->setType("grid");
    }

    /**
     * Set content node
     * @param object $a_node
     */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->grid_node = $a_node->first_child();		// this is the Tabs node
    }

    /**
     * Get sizes
     *
     *  Note that these are mapped to (BS3):
     *  s > .col-xs, m > .col-sm, l > .col-md, xl > .col-lg
     *
     * @return array
     */
    public static function getSizes()
    {
        return array("s" => "s", "m" => "m", "l" => "l", "xl" => "xl");
    }

    /**
     * Get widths
     * @return array
     */
    public static function getWidths()
    {
        return array(
            "1" => "1/12", "2" => "2/12", "3" => "3/12",
            "4" => "4/12", "5" => "5/12", "6" => "6/12",
            "7" => "7/12", "8" => "8/12", "9" => "9/12",
            "10" => "10/12", "11" => "11/12", "12" => "12/12"
        );
    }

    /**
    * Create new Grid node
    */
    public function create($a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->grid_node = $this->dom->create_element("Grid");
        $this->grid_node = $this->node->append_child($this->grid_node);
    }

    /**
     * Set attribute of grid tag
     *
     * @param string $a_attr	attribute name
     * @param string $a_value attribute value
     */
    /*
    protected function setTabsAttribute($a_attr, $a_value)
    {
        if (!empty($a_value))
        {
            $this->grid_node->set_attribute($a_attr, $a_value);
        }
        else
        {
            if ($this->grid_node->has_attribute($a_attr))
            {
                $this->grid_node->remove_attribute($a_attr);
            }
        }
    }*/


    /**
     * Save positions of grid cells
     *
     * @param array $a_pos
     */
    public function savePositions($a_pos)
    {
        asort($a_pos);

        $childs = $this->grid_node->child_nodes();
        $nodes = array();
        for ($i = 0; $i < count($childs); $i++) {
            if ($childs[$i]->node_name() == "GridCell") {
                $pc_id = $childs[$i]->get_attribute("PCID");
                $hier_id = $childs[$i]->get_attribute("HierId");
                $nodes[$hier_id . ":" . $pc_id] = $childs[$i];
                $childs[$i]->unlink($childs[$i]);
            }
        }
        
        foreach ($a_pos as $k => $v) {
            if (is_object($nodes[$k])) {
                $nodes[$k] = $this->grid_node->append_child($nodes[$k]);
            }
        }
    }

    /**
     * Save widths of cells
     */
    public function saveWidths($a_width_s, $a_width_m, $a_width_l, $a_width_xl)
    {
        $cell_nodes = $this->grid_node->child_nodes();
        for ($i = 0; $i < count($cell_nodes); $i++) {
            if ($cell_nodes[$i]->node_name() == "GridCell") {
                $pc_id = $cell_nodes[$i]->get_attribute("PCID");
                $hier_id = $cell_nodes[$i]->get_attribute("HierId");
                $k = $hier_id . ":" . $pc_id;
                $cell_nodes[$i]->set_attribute("WIDTH_XS", "");
                $cell_nodes[$i]->set_attribute("WIDTH_S", $a_width_s[$k]);
                $cell_nodes[$i]->set_attribute("WIDTH_M", $a_width_m[$k]);
                $cell_nodes[$i]->set_attribute("WIDTH_L", $a_width_l[$k]);
                $cell_nodes[$i]->set_attribute("WIDTH_XL", $a_width_xl[$k]);
            }
        }
    }


    /**
     * Delete grid cell
     */
    public function deleteGridCell($a_hier_id, $a_pc_id)
    {
        $childs = $this->grid_node->child_nodes();
        for ($i = 0; $i < count($childs); $i++) {
            if ($childs[$i]->node_name() == "GridCell") {
                if ($a_pc_id == $childs[$i]->get_attribute("PCID") &&
                    $a_hier_id == $childs[$i]->get_attribute("HierId")) {
                    $childs[$i]->unlink($childs[$i]);
                }
            }
        }
    }

    /**
     * Add grid cell
     */
    public function addGridCell($a_s, $a_m, $a_l, $a_xl)
    {
        $new_item = $this->dom->create_element("GridCell");
        $new_item = $this->grid_node->append_child($new_item);
        //$new_item->set_attribute("xs", $a_xs);
        $new_item->set_attribute("WIDTH_XS", "");
        $new_item->set_attribute("WIDTH_S", $a_s);
        $new_item->set_attribute("WIDTH_M", $a_m);
        $new_item->set_attribute("WIDTH_L", $a_l);
        $new_item->set_attribute("WIDTH_XL", $a_xl);
    }

    /**
     * Add a cell
     */
    public function addCell()
    {
        $new_item = $this->dom->create_element("GridCell");
        $new_item->set_attribute("WIDTH_XS", "");
        $new_item->set_attribute("WIDTH_S", "");
        $new_item->set_attribute("WIDTH_M", "");
        $new_item->set_attribute("WIDTH_L", "");
        $new_item->set_attribute("WIDTH_XL", "");
        $this->grid_node->append_child($new_item);
    }

    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("pc_grid", "pc_grid_cell", "ed_delete_cell", "ed_cell_left", "ed_cell_right");
    }

    /**
     * Get Javascript files
     */
    public function getJavascriptFiles($a_mode)
    {
        return parent::getJavascriptFiles($a_mode);
    }

    /**
     * Get Javascript files
     */
    public function getCssFiles($a_mode)
    {
        return parent::getCssFiles($a_mode);
    }

    public function getCellData()
    {
        $cells = array();
        $cell_nodes = $this->grid_node->child_nodes();
        $k = 0;
        for ($i = 0; $i < count($cell_nodes); $i++) {
            if ($cell_nodes[$i]->node_name() == "GridCell") {
                $pc_id = $cell_nodes[$i]->get_attribute("PCID");
                $hier_id = $cell_nodes[$i]->get_attribute("HierId");
                $cells[] = array("pos" => $k,
                    "xs" => $cell_nodes[$i]->get_attribute("WIDTH_XS"),
                    "s" => $cell_nodes[$i]->get_attribute("WIDTH_S"),
                    "m" => $cell_nodes[$i]->get_attribute("WIDTH_M"),
                    "l" => $cell_nodes[$i]->get_attribute("WIDTH_L"),
                    "xl" => $cell_nodes[$i]->get_attribute("WIDTH_XL"),
                    "pc_id" => $pc_id, "hier_id" => $hier_id);
                $k++;
            }
        }

        return $cells;
    }
}
