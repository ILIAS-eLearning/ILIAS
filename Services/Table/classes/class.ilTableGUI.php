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
 * HTML table component
 * @author	Sascha Hofmann <shofmann@databay.de>
 */
class ilTableGUI
{
    protected string $sort_order;
    protected string $link_params;
    protected array $header_params;
    /**
     * @var ilTemplate|ilGlobalTemplateInterface
     */
    protected $tpl;
    protected ilLanguage $lng;

    public string $title = "";      // table title name
    public string $icon = "";		// table title icon
    public string $icon_alt = "";	// table title icon alt text
    public string $help_page = "";				// table help name
    public string $help_icon = "";				// table help icon
    public string $help_icon_alt = "";			// table help icon alt text
    public array $header_names = [];	// titles of header columns
    public array $header_vars = [];		// var names of header columns
    public array $linkbar_vars = [];	// additional variables for linkbar
    public array $data = [];			// table content
    public int $column_count = 0;		// no. of columns
    public array $column_width = [];	// column width of each column
    public int $max_count = 0;				// max. count of database query
    public int $limit = 0;					// max. count of dataset per page
    public bool $max_limit = false;
    public int $offset = 0;				// dataset offset
    public string $order_column = "";			// order column
    public string $order_direction = "";		// order direction
    public string $footer_style = "";			// css format for links
    public string $footer_previous = "";		// value of previous link
    public string $footer_next = "";			// value of next link
    public bool $lang_support = true;	// if a lang object is included
    public bool $global_tpl = false;			// uses global tpl (true) or a local one (false)
    public string $form_name = "";			// the name of the parent form of the table
    public string $select_all_checkbox = "";  // the name (or the first characters if unique) of a checkbox the should be toggled with a select all button
    public array $action_buttons = [];  // action buttons in the table footer

    public string $prefix = "";				// prefix for sort and offset fields if you have two or more tables on a page that you want to sort separately
    public string $base = "";				// base script (deprecated)

    // default settings for enabled/disabled table modules
    public array $enabled = array(	"table" => true,
                            "title" => true,
                            "icon" => true,
                            "help" => false,
                            "content" => true,
                            "action" => false,
                            "header" => true,
                            "footer" => true,
                            "linkbar" => true,
                            "numinfo" => true,
                            "numinfo_header" => false,
                            "sort" => true,
                            "hits" => false,
                            "auto_sort" => true,
                            "select_all" => false
                        );

    // tpl styles (only one so far)
    public array $styles = array(
                            "table" => "fullwidth"
                        );

    /**
     * @param array|null $a_data content data (optional)
     * @param bool       $a_global_tpl content data (optional)
     */
    public function __construct(
        array $a_data = [],
        bool $a_global_tpl = true
    ) {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        $lng = $DIC->language();

        $this->global_tpl = $a_global_tpl;
        $this->header_vars = array();
        $this->header_params = array();
        $this->enabled["form"] = true;
        $this->action_buttons = array();
        if ($this->global_tpl) {
            $this->tpl = $tpl;
        } else {
            $this->tpl = new ilTemplate("tpl.table.html", true, true, "Services/Table");
        }

        $this->lng = $lng;

        if (!$this->lng) {
            $this->lang_support = false;
        }

        $this->setData($a_data);
    }

    public function setTemplate(ilTemplate $a_tpl): void
    {
        $this->tpl = $a_tpl;
    }

    public function getTemplateObject(): ilTemplate
    {
        return $this->tpl;
    }

