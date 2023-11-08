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
    protected DOMNode $table_pc_node;

    public function init(): void
    {
        $this->setType("td");
    }

    public function newRowAfter(int $cnt = 1): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();
        $parent_tr = $td->parentNode;
        $new_tr = $parent_tr->cloneNode(true);

        // remove pc ids
        if ($new_tr->hasAttribute("PCID")) {
            $new_tr->removeAttribute("PCID");
        }
        if ($next_tr = $parent_tr->nextSibling) {
            $new_tr = $next_tr->parentNode->insertBefore($new_tr, $next_tr);
        } else {
            $parent_table = $parent_tr->parentNode;
            $new_tr = $parent_table->appendChild($new_tr);
        }

        // remove td content of new row
        $this->deleteRowContent($new_tr);
        $this->fixHideAndSpans();
    }


    public function newRowBefore(int $cnt = 1): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();
        $parent_tr = $td->parentNode;
        $new_tr = $parent_tr->cloneNode(true);
        $new_tr = $parent_tr->parentNode->insertBefore($new_tr, $parent_tr);
        if ($new_tr->hasAttribute("PCID")) {
            $new_tr->removeAttribute("PCID");
        }

        // remove td content of new row
        $this->deleteRowContent($new_tr);
        $this->fixHideAndSpans();
    }


    /**
     * delete content of cells of a row (not the cells itself)
     */
    public function deleteRowContent(
        DOMNode $a_row_node
    ): void {
        // remove td content of row
        foreach ($a_row_node->childNodes as $td) {
            if ($td->hasAttribute("PCID")) {
                $td->removeAttribute("PCID");
            }
            foreach ($td->childNodes as $c) {
                $td->removeChild($c);
            }
        }
    }

    /**
     * delete content of a cell (not the cell itself)
     */
    public function deleteTDContent(
        DOMNode $a_td_node
    ): void {
        foreach ($a_td_node->childNodes as $child) {
            $a_td_node->removeChild($child);
        }
    }

    public function deleteRow(): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();
        $parent_tr = $td->parentNode;
        $parent_tr->parentNode->removeChild($parent_tr);
        $this->fixHideAndSpans();
    }

    public function newColAfter(int $cnt = 1): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;

        $parent_tr = $td->parentNode;
        $parent_table = $parent_tr->parentNode;

        // iterate all table rows
        foreach ($parent_table->childNodes as $row) {
            if ($row->nodeName == "TableRow") {
                // clone td at $col_nr
                $tds = $row->childNodes;
                $old_td = $tds->item($col_nr);
                $new_td = $old_td->cloneNode(true);

                if ($new_td->hasAttribute("PCID")) {
                    $new_td->removeAttribute("PCID");
                }

                // insert clone after $col_nr
                if ($next_td = $old_td->nextSibling) {
                    $new_td = $next_td->parentNode->insertBefore($new_td, $next_td);
                } else {
                    $new_td = $row->appendChild($new_td);
                }
                $this->deleteTDContent($new_td);
            }
        }
        $this->fixHideAndSpans();
    }

    public function newColBefore(int $cnt = 1): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;
        $parent_tr = $td->parentNode;
        $parent_table = $parent_tr->parentNode;

        // iterate all table rows
        foreach ($parent_table->childNodes as $row) {
            if ($row->nodeName == "TableRow") {
                // clone td at $col_nr
                $tds = $row->childNodes;
                $old_td = $tds->item($col_nr);
                $new_td = $old_td->cloneNode(true);

                if ($new_td->hasAttribute("PCID")) {
                    $new_td->removeAttribute("PCID");
                }

                // insert clone before $col_nr
                $new_td = $old_td->parentNode->insertBefore($new_td, $old_td);
                $this->deleteTDContent($new_td);
            }
        }
        $this->fixHideAndSpans();
    }

    public function deleteCol(): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;

        $parent_tr = $td->parentNode;
        $parent_table = $parent_tr->parentNode;

        // iterate all table rows
        foreach ($parent_table->childNodes as $row) {
            if ($row->nodeName == "TableRow") {
                // unlink td at $col_nr
                $tds = $row->childNodes;
                $tds->item($col_nr)->parentNode->removeChild($tds->item($col_nr));
            }
        }
        $this->fixHideAndSpans();
    }

    public function moveRowDown(): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();
        $tr = $td->parentNode;
        $next = $tr->nextSibling;
        $next_copy = $next->cloneNode(true);
        $next_copy = $tr->parentNode->insertBefore($next_copy, $tr);
        $next->parentNode->removeChild($next);
        $this->fixHideAndSpans();
    }

    public function moveRowUp(): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();
        $tr = $td->parentNode;
        $prev = $tr->previousSibling;
        $tr_copy = $tr->cloneNode(true);
        $tr_copy = $prev->parentNode->insertBefore($tr_copy, $prev);
        $tr->parentNode->removeChild($tr);
        $this->fixHideAndSpans();
    }

    public function moveColRight(): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;

        $parent_tr = $td->parentNode;
        $parent_table = $parent_tr->parentNode;

        // iterate all table rows
        foreach ($parent_table->childNodes as $row) {
            if ($row->nodeName == "TableRow") {
                $tds = $row->childNodes;
                $td = $tds->item($col_nr);
                $next = $td->nextSibling;
                $next_copy = $next->cloneNode(true);
                $next_copy = $td->parentNode->insertBefore($next_copy, $td);
                $next->parentNode->removeChild($next);
            }
        }
        $this->fixHideAndSpans();
    }

    public function moveColLeft(): void
    {
        $this->initTablePCNode();
        $td = $this->getDomNode();

        // determine current column nr
        $hier_id = $this->getHierId();
        $parts = explode("_", $hier_id);
        $col_nr = array_pop($parts);
        $col_nr--;

        $parent_tr = $td->parentNode;
        $parent_table = $parent_tr->parentNode;

        // iterate all table rows
        foreach ($parent_table->childNodes as $row) {
            if ($row->nodeName == "TableRow") {
                $tds = $row->childNodes;
                $td = $tds->item($col_nr);
                $prev = $td->previousSibling;
                $td_copy = $td->cloneNode(true);
                $td_copy = $prev->parentNode->insertBefore($td_copy, $prev);
                $td->parentNode->removeChild($td);
            }
        }
        $this->fixHideAndSpans();
    }

    public function initTablePCNode(): void
    {
        $td = $this->getDomNode();
        $tr = $td->parentNode;
        $table = $tr->parentNode;
        $this->table_pc_node = $table->parentNode;
    }

    public function fixHideAndSpans(): void
    {
        $table_obj = new ilPCTable($this->getPage());
        $table_obj->setDomNode($this->table_pc_node);
        $table_obj->readHierId();
        $table_obj->readPCId();
        $table_obj->fixHideAndSpans();
    }
}
