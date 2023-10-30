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
 * Class ilPCTable
 * Table content object (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCTable extends ilPageContent
{
    public function init(): void
    {
        $this->setType("tab");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode(
            $a_hier_id,
            $a_pc_id,
            "Table",
            ["Language" => ""]
        );
    }

    public function addRow(): DOMNode
    {
        $new_tr = $this->dom_doc->createElement("TableRow");
        $new_tr = $this->getChildNode()->appendChild($new_tr);
        return $new_tr;
    }

    public function addCell(
        DOMNode $aRow,
        string $a_data = "",
        string $a_lang = ""
    ): DOMNode {
        $new_td = $this->dom_doc->createElement("TableData");
        $new_td = $aRow->appendChild($new_td);

        // insert data if given
        if ($a_data != "") {
            $new_pg = $this->getNewPageContentNode();
            $new_par = $this->dom_doc->createElement("Paragraph");
            $new_par = $new_pg->appendChild($new_par);
            $new_par->setAttribute("Language", $a_lang);
            $new_par->setAttribute("Characteristic", "TableContent");
            $this->dom_util->setContent($new_par, $a_data);
            $new_td->appendChild($new_pg);
        }

        return $new_td;
    }

    /**
     * Get cell text of row $i and cell $j
     */
    public function getCellText(int $i, int $j): string
    {
        $cell_par = $this->getCellNode($i, $j, false);
        if (!is_null($cell_par)) {
            $content = "";
            foreach ($cell_par->childNodes as $c) {
                $content .= $this->dom_util->dump($c);
            }
            return $content;
        } else {
            return "";
        }
    }

    /**
     * Get cell paragraph node of row $i and cell $j
     */
    public function getCellNode(int $i, int $j, bool $create_if_not_exists = false): ?DOMNode
    {
        $path = "//PageContent[@HierId='" . $this->getHierId() . "']" .
            "/Table/TableRow[" . ($i + 1) . "]/TableData[" . ($j + 1) . "]/PageContent[1]/Paragraph[1]";
        $nodes = $this->dom_util->path($this->dom_doc, $path);
        if (!is_null($nodes->item(0))) {
            return $nodes->item(0);
        } else {		// no node -> delete all childs and create paragraph
            if (!$create_if_not_exists) {
                return null;
            }
            $path2 = "//PageContent[@HierId='" . $this->getHierId() . "']" .
                "/Table/TableRow[" . ($i + 1) . "]/TableData[" . ($j + 1) . "]";
            $nodes2 = $this->dom_util->path($this->dom_doc, $path2);

            $td_node = $nodes2->item(0);

            if (!is_null($td_node)) {
                // delete children of paragraph node
                foreach ($td_node->childNodes as $child) {
                    $td_node->removeChild($child);
                }

                // create page content and paragraph node here.
                $pc_node = $this->getNewPageContentNode();
                $pc_node = $td_node->appendChild($pc_node);
                $par_node = $this->dom_doc->createElement("Paragraph");
                $par_node = $pc_node->appendChild($par_node);
                $par_node->setAttribute("Characteristic", "TableContent");
                $par_node->setAttribute(
                    "Language",
                    $this->getLanguage()
                );

                return $par_node;
            }
        }

        return null;
    }

    /**
     * Get cell paragraph node of row $i and cell $j
     */
    public function getTableDataNode(int $i, int $j): ?php4DOMElement
    {
        $xpc = xpath_new_context($this->dom);
        $path = "//PageContent[@HierId='" . $this->getHierId() . "']" .
            "/Table/TableRow[$i+1]/TableData[$j+1]";
        $res = xpath_eval($xpc, $path);

        if (isset($res->nodeset[0])) {
            return $res->nodeset[0];
        }
        return null;
    }

    /**
     * add rows to table
     */
    public function addRows(int $a_nr_rows, int $a_nr_cols): void
    {
        for ($i = 1; $i <= $a_nr_rows; $i++) {
            $aRow = $this->addRow();
            for ($j = 1; $j <= $a_nr_cols; $j++) {
                $this->addCell($aRow);
            }
        }
    }

    /**
     * import from table
     */
    public function importSpreadsheet(
        string $a_lang,
        string $a_data
    ): void {
        $max_cols = 0;

        str_replace($a_data, "\r", "\n");
        str_replace($a_data, "\n\n", "\n");
        $target_rows = array();
        $rows = explode("\n", $a_data);

        // get maximum of cols in a row and
        // put data in target_row arrays
        foreach ($rows as $row) {
            $cells = explode("\t", $row);
            if (count($cells) === 1) {
                $cells = explode(";", $row);
            }
            $max_cols = ($max_cols > count($cells))
                ? $max_cols
                : count($cells);
            $target_rows[] = $cells;
        }

        // iterate target row arrays and insert data
        foreach ($target_rows as $row) {
            $aRow = $this->addRow();
            for ($j = 0; $j < $max_cols; $j++) {
                // mask html
                $data = str_replace("&", "&amp;", ($row[$j] ?? ""));
                $data = str_replace("<", "&lt;", $data);
                $data = str_replace(">", "&gt;", $data);

                $this->addCell($aRow, $data, $a_lang);
            }
        }
    }

    public function getLanguage(): string
    {
        return $this->getTableAttribute("Language");
    }

    public function setLanguage(string $a_lang): void
    {
        if ($a_lang != "") {
            $this->setTableAttribute("Language", $a_lang);
        }
    }

    public function getWidth(): string
    {
        return $this->getTableAttribute("Width");
    }

    public function setWidth(string $a_width): void
    {
        $this->setTableAttribute("Width", $a_width);
    }

    public function getBorder(): string
    {
        return $this->getTableAttribute("Border");
    }

    public function setBorder(string $a_border): void
    {
        $this->setTableAttribute("Border", $a_border);
    }

    public function getCellSpacing(): string
    {
        return $this->getTableAttribute("CellSpacing");
    }

    public function setCellSpacing(string $a_spacing): void
    {
        $this->setTableAttribute("CellSpacing", $a_spacing);
    }

    public function getCellPadding(): string
    {
        return $this->getTableAttribute("CellPadding");
    }

    public function setCellPadding(string $a_padding): void
    {
        $this->setTableAttribute("CellPadding", $a_padding);
    }

    public function setHorizontalAlign(string $a_halign): void
    {
        $this->getChildNode()->setAttribute("HorizontalAlign", $a_halign);
    }

    public function getHorizontalAlign(): string
    {
        return $this->getTableAttribute("HorizontalAlign");
    }

    /**
     * set width of table data cell
     */
    public function setTDWidth(
        string $a_hier_id,
        string $a_width,
        string $a_pc_id = ""
    ): void {
        if ($a_pc_id == "") {
            $path = "//TableData[@HierId = '" . $a_hier_id . "']";
        } else {
            $path = "//TableData[@PCID = '" . $a_pc_id . "']";
        }
        $nodes = $this->dom_util->path($this->dom_doc, $path);

        if (count($nodes) == 1) {
            if ($a_width != "") {
                $nodes->item(0)->setAttribute("Width", $a_width);
            } else {
                if ($nodes->item(0)->hasAttribute("Width")) {
                    $nodes->item(0)->removeAttribute("Width");
                }
            }
        }
    }

    public function setTDSpans(
        array $a_colspans,
        array $a_rowspans
    ): void {
        $y = 0;
        $rows = $this->getChildNode()->childNodes;
        foreach ($rows as $row) {
            if ($row->nodeName == "TableRow") {
                $x = 0;
                $cells = $row->childNodes;
                foreach ($cells as $cell) {
                    if ($cell->nodeName == "TableData") {
                        $ckey = $cell->getAttribute("HierId") . ":" . $cell->getAttribute("PCID");
                        if ((int) ($a_colspans[$ckey] ?? 0) > 1) {
                            $cell->setAttribute("ColSpan", (int) $a_colspans[$ckey]);
                        } else {
                            if ($cell->hasAttribute("ColSpan")) {
                                $cell->removeAttribute("ColSpan");
                            }
                        }
                        if ((int) ($a_rowspans[$ckey] ?? 0) > 1) {
                            $cell->setAttribute("RowSpan", (int) $a_rowspans[$ckey]);
                        } else {
                            if ($cell->hasAttribute("RowSpan")) {
                                $cell->removeAttribute("RowSpan");
                            }
                        }
                    }
                    $x++;
                }
                $y++;
            }
        }
        $this->fixHideAndSpans();
    }

    /**
     * Fix Hide and Spans. Reduces col and rowspans that are to high.
     * Sets Hide attribute for all cells that are hidden due to other span
     * attributes. Sets hidden cells to empty.
     */
    public function fixHideAndSpans(): void
    {
        // first: get max x and y
        $max_x = $max_y = 0;
        $y = 0;
        $rows = $this->getChildNode()->childNodes;

        foreach ($rows as $row) {
            if ($row->nodeName == "TableRow") {
                $x = 0;
                $cells = $row->childNodes;
                foreach ($cells as $cell) {
                    if ($cell->nodeName == "TableData") {
                        $max_x = max($max_x, $x);
                        $max_y = max($max_y, $y);
                    }
                    $x++;
                }
                $y++;
            }
        }

        // second: fix hidden/colspans for all cells
        $y = 0;
        $colspans = [];
        $rowspans = [];
        $rows = $this->getChildNode()->childNodes;
        foreach ($rows as $row) {
            if ($row->nodeName == "TableRow") {
                $x = 0;
                $cells = $row->childNodes;
                foreach ($cells as $cell) {
                    if ($cell->nodeName == "TableData") {
                        $cspan = max(1, (int) $cell->getAttribute("ColSpan"));
                        $rspan = max(1, (int) $cell->getAttribute("RowSpan"));

                        // if col or rowspan is to high: reduce it to the max
                        if ($cspan > $max_x - $x + 1) {
                            $cell->setAttribute("ColSpan", $max_x - $x + 1);
                            $cspan = $max_x - $x + 1;
                        }
                        if ($rspan > $max_y - $y + 1) {
                            $cell->setAttribute("RowSpan", $max_y - $y + 1);
                            $rspan = $max_y - $y + 1;
                        }

                        // check hidden status
                        if ($this->checkCellHidden($colspans, $rowspans, $x, $y)) {
                            // hidden: set hidden flag, remove col and rowspan
                            $cell->setAttribute("Hidden", "Y");
                            $cspan = 1;
                            $rspan = 1;
                            if ($cell->hasAttribute("ColSpan")) {
                                $cell->removeAttribute("ColSpan");
                            }
                            if ($cell->hasAttribute("RowSpan")) {
                                $cell->removeAttribute("RowSpan");
                            }
                            $this->makeEmptyCell($cell);
                        } else {
                            // not hidden: remove hidden flag if existing
                            if ($cell->hasAttribute("Hidden")) {
                                $cell->removeAttribute("Hidden");
                            }
                        }

                        $colspans[$x][$y] = $cspan;
                        $rowspans[$x][$y] = $rspan;
                    }
                    $x++;
                }
                $y++;
            }
        }
    }


    public function makeEmptyCell(DomNode $td_node): void
    {
        // delete children of paragraph node
        foreach ($td_node->childNodes as $child) {
            $td_node->removeChild($child);
        }
    }

    /**
     * Check hidden status
     */
    public function checkCellHidden(array $colspans, array $rowspans, int $x, int $y): bool
    {
        for ($i = 0; $i <= $x; $i++) {
            for ($j = 0; $j <= $y; $j++) {
                if ($i != $x || $j != $y) {
                    if ((($i + $colspans[$i][$j] > $x) &&
                        ($j + $rowspans[$i][$j] > $y))) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get all cell classes
     */
    public function getAllCellClasses(): array
    {
        $classes = array();
        $rows = $this->getChildNode()->childNodes;
        foreach ($rows as $row) {
            if ($row->nodeName == "TableRow") {
                $cells = $row->childNodes;
                foreach ($cells as $cell) {
                    if ($cell->nodeName == "TableData") {
                        $classes[$cell->getAttribute("HierId") . ":" . $cell->getAttribute("PCID")]
                            = $cell->getAttribute("Class");
                    }
                }
            }
        }

        return $classes;
    }

    public function getAllCellAlignments(): array
    {
        $classes = array();
        $rows = $this->getChildNode()->childNodes;
        foreach ($rows as $row) {
            if ($row->nodeName == "TableRow") {
                $cells = $row->childNodes;
                foreach ($cells as $cell) {
                    if ($cell->nodeName == "TableData") {
                        $classes[$cell->getAttribute("HierId") . ":" . $cell->getAttribute("PCID")]
                            = $cell->getAttribute("HorizontalAlign");
                    }
                }
            }
        }

        return $classes;
    }

    /**
     * Get all cell spans
     */
    public function getAllCellSpans(): array
    {
        $spans = array();
        $rows = $this->getChildNode()->childNodes;
        $y = 0;
        $max_x = 0;
        $max_y = 0;
        foreach ($rows as $row) {
            if ($row->nodeName == "TableRow") {
                $x = 0;
                $cells = $row->childNodes;
                foreach ($cells as $cell) {
                    if ($cell->nodeName == "TableData") {
                        $spans[$cell->getAttribute("HierId") . ":" . $cell->getAttribute("PCID")]
                            = array("x" => $x, "y" => $y, "colspan" => $cell->getAttribute("ColSpan"),
                                "rowspan" => $cell->getAttribute("RowSpan"));
                        $max_x = max($max_x, $x);
                        $max_y = max($max_y, $y);
                    }
                    $x++;
                }
                $y++;
            }
        }
        foreach ($spans as $k => $v) {
            $spans[$k]["max_x"] = $max_x;
            $spans[$k]["max_y"] = $max_y;
        }

        return $spans;
    }

    /**
     * Get all cell widths
     * @return array array of cell style classes
     */
    public function getAllCellWidths(): array
    {
        $widths = array();
        $rows = $this->getChildNode()->childNodes;
        foreach ($rows as $row) {
            if ($row->nodeName == "TableRow") {
                $cells = $row->childNodes;
                foreach ($cells as $cell) {
                    if ($cell->nodeName == "TableData") {
                        $widths[$cell->getAttribute("HierId") . ":" . $cell->getAttribute("PCID")]
                            = $cell->getAttribute("Width");
                    }
                }
            }
        }

        return $widths;
    }

    /**
     * set class of table data cell
     */
    public function setTDClass(
        string $a_hier_id,
        string $a_class,
        string $a_pc_id = ""
    ): void {
        if ($a_pc_id == "") {
            $path = "//TableData[@HierId = '" . $a_hier_id . "']";
        } else {
            $path = "//TableData[@PCID = '" . $a_pc_id . "']";
        }
        $nodes = $this->dom_util->path($this->dom_doc, $path);
        if (count($nodes) == 1) {
            if ($a_class != "") {
                $nodes->item(0)->setAttribute("Class", $a_class);
            } else {
                if ($nodes->item(0)->hasAttribute("Class")) {
                    $nodes->item(0)->removeAttribute("Class");
                }
            }
        }
    }

    /**
     * set alignment of table data cell
     */
    public function setTDAlignment(
        string $a_hier_id,
        string $a_class,
        string $a_pc_id = ""
    ): void {
        if ($a_pc_id == "") {
            $path = "//TableData[@HierId = '" . $a_hier_id . "']";
        } else {
            $path = "//TableData[@PCID = '" . $a_pc_id . "']";
        }
        $nodes = $this->dom_util->path($this->dom_doc, $path);
        if (count($nodes) == 1) {
            if ($a_class != "") {
                $nodes->item(0)->setAttribute("HorizontalAlign", $a_class);
            } else {
                if ($nodes->item(0)->hasAttribute("HorizontalAlign")) {
                    $nodes->item(0)->removeAttribute("HorizontalAlign");
                }
            }
        }
    }

    public function getCaption(): string
    {
        $hier_id = $this->getHierId();
        if (!empty($hier_id)) {
            $path = "//PageContent[@HierId = '" . $hier_id . "']/Table/Caption";
            $nodes = $this->dom_util->path($this->dom_doc, $path);
            if (count($nodes) == 1) {
                return $this->dom_util->getContent($nodes->item(0));
            }
        }
        return "";
    }

    /**
     * get caption alignment (Top | Bottom)
     */
    public function getCaptionAlign(): string
    {
        $hier_id = $this->getHierId();
        if (!empty($hier_id)) {
            $path = "//PageContent[@HierId = '" . $hier_id . "']/Table/Caption";
            $nodes = $this->dom_util->path($this->dom_doc, $path);
            if (count($nodes) == 1) {
                return $nodes->item(0)->getAttribute("Align");
            }
        }
        return "";
    }

    public function setCaption(string $a_content, string $a_align): void
    {
        if ($a_content != "") {
            $this->dom_util->setFirstOptionalElement(
                $this->getChildNode(),
                "Caption",
                array("Summary", "TableRow"),
                $a_content,
                array("Align" => $a_align)
            );
        } else {
            $this->dom_util->deleteAllChildsByName(
                $this->getChildNode(),
                array("Caption")
            );
        }
    }

    public function setFirstRowStyle(
        string $a_class
    ): void {
        foreach ($this->getChildNode()->childNodes as $child) {
            if ($child->nodeName == "TableRow") {
                foreach ($child->childNodes as $gchild) {
                    if ($gchild->nodeName == "TableData") {
                        $gchild->setAttribute("Class", $a_class);
                    }
                }
                return;
            }
        }
    }

    /**
     * Set Style Class of table
     */
    public function setClass(string $a_class): void
    {
        $this->setTableAttribute("Class", $a_class);
    }

    public function getClass(): string
    {
        return $this->getTableAttribute("Class");
    }

    public function setTemplate(string $a_template): void
    {
        $this->setTableAttribute("Template", $a_template);
    }

    public function getTemplate(): string
    {
        return $this->getTableAttribute("Template");
    }

    public function setHeaderRows(int $a_nr): void
    {
        $this->setTableAttribute("HeaderRows", $a_nr);
    }

    public function getHeaderRows(): int
    {
        return (int) $this->getTableAttribute("HeaderRows");
    }

    public function setFooterRows(int $a_nr): void
    {
        $this->setTableAttribute("FooterRows", $a_nr);
    }

    public function getFooterRows(): int
    {
        return (int) $this->getTableAttribute("FooterRows");
    }

    public function setHeaderCols(int $a_nr): void
    {
        $this->setTableAttribute("HeaderCols", $a_nr);
    }

    public function getHeaderCols(): int
    {
        return (int) $this->getTableAttribute("HeaderCols");
    }

    public function setFooterCols(int $a_nr): void
    {
        $this->setTableAttribute("FooterCols", $a_nr);
    }

    public function getFooterCols(): int
    {
        return (int) $this->getTableAttribute("FooterCols");
    }

    /**
     * Set attribute of table tag
     */
    protected function setTableAttribute(
        string $a_attr,
        string $a_value
    ): void {
        if (!empty($a_value)) {
            $this->getChildNode()->setAttribute($a_attr, $a_value);
        } else {
            if ($this->getChildNode()->hasAttribute($a_attr)) {
                $this->getChildNode()->removeAttribute($a_attr);
            }
        }
    }

    public function getTableAttribute(string $a_attr): string
    {
        if (!is_null($this->getChildNode())) {
            return  $this->getChildNode()->getAttribute($a_attr);
        }
        return "";
    }

    public static function getLangVars(): array
    {
        return array("ed_insert_dtable", "ed_insert_atable","ed_new_row_after", "ed_new_row_before",
            "ed_new_col_after", "ed_new_col_before", "ed_delete_col",
            "ed_delete_row", "ed_edit_data", "ed_row_up", "ed_row_down",
            "ed_col_left", "ed_col_right");
    }


    public static function handleCopiedContent(
        DOMDocument $a_domdoc,
        bool $a_self_ass = true,
        bool $a_clone_mobs = false,
        int $new_parent_id = 0,
        int $obj_copy_id = 0
    ): void {
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query("//Table");
        foreach ($nodes as $node) {
            $node->removeAttribute("Id");
        }
    }

    public function getModel(): ?stdClass
    {
        $model = new \stdClass();

        $y = 0;
        foreach ($this->getChildNode()->childNodes as $row) {
            if ($row->nodeName == "TableRow") {
                $x = 0;
                foreach ($row->childNodes as $cell) {
                    if ($cell->nodeName == "TableData") {
                        $text = ilPCParagraph::xml2output(
                            $this->getCellText($y, $x),
                            true,
                            false
                        );
                        $text = ilPCParagraphGUI::xml2outputJS($text);
                        $model->content[$y][$x] = $text;
                    }
                    $x++;
                }
                $y++;
            }
        }

        if ($this->getTemplate() != "") {
            $model->template = $this->getTemplate();
            $model->characteristic = "";
        } else {
            $model->characteristic = $this->getClass();
            $model->template = "";
        }

        $model->hasHeaderRows = ($this->getHeaderRows() > 0);

        return $model;
    }
}