    /**
     * Set table data
     */
    public function setData(array $a_data): void
    {
        $this->data = $a_data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $a_title table title
     * @param string $a_icon file name of title icon
     * @param string $a_icon_alt alternative text for title icon
     */
    public function setTitle(string $a_title, string $a_icon = "", string $a_icon_alt = ""): void
    {
        $this->title = $a_title;
        $this->icon = $a_icon;
        $this->icon_alt = $a_icon_alt;

        if (!$this->icon) {
            $this->enabled["icon"] = false;

            return;
        }

        if (!$this->icon_alt) {
            $this->icon_alt = $this->icon;
        }
        $this->enabled["icon"] = true;
    }

    public function setHelp(string $a_help_page, string $a_help_icon, string $a_help_icon_alt = ""): void
    {
        $this->help_page = $a_help_page;
        $this->help_icon = $a_help_icon;
        $this->help_icon_alt = $a_help_icon_alt;

        if (!$this->help_icon_alt) {
            $this->help_icon_alt = $this->help_icon;
        }
    }

    public function setHeaderNames(array $a_header_names): void
    {
        $this->header_names = $a_header_names;
        $this->column_count = count($this->header_names);
    }

    public function getColumnCount(): int
    {
        return $this->column_count;
    }

    public function setHeaderVars(array $a_header_vars, array $a_header_params = []): void
    {
        $this->header_vars = $a_header_vars;
        $this->header_params = $a_header_params;
        $this->link_params = "";
        foreach ($a_header_params as $key => $val) {
            $this->link_params .= $key . "=" . $val . "&";
        }
    }

    /**
     * set table column widths
     */
    public function setColumnWidth(array $a_column_width): void
    {
        $this->column_width = $a_column_width;
    }

    public function setOneColumnWidth(string $a_column_width, int $a_column_number): void
    {
        $this->column_width[$a_column_number] = $a_column_width;
    }

    /**
     * set max. count of database query
     * you don't need to set max count if using integrated content rendering feature
     * if max_limit is true, no limit is given -> set limit to max_count
     */
    public function setMaxCount(int $a_max_count): void
    {
        $this->max_count = $a_max_count;

        if ($this->max_limit) {
            $this->limit = $this->max_count;
        }
    }

    /**
     * set max. datasets displayed per page
     */
    public function setLimit(int $a_limit = 0, int $a_default_limit = 0): void
    {
        $this->limit = ($a_limit) ?: $a_default_limit;

        if ($this->limit == 0) {
            $this->max_limit = true;
        }
    }

    public function getLimit(): int
    {
        return $this->limit;
    }


    /**
     * set prefix for sort and offset fields
     * (if you have two or more tables on a page that you want to sort separately)
     */
    public function setPrefix(string $a_prefix): void
    {
        $this->prefix = $a_prefix ?: "";
    }

    /**
     * set dataset offset
     */
    public function setOffset(int $a_offset): void
    {
        $this->offset = ($a_offset) ?: 0;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOrderColumn(
        string $a_order_column = "",
        string $a_default_column = ""
    ): void {
        // set default sort column to first column
        if (empty($a_order_column)) {
            if (!empty($a_default_column)) {
                $oc = array_search($a_default_column, $this->header_vars);
            } else {
                $oc = "";
            }
        } else {
            $oc = array_search($a_order_column, $this->header_vars);
        }

        $this->order_column = $oc ?: "";
    }

    public function getOrderColumn(): string
    {
        return $this->order_column;
    }

    public function setOrderDirection(string $a_order_direction): void
    {
        if (strtolower($a_order_direction) == "desc") {
            $this->order_direction = "desc";
            $this->sort_order = "asc";
        } else {
            $this->order_direction = "asc"; // set default sort order to "ASC"
            $this->sort_order = "desc";
        }
    }

    public function getOrderDirection(): string
    {
        return $this->order_direction;
    }

    public function setFooter(
        string $a_style,
        string $a_previous = "",
        string $a_next = ""
    ): void {
        $this->footer_style = $a_style;
        $this->footer_previous = $a_previous ?: "<<<";
        $this->footer_next = $a_next ?: ">>>";
    }

    /**
     * @deprecated Use setEnable...<Section>() of Table2GUI instead
     */
    public function enable(string $a_module_name): void
    {
        if (!in_array($a_module_name, array_keys($this->enabled))) {
            return;
        }

        $this->enabled[$a_module_name] = true;
    }

    /**
     * @deprecated Use setEnable<Section>() of Table2GUI instead
     */
    public function disable(string $a_module_name): void
    {
        if (!in_array($a_module_name, array_keys($this->enabled))) {
            return;
        }

        $this->enabled[$a_module_name] = false;
    }


    public function sortData(): void
    {
        if ($this->enabled["sort"]) {
            $this->data = ilArrayUtil::sortArray($this->data, $this->order_column, $this->order_direction);
        }
        $this->data = array_slice($this->data, $this->offset, $this->limit);
    }

    public function render(): string
    {
        if ($this->enabled['table']) {
            $this->tpl->setVariable("CSS_TABLE", "table table-striped" /* $this->getStyle("table") */);
        }

        // table title icon
        if ($this->enabled["icon"] && $this->enabled["title"]) {
            $this->tpl->setCurrentBlock("tbl_header_title_icon");
            $this->tpl->setVariable("TBL_TITLE_IMG", ilUtil::getImagePath($this->icon));
            $this->tpl->setVariable("TBL_TITLE_IMG_ALT", $this->icon_alt);
            $this->tpl->parseCurrentBlock();
        }
        // table title help
        if ($this->enabled["help"] && $this->enabled["title"]) {
            $this->tpl->setCurrentBlock("tbl_header_title_help");
            $this->tpl->setVariable("TBL_HELP_IMG", ilUtil::getImagePath($this->help_icon));
            $this->tpl->setVariable("TBL_HELP_LINK", $this->help_page);
            $this->tpl->setVariable("TBL_HELP_IMG_ALT", $this->help_icon_alt);
            $this->tpl->parseCurrentBlock();
        }

        // hits per page selector
        if ($this->enabled["hits"] && $this->enabled["title"]) {
            $this->tpl->setCurrentBlock("tbl_header_hits_page");
            $this->tpl->setVariable("HITS_PER_PAGE", $this->lng->txt("hits_per_page"));
            $this->tpl->parseCurrentBlock();
        }

        // table title
        if ($this->enabled["title"]) {
            $this->tpl->setCurrentBlock("tbl_header_title");
            $this->tpl->setVariable("COLUMN_COUNT", $this->column_count);
            $this->tpl->setVariable("TBL_TITLE", $this->title);
            $this->tpl->parseCurrentBlock();
        }

        // table header
        if ($this->enabled["header"]) {
            $this->renderHeader();
        }

        // table data
        // the table content may be skipped to use an individual template blockfile
        // To do so don't set $this->data and parse your table content by yourself
        // The template block name for the blockfile MUST be 'TBL_CONTENT'

        if ($this->enabled["content"]) {
            if ($this->enabled['auto_sort']) {
                $this->setMaxCount(count($this->data));
                $this->sortData();
            }
            $count = 0;

            foreach ($this->data as $tbl_content_row) {
                foreach ($tbl_content_row as $key => $tbl_content_cell) {
                    if (is_array($tbl_content_cell)) {
                        $this->tpl->setCurrentBlock("tbl_cell_subtitle");
                        $this->tpl->setVariable("TBL_CELL_SUBTITLE", $tbl_content_cell[1]);
                        $this->tpl->parseCurrentBlock();
                        $tbl_content_cell = "<b>" . $tbl_content_cell[0] . "</b>";
                    }

                    $this->tpl->setCurrentBlock("tbl_content_cell");
                    $this->tpl->setVariable("TBL_CONTENT_CELL", $tbl_content_cell);
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("tbl_content_row");
                $this->tpl->setVariable("ROWCOLOR", " ");
                $this->tpl->parseCurrentBlock();

                $count++;
            }
        }
        // select all checkbox
        if ($this->enabled["select_all"]) {
            if ((strlen($this->getFormName())) && (strlen($this->getSelectAllCheckbox()))) {
                $this->tpl->setVariable('SELECT_PREFIX', $this->prefix);
                $this->tpl->setVariable("SELECT_ALL_TXT_SELECT_ALL", $this->lng->txt("select_all"));
                $this->tpl->setVariable("SELECT_ALL_CHECKBOX_NAME", $this->getSelectAllCheckbox());
                $this->tpl->setVariable("SELECT_ALL_FORM_NAME", $this->getFormName());
                if (!($this->enabled["numinfo"] && $this->enabled["footer"])) {
                    $this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
                }
            }
        }

        // table header numinfo
        if ($this->enabled["numinfo_header"]) {
            $start = $this->offset + 1;				// compute num info
            $end = $this->offset + $this->limit;

            if ($end > $this->max_count or $this->limit == 0) {
                $end = $this->max_count;
            }

            if ($this->lang_support) {
                $numinfo = "(" . $this->lng->txt("dataset") . " " . $start . " - " . $end . " " . strtolower($this->lng->txt("of")) . " " . $this->max_count . ")";
            } else {
                $numinfo = "(Dataset " . $start . " - " . $end . " of " . $this->max_count . ")";
            }
            if ($this->max_count > 0) {
                //$numinfo = $this->lng->txt("no_datasets");
                $this->tpl->setCurrentBlock("tbl_header_numinfo");
                $this->tpl->setVariable("NUMINFO_HEADER", $numinfo);
                $this->tpl->setVariable("COLUMN_COUNT_HEADER", $this->getColumnCount());
                $this->tpl->parseCurrentBlock();
            }
        }
        // table footer numinfo
        if ($this->enabled["numinfo"] && $this->enabled["footer"]) {
            $start = $this->offset + 1;				// compute num info
            $end = $this->offset + $this->limit;

            if ($end > $this->max_count or $this->limit == 0) {
                $end = $this->max_count;
            }

            if ($this->lang_support) {
                $numinfo = "(" . $this->lng->txt("dataset") . " " . $start . " - " . $end . " " . strtolower($this->lng->txt("of")) . " " . $this->max_count . ")";
            } else {
                $numinfo = "(Dataset " . $start . " - " . $end . " of " . $this->max_count . ")";
            }
            if ($this->max_count > 0) {
                //$numinfo = $this->lng->txt("no_datasets");
                $this->tpl->setCurrentBlock("tbl_footer_numinfo");
                $this->tpl->setVariable("NUMINFO", $numinfo);
                $this->tpl->parseCurrentBlock();
            }
        }
        // table footer linkbar
        if ($this->enabled["linkbar"] && $this->enabled["footer"] && $this->limit != 0
             && $this->max_count > 0) {
            $params = array(
                            $this->prefix . "sort_by" => $this->header_vars[$this->order_column],
                            $this->prefix . "sort_order" => $this->order_direction
                            );
            $params = array_merge($this->header_params, $params);

            $layout = array(
                            "link" => $this->footer_style,
                            "prev" => $this->footer_previous,
                            "next" => $this->footer_next,
                            );

            $base = ($this->getBase() == "")
                ? basename($_SERVER["PHP_SELF"])
                : $this->getBase();

            $linkbar = $this->linkbar($base, $this->max_count, $this->limit, $this->offset, $params, $layout, $this->prefix);
            $this->tpl->setCurrentBlock("tbl_footer_linkbar");
            $this->tpl->setVariable("LINKBAR", $linkbar);
            $this->tpl->parseCurrentBlock();
        }

        // table footer
        if ($this->enabled["footer"] && $this->max_count > 0) {
            $this->tpl->setCurrentBlock("tbl_footer");
            $this->tpl->setVariable("COLUMN_COUNT", $this->column_count);
            $this->tpl->parseCurrentBlock();
        }

        // action buttons
        if ($this->enabled["action"]) {
            foreach ($this->action_buttons as $button) {
                $this->tpl->setCurrentBlock("tbl_action_btn");
                $this->tpl->setVariable("BTN_NAME", $button["name"]);
                $this->tpl->setVariable("BTN_VALUE", $button["value"]);
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("tbl_action_row");
            $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
            $this->tpl->setVariable("ALT_ARROW", $this->lng->txt("arrow_downright.svg"));
            $this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
            $this->tpl->parseCurrentBlock();
        }

        if ($this->enabled["form"]) {
            $this->tpl->touchBlock("tbl_form_footer");
        }

        if ($this->enabled['table']) {
            $this->tpl->touchBlock("tbl_table_end");
        }

        if (!$this->global_tpl) {
            return $this->tpl->get();
        }
        return "";
    }

    public static function linkbar(
        string $AScript,
        int $AHits,
        int $ALimit,
        int $AOffset,
        array $AParams = array(),
        array $ALayout = array(),
        string $prefix = ''
    ): string {
        $LinkBar = "";
        $params = "";

        $layout_link = "";
        $layout_prev = "&lt;&lt;";
        $layout_next = "&gt;&gt;";

        // layout options
        if ((is_array($ALayout) && (count($ALayout) > 0))) {
            if ($ALayout["link"]) {
                $layout_link = " class=\"" . $ALayout["link"] . "\"";
            }

            if ($ALayout["prev"]) {
                $layout_prev = $ALayout["prev"];
            }

            if ($ALayout["next"]) {
                $layout_next = $ALayout["next"];
            }
        }

        // show links, if hits greater limit
        // or offset > 0 (can be > 0 due to former setting)
        if ($AHits > $ALimit || $AOffset > 0) {
            if (!empty($AParams)) {
                foreach ($AParams as $key => $value) {
                    $params .= $key . "=" . $value . "&";
                }
            }
            // if ($params) $params = substr($params,0,-1);
            if (strpos($AScript, '&')) {
                $link = $AScript . "&" . $params . $prefix . "offset=";
            } else {
                $link = $AScript . "?" . $params . $prefix . "offset=";
            }

            // ?bergehe "zurck"-link, wenn offset 0 ist.
            if ($AOffset >= 1) {
                $prevoffset = $AOffset - $ALimit;
                if ($prevoffset < 0) {
                    $prevoffset = 0;
                }
                $LinkBar .= "<a" . $layout_link . " href=\"" . $link . $prevoffset . "\">" . $layout_prev . "&nbsp;</a>";
            }

            // Ben?tigte Seitenzahl kalkulieren
            $pages = intval($AHits / $ALimit);

            // Wenn ein Rest bleibt, addiere eine Seite
            if (($AHits % $ALimit)) {
                $pages++;
            }

            // Bei Offset = 0 keine Seitenzahlen anzeigen : DEAKTIVIERT
            //			if ($AOffset != 0) {

            // ansonsten zeige Links zu den anderen Seiten an
            for ($i = 1 ;$i <= $pages ; $i++) {
                $newoffset = $ALimit * ($i - 1);

                if ($newoffset == $AOffset) {
                    $LinkBar .= "[" . $i . "] ";
                } else {
                    $LinkBar .= '<a ' . $layout_link . ' href="' .
                        $link . $newoffset . '">[' . $i . ']</a> ';
                }
            }
            //			}

            // Checken, ob letze Seite erreicht ist
            // Wenn nicht, gebe einen "Weiter"-Link aus
            if (!(($AOffset / $ALimit) == ($pages - 1)) && ($pages != 1)) {
                $newoffset = $AOffset + $ALimit;
                $LinkBar .= "<a" . $layout_link . " href=\"" . $link . $newoffset . "\">&nbsp;" . $layout_next . "</a>";
            }

            return $LinkBar;
        }
        return "";
    }

    public function renderHeader(): void
    {
        foreach ($this->header_names as $key => $tbl_header_cell) {
            if (!$this->enabled["sort"]) {
                $this->tpl->setCurrentBlock("tbl_header_no_link");
                if ($this->column_width[$key]) {
                    $this->tpl->setVariable("TBL_COLUMN_WIDTH_NO_LINK", " width=\"" . $this->column_width[$key] . "\"");
                }
                $this->tpl->setVariable("TBL_HEADER_CELL_NO_LINK", $tbl_header_cell);
                $this->tpl->parseCurrentBlock();
                continue;
            }
            if (($key == $this->order_column) && ($this->order_direction != "")) {
                if (strcmp($this->header_vars[$key], "") != 0) {
                    $this->tpl->setCurrentBlock("tbl_order_image");
                    $this->tpl->parseCurrentBlock();
                }
            }

            $this->tpl->setCurrentBlock("tbl_header_cell");
            $this->tpl->setVariable("TBL_HEADER_CELL", $tbl_header_cell);

            // only set width if a value is given for that column
            if ($this->column_width[$key]) {
                $this->tpl->setVariable("TBL_COLUMN_WIDTH", " width=\"" . $this->column_width[$key] . "\"");
            }

            $lng_sort_column = ($this->lang_support) ? $this->lng->txt("sort_by_this_column") : "Sort by this column";
            $this->tpl->setVariable("TBL_ORDER_ALT", $lng_sort_column);

            $order_dir = "asc";

            if ($key == $this->order_column) {
                $order_dir = $this->sort_order;

                $lng_change_sort = ($this->lang_support) ? $this->lng->txt("change_sort_direction") : "Change sort direction";
                $this->tpl->setVariable("TBL_ORDER_ALT", $lng_change_sort);
            }

            $this->setOrderLink($key, $order_dir);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock("tbl_header");
        $this->tpl->parseCurrentBlock();
    }

    public function setOrderLink(string $key, string $order_dir): void
    {
        $this->tpl->setVariable("TBL_ORDER_LINK", basename($_SERVER["PHP_SELF"]) . "?" . $this->link_params . $this->prefix . "sort_by=" . $this->header_vars[$key] . "&" . $this->prefix . "sort_order=" . $order_dir . "&" . $this->prefix . "offset=" . $this->offset);
    }

    public function setStyle(
        string $a_element,
        string $a_style
    ): void {
        $this->styles[$a_element] = $a_style;
    }

    public function getStyle(string $a_element): string
    {
        return $this->styles[$a_element];
    }

    /**
     * @param	string	$a_base	Base script name (deprecated, only use this for workarounds)
     */
    public function setBase(string $a_base): void
    {
        $this->base = $a_base;
    }

    public function getBase(): string
    {
        return $this->base;
    }

    /**
     * get the name of the parent form
     */
    public function getFormName(): string
    {
        return $this->form_name;
    }

    public function setFormName(string $a_name = "cmd"): void
    {
        $this->form_name = $a_name;
    }

    /**
     * get the name of the checkbox that should be toggled with a select all button
     */
    public function getSelectAllCheckbox(): string
    {
        return $this->select_all_checkbox;
    }

    public function setSelectAllCheckbox(string $a_select_all_checkbox): void
    {
        $this->select_all_checkbox = $a_select_all_checkbox;
    }

    public function clearActionButtons(): void
    {
        $this->action_buttons = array();
    }

    public function addActionButton(string $btn_name, string $btn_value): void
    {
        $this->action_buttons[] = array(
            "name" => $btn_name,
            "value" => $btn_value
        );
    }
}
