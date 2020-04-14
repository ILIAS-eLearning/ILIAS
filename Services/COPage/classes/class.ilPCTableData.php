<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilPCTableData
*
* Table Data content object - a table cell (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTableData extends ilPageContent
{
    public $dom;

    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("td");
    }

    /**
    * insert new row after cell
    */
    public function newRowAfter()
    {
        $this->initTablePCNode();
        $td = $this->getNode();
        $parent_tr = $td->parent_node();
        $new_tr = $parent_tr->clone_node(true);
        
        // remove pc ids
        if ($new_tr->has_attribute("PCID")) {
            $new_tr->remove_attribute("PCID");
        }
        if ($next_tr = $parent_tr->next_sibling()) {
            $new_tr = $next_tr->insert_before($new_tr, $next_tr);
        } else {
            $parent_table = $parent_tr->parent_node();
            $new_tr = $parent_table->append_child($new_tr);
        }

        // remove td content of new row
        $this->deleteRowContent($new_tr);
        $this->fixHideAndSpans();
    }


    /**
    * insert new row after cell
    */
    public function newRowBefore()
    {
        $this->initTablePCNode();
        $td = $this->getNode();
        $parent_tr = $td->parent_node();
        $new_tr = $parent_tr->clone_node(true);
        $new_tr = $parent_tr->insert_before($new_tr, $parent_tr);
        if ($new_tr->has_attribute("PCID")) {
            $new_tr->remove_attribute("PCID");
        }

        // remove td content of new row
        $this->deleteRowContent($new_tr);
        $this->fixHideAndSpans();
    }


    /**
    * delete content of cells of a row (not the cells itself)
    *
    * @access private
    */
    public function deleteRowContent(&$a_row_node)
    {
        // remove td content of row
        $tds = $a_row_node->child_nodes();
        for ($i = 0; $i < count($tds); $i++) {
            if ($tds[$i]->has_attribute("PCID")) {
                $tds[$i]->remove_attribute("PCID");
            }
            $td_childs = $tds[$i]->child_nodes();
            for ($j = 0; $j < count($td_childs); $j++) {
                $tds[$i]->remove_child($td_childs[$j]);
            }
        }
    }

    /**
    * delete content of a cell (not the cell itself)
    *
    * @access private
    */
    public function deleteTDContent(&$a_td_node)
    {
        $td_childs = $a_td_node->child_nodes();
        for ($j = 0; $j < count($td_childs); $j++) {
            $a_td_node->remove_child($td_childs[$j]);
        }
    }


    /**
    * delete row of cell
    */
    public function deleteRow()
    {
        $this->initTablePCNode();
        $td = $this->getNode();
        $parent_tr = $td->parent_node();
        $parent_tr->unlink($parent_tr);
        $this->fixHideAndSpans();
    }


    /**
    * insert new column after cell
    */
    public function newColAfter()
    {
        $this->initTablePCNode();
        $td = $this->getNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;

        $parent_tr = $td->parent_node();
        $parent_table = $parent_tr->parent_node();

        // iterate all table rows
        $rows = $parent_table->child_nodes();
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]->node_name() == "TableRow") {
                // clone td at $col_nr
                $tds = $rows[$i]->child_nodes();
                $new_td = $tds[$col_nr]->clone_node(true);
                
                if ($new_td->has_attribute("PCID")) {
                    $new_td->remove_attribute("PCID");
                }

                // insert clone after $col_nr
                if ($next_td = $tds[$col_nr]->next_sibling()) {
                    $new_td = $next_td->insert_before($new_td, $next_td);
                } else {
                    $new_td = $rows[$i]->append_child($new_td);
                }
                $this->deleteTDContent($new_td);
            }
        }
        $this->fixHideAndSpans();
    }

    /**
    * insert new column before cell
    */
    public function newColBefore()
    {
        $this->initTablePCNode();
        $td = $this->getNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;

        $parent_tr = $td->parent_node();
        $parent_table = $parent_tr->parent_node();

        // iterate all table rows
        $rows = $parent_table->child_nodes();
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]->node_name() == "TableRow") {
                // clone td at $col_nr
                $tds = $rows[$i]->child_nodes();
                $new_td = $tds[$col_nr]->clone_node(true);
                
                if ($new_td->has_attribute("PCID")) {
                    $new_td->remove_attribute("PCID");
                }

                // insert clone before $col_nr
                $new_td = $tds[$col_nr]->insert_before($new_td, $tds[$col_nr]);
                $this->deleteTDContent($new_td);
            }
        }
        $this->fixHideAndSpans();
    }

    /**
    * delete column of cell
    */
    public function deleteCol()
    {
        $this->initTablePCNode();
        $td = $this->getNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;

        $parent_tr = $td->parent_node();
        $parent_table = $parent_tr->parent_node();

        // iterate all table rows
        $rows = $parent_table->child_nodes();
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]->node_name() == "TableRow") {
                // unlink td at $col_nr
                $tds = $rows[$i]->child_nodes();
                $tds[$col_nr]->unlink($tds[$col_nr]);
            }
        }
        $this->fixHideAndSpans();
    }

    /**
    * move row down
    */
    public function moveRowDown()
    {
        $this->initTablePCNode();
        $td = $this->getNode();
        $tr = $td->parent_node();
        $next = $tr->next_sibling();
        $next_copy = $next->clone_node(true);
        $next_copy = $tr->insert_before($next_copy, $tr);
        $next->unlink($next);
        $this->fixHideAndSpans();
    }

    /**
    * move row up
    */
    public function moveRowUp()
    {
        $this->initTablePCNode();
        $td = $this->getNode();
        $tr = $td->parent_node();
        $prev = $tr->previous_sibling();
        $tr_copy = $tr->clone_node(true);
        $tr_copy = $prev->insert_before($tr_copy, $prev);
        $tr->unlink($tr);
        $this->fixHideAndSpans();
    }

    /**
    * move column right
    */
    public function moveColRight()
    {
        $this->initTablePCNode();
        $td = $this->getNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;

        $parent_tr = $td->parent_node();
        $parent_table = $parent_tr->parent_node();

        // iterate all table rows
        $rows = $parent_table->child_nodes();
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]->node_name() == "TableRow") {
                $tds = $rows[$i]->child_nodes();
                $td = $tds[$col_nr];
                //$td = $this->getNode();
                $next = $td->next_sibling();
                $next_copy = $next->clone_node(true);
                $next_copy = $td->insert_before($next_copy, $td);
                $next->unlink($next);
            }
        }
        $this->fixHideAndSpans();
    }

    /**
    * move column left
    */
    public function moveColLeft()
    {
        $this->initTablePCNode();
        $td = $this->getNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;

        $parent_tr = $td->parent_node();
        $parent_table = $parent_tr->parent_node();

        // iterate all table rows
        $rows = $parent_table->child_nodes();
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]->node_name() == "TableRow") {
                $tds = $rows[$i]->child_nodes();
                $td = $tds[$col_nr];
                $prev = $td->previous_sibling();
                $td_copy = $td->clone_node(true);
                $td_copy = $prev->insert_before($td_copy, $prev);
                $td->unlink($td);
            }
        }
        $this->fixHideAndSpans();
    }

    /**
    * Table PC Node
    */
    public function initTablePCNode()
    {
        $td = $this->getNode();
        $tr = $td->parent_node();
        $table = $tr->parent_node();
        $this->table_pc_node = $table->parent_node();
    }
    
    /**
    * Fix hide attribute and spans
    */
    public function fixHideAndSpans()
    {
        include_once("./Services/COPage/classes/class.ilPCTable.php");
        $table_obj = new ilPCTable($this->getPage());
        $table_obj->setNode($this->table_pc_node);
        $table_obj->readHierId();
        $table_obj->readPCId();
        $table_obj->fixHideAndSpans();
    }
}
