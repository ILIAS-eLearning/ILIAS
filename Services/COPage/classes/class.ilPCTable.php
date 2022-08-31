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
    public php4DOMElement $tab_node;

    public function init(): void
    {
        $this->setType("tab");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->tab_node = $a_node->first_child();		// this is the Table node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->tab_node = $this->dom->create_element("Table");
        $this->tab_node = $this->node->append_child($this->tab_node);
        $this->tab_node->set_attribute("Language", "");
    }

    public function addRow(): php4DOMElement
    {
        $new_tr = $this->dom->create_element("TableRow");
        $new_tr = $this->tab_node->append_child($new_tr);
        return $new_tr;
    }

    public function addCell(
        php4DOMElement $aRow,
        string $a_data = "",
        string $a_lang = ""
    ): php4DOMElement {
        $new_td = $this->dom->create_element("TableData");
        $new_td = $aRow->append_child($new_td);

        // insert data if given
        if ($a_data != "") {
            $new_pg = $this->createPageContentNode(false);
            $new_par = $this->dom->create_element("Paragraph");
            $new_par = $new_pg->append_child($new_par);
            $new_par->set_attribute("Language", $a_lang);
            $new_par->set_attribute("Characteristic", "TableContent");
            $new_par->set_content($a_data);
            $new_td->append_child($new_pg);
        }

        return $new_td;
    }

    /**
     * Get cell text of row $i and cell $j
     */
    public function getCellText(int $i, int $j): string
    {
        $cell_par = $this->getCellNode($i, $j, false);

        if (is_object($cell_par)) {
            $content = "";
            $childs = $cell_par->child_nodes();
            for ($i = 0; $i < count($childs); $i++) {
                $content .= $this->dom->dump_node($childs[$i]);
            }
            return $content;
        } else {
            return "";
        }
    }

    /**
     * Get cell paragraph node of row $i and cell $j
     */
    public function getCellNode(int $i, int $j, bool $create_if_not_exists = false): ?php4DOMElement
    {
        $xpc = xpath_new_context($this->dom);
        $path = "//PageContent[@HierId='" . $this->getHierId() . "']" .
            "/Table/TableRow[$i+1]/TableData[$j+1]/PageContent[1]/Paragraph[1]";
        //echo "<br>++".$path;
        //]--//PageContent[@HierId='3']/Table/TableRow[+1]/TableData[0 style=+1]/PageContent[1]/Paragraph[1]
        $res = xpath_eval($xpc, $path);

        if (isset($res->nodeset[0])) {
            return $res->nodeset[0];
        } else {		// no node -> delete all childs and create paragraph
            if (!$create_if_not_exists) {
                return null;
            }
            $xpc2 = xpath_new_context($this->dom);
            $path2 = "//PageContent[@HierId='" . $this->getHierId() . "']" .
                "/Table/TableRow[" . ($i + 1) . "]/TableData[" . ($j + 1) . "]";
            //$path2 = "//PageContent";

            $res2 = xpath_eval($xpc2, $path2);

            $td_node = $res2->nodeset[0];

            if (is_object($td_node)) {
                // delete children of paragraph node
                $children = $td_node->child_nodes();
                for ($i = 0; $i < count($children); $i++) {
                    $td_node->remove_child($children[$i]);
                }

                // create page content and paragraph node here.
                $pc_node = $this->createPageContentNode(false);
                $pc_node = $td_node->append_child($pc_node);
                $par_node = $this->dom->create_element("Paragraph");
                $par_node = $pc_node->append_child($par_node);
                $par_node->set_attribute("Characteristic", "TableContent");
                $par_node->set_attribute(
                    "Language",
                    $this->getLanguage()
                );

                return $par_node;
            }
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
        $this->tab_node->set_attribute("HorizontalAlign", $a_halign);
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
        $xpc = xpath_new_context($this->dom);

        if ($a_pc_id == "") {
            $path = "//TableData[@HierId = '" . $a_hier_id . "']";
        } else {
            $path = "//TableData[@PCID = '" . $a_pc_id . "']";
        }
        $res = xpath_eval($xpc, $path);

        if (count($res->nodeset) == 1) {
            if ($a_width != "") {
                $res->nodeset[0]->set_attribute("Width", $a_width);
            } else {
                if ($res->nodeset[0]->has_attribute("Width")) {
                    $res->nodeset[0]->remove_attribute("Width");
                }
            }
        }
    }

    public function setTDSpans(
        array $a_colspans,
        array $a_rowspans
    ): void {
        $y = 0;
        $rows = $this->tab_node->child_nodes();
        foreach ($rows as $row) {
            if ($row->node_name() == "TableRow") {
                $x = 0;
                $cells = $row->child_nodes();
                foreach ($cells as $cell) {
                    if ($cell->node_name() == "TableData") {
                        $ckey = $cell->get_attribute("HierId") . ":" . $cell->get_attribute("PCID");
                        if ((int) $a_colspans[$ckey] > 1) {
                            $cell->set_attribute("ColSpan", (int) $a_colspans[$ckey]);
                        } else {
                            if ($cell->has_attribute("ColSpan")) {
                                $cell->remove_attribute("ColSpan");
                            }
                        }
                        if ((int) $a_rowspans[$ckey] > 1) {
                            $cell->set_attribute("RowSpan", (int) $a_rowspans[$ckey]);
                        } else {
                            if ($cell->has_attribute("RowSpan")) {
                                $cell->remove_attribute("RowSpan");
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
        $rows = $this->tab_node->child_nodes();

        foreach ($rows as $row) {
            if ($row->node_name() == "TableRow") {
                $x = 0;
                $cells = $row->child_nodes();
                foreach ($cells as $cell) {
                    if ($cell->node_name() == "TableData") {
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
        $rows = $this->tab_node->child_nodes();
        foreach ($rows as $row) {
            if ($row->node_name() == "TableRow") {
                $x = 0;
                $cells = $row->child_nodes();
                foreach ($cells as $cell) {
                    if ($cell->node_name() == "TableData") {
                        $cspan = max(1, (int) $cell->get_attribute("ColSpan"));
                        $rspan = max(1, (int) $cell->get_attribute("RowSpan"));

                        // if col or rowspan is to high: reduce it to the max
                        if ($cspan > $max_x - $x + 1) {
                            $cell->set_attribute("ColSpan", $max_x - $x + 1);
                            $cspan = $max_x - $x + 1;
                        }
                        if ($rspan > $max_y - $y + 1) {
                            $cell->set_attribute("RowSpan", $max_y - $y + 1);
                            $rspan = $max_y - $y + 1;
                        }

                        // check hidden status
                        if ($this->checkCellHidden($colspans, $rowspans, $x, $y)) {
                            // hidden: set hidden flag, remove col and rowspan
                            $cell->set_attribute("Hidden", "Y");
                            $cspan = 1;
                            $rspan = 1;
                            if ($cell->has_attribute("ColSpan")) {
                                $cell->remove_attribute("ColSpan");
                            }
                            if ($cell->has_attribute("RowSpan")) {
                                $cell->remove_attribute("RowSpan");
                            }
                            $this->makeEmptyCell($cell);
                        } else {
                            // not hidden: remove hidden flag if existing
                            if ($cell->has_attribute("Hidden")) {
                                $cell->remove_attribute("Hidden");
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


    /**
     * Make cell empty
     */
    public function makeEmptyCell(php4DOMElement $td_node): void
    {
        // delete children of paragraph node
        $children = $td_node->child_nodes();
        for ($i = 0; $i < count($children); $i++) {
            $td_node->remove_child($children[$i]);
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
        $rows = $this->tab_node->child_nodes();
        foreach ($rows as $row) {
            if ($row->node_name() == "TableRow") {
                $cells = $row->child_nodes();
                foreach ($cells as $cell) {
                    if ($cell->node_name() == "TableData") {
                        $classes[$cell->get_attribute("HierId") . ":" . $cell->get_attribute("PCID")]
                            = $cell->get_attribute("Class");
                    }
                }
            }
        }

        return $classes;
    }

    public function getAllCellAlignments(): array
    {
        $classes = array();
        $rows = $this->tab_node->child_nodes();
        foreach ($rows as $row) {
            if ($row->node_name() == "TableRow") {
                $cells = $row->child_nodes();
                foreach ($cells as $cell) {
                    if ($cell->node_name() == "TableData") {
                        $classes[$cell->get_attribute("HierId") . ":" . $cell->get_attribute("PCID")]
                            = $cell->get_attribute("HorizontalAlign");
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
        $rows = $this->tab_node->child_nodes();
        $y = 0;
        $max_x = 0;
        $max_y = 0;
        foreach ($rows as $row) {
            if ($row->node_name() == "TableRow") {
                $x = 0;
                $cells = $row->child_nodes();
                foreach ($cells as $cell) {
                    if ($cell->node_name() == "TableData") {
                        $spans[$cell->get_attribute("HierId") . ":" . $cell->get_attribute("PCID")]
                            = array("x" => $x, "y" => $y, "colspan" => $cell->get_attribute("ColSpan"),
                                "rowspan" => $cell->get_attribute("RowSpan"));
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
        $rows = $this->tab_node->child_nodes();
        foreach ($rows as $row) {
            if ($row->node_name() == "TableRow") {
                $cells = $row->child_nodes();
                foreach ($cells as $cell) {
                    if ($cell->node_name() == "TableData") {
                        $widths[$cell->get_attribute("HierId") . ":" . $cell->get_attribute("PCID")]
                            = $cell->get_attribute("Width");
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
        $xpc = xpath_new_context($this->dom);
        if ($a_pc_id == "") {
            $path = "//TableData[@HierId = '" . $a_hier_id . "']";
        } else {
            $path = "//TableData[@PCID = '" . $a_pc_id . "']";
        }
        $res = xpath_eval($xpc, $path);
        if (count($res->nodeset) == 1) {
            if ($a_class != "") {
                $res->nodeset[0]->set_attribute("Class", $a_class);
            } else {
                if ($res->nodeset[0]->has_attribute("Class")) {
                    $res->nodeset[0]->remove_attribute("Class");
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
        $xpc = xpath_new_context($this->dom);
        if ($a_pc_id == "") {
            $path = "//TableData[@HierId = '" . $a_hier_id . "']";
        } else {
            $path = "//TableData[@PCID = '" . $a_pc_id . "']";
        }
        $res = xpath_eval($xpc, $path);
        if (count($res->nodeset) == 1) {
            if ($a_class != "") {
                $res->nodeset[0]->set_attribute("HorizontalAlign", $a_class);
            } else {
                if ($res->nodeset[0]->has_attribute("HorizontalAlign")) {
                    $res->nodeset[0]->remove_attribute("HorizontalAlign");
                }
            }
        }
    }

    public function getCaption(): string
    {
        $hier_id = $this->getHierId();
        if (!empty($hier_id)) {
            $xpc = xpath_new_context($this->dom);
            $path = "//PageContent[@HierId = '" . $hier_id . "']/Table/Caption";
            $res = xpath_eval($xpc, $path);

            if (count($res->nodeset) == 1) {
                return $res->nodeset[0]->get_content();
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
            $xpc = xpath_new_context($this->dom);
            $path = "//PageContent[@HierId = '" . $hier_id . "']/Table/Caption";
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) == 1) {
                return $res->nodeset[0]->get_attribute("Align");
            }
        }
        return "";
    }

    public function setCaption(string $a_content, string $a_align): void
    {
        if ($a_content != "") {
            ilDOMUtil::setFirstOptionalElement(
                $this->dom,
                $this->tab_node,
                "Caption",
                array("Summary", "TableRow"),
                $a_content,
                array("Align" => $a_align)
            );
        } else {
            ilDOMUtil::deleteAllChildsByName($this->tab_node, array("Caption"));
        }
    }


    public function importTableAttributes(
        php4DOMElement $node
    ): void {
        /*echo "importing table attributes";
        var_dump($tableNode);*/
        if ($node->has_attributes()) {
            foreach ($node->attributes() as $n) {
                switch (strtolower($n->node_name())) {
                    case "border":
                        $this->setBorder($this->extractText($n));
                        break;
                    case "align":
                        $this->setHorizontalAlign(ucfirst(strtolower($this->extractText($n))));
                        break;
                    case "cellspacing":
                        $this->setCellSpacing($this->extractText($n));
                        break;
                    case "cellpadding":
                        $this->setCellPadding($this->extractText($n));
                        break;
                    case "width":
                        $this->setWidth($this->extractText($n));
                        break;
                }
            }
        }
    }


    public function importCellAttributes(
        php4DOMElement $node,
        php4DOMElement $par
    ): void {
        /*echo "importing table attributes";
        var_dump($tableNode);*/
        if ($node->has_attributes()) {
            foreach ($node->attributes() as $n) {
                switch (strtolower($n->node_name())) {
                    case "class":
                        $par->set_attribute("Class", $this->extractText($n));
                        break;
                    case "width":
                        $par->set_attribute("Width", $this->extractText($n));
                        break;
                }
            }
        }
    }


    public function importRow(
        string $lng,
        php4DOMElement $node
    ): void {
        $aRow = $this->addRow();

        if ($node->has_child_nodes()) {
            foreach ($node->child_nodes() as $n) {
                if ($n->node_type() == XML_ELEMENT_NODE &&
                strcasecmp($n->node_name(), "td") == 0) {
                    $this->importCell($lng, $n, $aRow);
                }
            }
        }
    }

    public function importCell(
        string $lng,
        php4DOMElement $cellNode,
        php4DOMElement $aRow
    ): void {
        /*echo "add Cell";
        var_dump($cellNode);*/
        $aCell = $this->addCell($aRow);
        $par = new ilPCParagraph($this->getPage());
        $par->createAtNode($aCell);
        $par->setText($par->input2xml($this->extractText($cellNode)));
        $par->setCharacteristic("TableContent");
        $par->setLanguage($lng);
        $this->importCellAttributes($cellNode, $aCell);
    }

    public function extractText(
        php4DOMElement $node
    ): string {
        $output = "";

        $owner_document = $node->owner_document();
        $children = $node->child_nodes();
        $total_children = count($children);
        for ($i = 0; $i < $total_children; $i++) {
            $cur_child_node = $children[$i];
            $output .= $owner_document->dump_node($cur_child_node);
        }
        return $output;
    }

    /**
     * @return bool|string
     */
    public function importHtml(
        string $lng,
        string $htmlTable
    ) {
        $dummy = ilUtil::stripSlashes($htmlTable, false);
        $dom = domxml_open_mem($dummy, DOMXML_LOAD_PARSING, $error);

        if ($dom) {
            $xpc = xpath_new_context($dom);
            // extract first table object
            $path = "//table[1] | //Table[1]";
            $res = xpath_eval($xpc, $path);

            if (count($res->nodeset) == 0) {
                $error = "Could not find a table root node";
            }

            if (empty($error)) {
                for ($i = 0; $i < count($res->nodeset); $i++) {
                    $node = $res->nodeset[$i];

                    $this->importTableAttributes($node);

                    if ($node->has_child_nodes()) {
                        foreach ($node->child_nodes() as $n) {
                            if ($n->node_type() == XML_ELEMENT_NODE &&
                            strcasecmp($n->node_name(), "tr") == 0) {
                                $this->importRow($lng, $n);
                            }
                        }
                    }
                }
            }
            $dom->free();
        }
        if (is_array($error)) {
            $errmsg = "";
            foreach ($error as $errorline) {    # Loop through all errors
                $errmsg .= "[" . $errorline['line'] . ", " . $errorline['col'] . "]: " . $errorline['errormessage'] . " at Node '" . $errorline['nodename'] . "'<br />";
            }
        } else {
            $errmsg = $error;
        }

        if (empty($errmsg)) {
            return true;
        }

        return $errmsg;
    }

    public function setFirstRowStyle(
        string $a_class
    ): void {
        $childs = $this->tab_node->child_nodes();
        foreach ($childs as $child) {
            if ($child->node_name() == "TableRow") {
                $gchilds = $child->child_nodes();
                foreach ($gchilds as $gchild) {
                    if ($gchild->node_name() == "TableData") {
                        $gchild->set_attribute("Class", $a_class);
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
            $this->tab_node->set_attribute($a_attr, $a_value);
        } else {
            if ($this->tab_node->has_attribute($a_attr)) {
                $this->tab_node->remove_attribute($a_attr);
            }
        }
    }

    public function getTableAttribute(string $a_attr): string
    {
        if (is_object($this->tab_node)) {
            return  $this->tab_node->get_attribute($a_attr);
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

        $rows = $this->tab_node->child_nodes();

        $y = 0;
        foreach ($rows as $row) {
            if ($row->node_name() == "TableRow") {
                $x = 0;
                $cells = $row->child_nodes();
                foreach ($cells as $cell) {
                    if ($cell->node_name() == "TableData") {
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

        return $model;
    }
}
