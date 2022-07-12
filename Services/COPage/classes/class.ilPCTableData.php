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
 * Table Data content object - a table cell (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCTableData extends ilPageContent
{
    protected php4DOMElement $table_pc_node;

    public function init() : void
    {
        $this->setType("td");
    }

    public function newRowAfter() : void
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


    public function newRowBefore() : void
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
     */
    public function deleteRowContent(
        php4DOMElement $a_row_node
    ) : void {
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
     */
    public function deleteTDContent(
        php4DOMElement $a_td_node
    ) : void {
        $td_childs = $a_td_node->child_nodes();
        for ($j = 0; $j < count($td_childs); $j++) {
            $a_td_node->remove_child($td_childs[$j]);
        }
    }

    public function deleteRow() : void
    {
        $this->initTablePCNode();
        $td = $this->getNode();
        $parent_tr = $td->parent_node();
        $parent_tr->unlink($parent_tr);
        $this->fixHideAndSpans();
    }

    public function newColAfter() : void
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

    public function newColBefore() : void
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

    public function deleteCol() : void
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

    public function moveRowDown() : void
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

    public function moveRowUp() : void
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

    public function moveColRight() : void
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

    public function moveColLeft() : void
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

    public function initTablePCNode() : void
    {
        $td = $this->getNode();
        $tr = $td->parent_node();
        $table = $tr->parent_node();
        $this->table_pc_node = $table->parent_node();
    }
    
    public function fixHideAndSpans() : void
    {
        $table_obj = new ilPCTable($this->getPage());
        $table_obj->setNode($this->table_pc_node);
        $table_obj->readHierId();
        $table_obj->readPCId();
        $table_obj->fixHideAndSpans();
    }
}
