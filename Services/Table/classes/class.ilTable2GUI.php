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
 * @author	Alex Killing <alex.killing@gmx.de>
 * @author	Sascha Hofmann <shofmann@databay.de>
 *
 * @deprecated 11
 */
class ilTable2GUI extends ilTableGUI
{
    public const FILTER_TEXT = 1;
    public const FILTER_SELECT = 2;
    public const FILTER_DATE = 3;
    public const FILTER_LANGUAGE = 4;
    public const FILTER_NUMBER_RANGE = 5;
    public const FILTER_DATE_RANGE = 6;
    public const FILTER_DURATION_RANGE = 7;
    public const FILTER_DATETIME_RANGE = 8;
    public const FILTER_CHECKBOX = 9;
    public const EXPORT_EXCEL = 1;
    public const EXPORT_CSV = 2;
    public const ACTION_ALL_LIMIT = 1000;
    protected string $requested_tmpl_delete;
    protected string $requested_tmpl_create;
    protected string $requested_nav_par2 = "";
    protected string $requested_nav_par = "";
    protected string $requested_nav_par1 = "";
    protected ?\ILIAS\Table\TableGUIRequest $table_request = null;
    protected array $selected_columns = [];

    protected ilCtrl $ctrl;
    protected ?object $parent_obj = null;
    protected string $parent_cmd = "";
    protected string $close_command = "";
    private string $unique_id = "";
    private string $headerHTML = "";
    protected string $top_anchor = "il_table_top";
    protected array $filters = array();
    protected array $optional_filters = array();
    protected string $filter_cmd = 'applyFilter';
    protected string $reset_cmd = 'resetFilter';
    protected int $filter_cols = 5;
    protected bool $ext_sort = false;
    protected bool $ext_seg = false;
    protected string $context = "";

    protected array $mi_sel_buttons = [];
    protected bool $disable_filter_hiding = false;
    protected bool $top_commands = true;
    protected array $selectable_columns = array();
    protected array $selected_column = array();
    protected bool $show_templates = false;
    protected bool $show_rows_selector = true; // JF, 2014-10-27
    protected bool $rows_selector_off = false;

    protected bool $nav_determined = false;
    protected bool $limit_determined = false;
    protected bool $filters_determined = false;
    protected bool $columns_determined = false;
    protected bool $open_form_tag = true;
    protected bool $close_form_tag = true;
    protected array $export_formats = [];
    protected bool $export_mode = false;
    protected bool $print_mode = false;
    protected bool $enable_command_for_all = false;
    protected bool $restore_filter = false;
    protected array $restore_filter_values = [];
    protected bool $default_filter_visibility = false;
    protected array $sortable_fields = array();
    protected bool $prevent_double_submission = true;
    protected string $row_selector_label = "";
    protected bool $select_all_on_top = false;
    protected array $sel_buttons = [];
    protected string $nav_value = '';
    protected string $noentriestext = '';
    protected string $css_row = '';
    protected bool $display_as_block = false;
    protected string $description = '';
    protected string $id = "";
    protected bool $custom_prev_next = false;
    protected string $reset_cmd_txt = "";
    protected string $defaultorderfield = "";
    protected string $defaultorderdirection = "";
    protected array $column = [];
    protected bool $datatable = false;
    protected bool $num_info = false;
    protected bool $form_multipart = false;
    protected array $row_data = [];
    protected string $order_field = "";
    protected array $selected_filter = [];
    protected string $form_action = "";
    protected string $formname = "";
    protected string $sort_order = "";
    protected array $buttons = [];
    protected array $multi = [];
    protected array $hidden_inputs = [];
    protected array $header_commands = [];
    protected string $row_template = "";
    protected string $row_template_dir = "";
    protected string $filter_cmd_txt = "";
    protected string $custom_prev = "";
    protected string $custom_next = "";
    protected ?array $raw_post_data = null;
    protected \ilGlobalTemplateInterface $main_tpl;

    /**
     * @param object|null $a_parent_obj upper GUI class, which calls ilTable2GUI
     */
    public function __construct(
        ?object $a_parent_obj,
        string $a_parent_cmd = "",
        string $a_template_context = ""
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();

        if (isset($DIC["http"])) {
            $this->table_request = new \ILIAS\Table\TableGUIRequest(
                $DIC->http(),
                $DIC->refinery()
            );
        }
        $this->getRequestedValues();
        parent::__construct([], false);
        $this->unique_id = md5(uniqid('', true));
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
        $this->buttons = array();
        $this->header_commands = array();
        $this->multi = array();
        $this->hidden_inputs = array();
        $this->formname = "table_" . $this->unique_id;
        $this->tpl = new ilTemplate("tpl.table2.html", true, true, "Services/Table");

        $lng->loadLanguageModule('tbl');

        if (!$a_template_context) {
            $a_template_context = $this->getId();
        }
        $this->setContext($a_template_context);

        // activate export mode
        if (isset($this->table_request)) {
            $this->export_mode = $this->table_request->getExportMode($this->prefix);

            // template handling
            if ($this->table_request->getTemplate($this->prefix) != "") {
                $this->restoreTemplate($this->table_request->getTemplate($this->prefix));
            }
        }

        $this->determineLimit();
        $this->setIsDataTable(true);
        $this->setEnableNumInfo(true);
        $this->determineSelectedColumns();

        $this->raw_post_data = [];
        if (isset($DIC["http"])) {
            $this->raw_post_data = $DIC->http()->request()->getParsedBody();
        }
    }

    protected function getRequestedValues(): void
    {
        if (is_null($this->table_request)) {
            return;
        }
        $this->requested_nav_par = $this->table_request->getNavPar($this->getNavParameter());
        $this->requested_nav_par1 = $this->table_request->getNavPar($this->getNavParameter(), 1);
        $this->requested_nav_par2 = $this->table_request->getNavPar($this->getNavParameter(), 2);
        $this->requested_tmpl_create = $this->table_request->getTemplCreate();
        $this->requested_tmpl_delete = $this->table_request->getTemplDelete();
    }

    public function setOpenFormTag(bool $a_val): void
    {
        $this->open_form_tag = $a_val;
    }

    public function getOpenFormTag(): bool
    {
        return $this->open_form_tag;
    }

    public function setCloseFormTag(bool $a_val): void
    {
        $this->close_form_tag = $a_val;
    }

    public function getCloseFormTag(): bool
    {
        return $this->close_form_tag;
    }

    public function determineLimit(): void
    {
        global $DIC;

        $ilUser = null;
        if (isset($DIC["ilUser"])) {
            $ilUser = $DIC["ilUser"];
        }

        if ($this->limit_determined) {
            return;
        }

        $limit = 0;
        if (isset($this->table_request) && !is_null($this->table_request->getRows($this->prefix))) {
            $this->storeProperty("rows", $this->table_request->getRows($this->prefix));
            $limit = $this->table_request->getRows($this->prefix) ?? 0;
            $this->resetOffset();
        }

        if ($limit == 0) {
            $rows = (int) $this->loadProperty("rows");
            if ($rows > 0) {
                $limit = $rows;
            } else {
                if (is_object($ilUser)) {
                    $limit = (int) $ilUser->getPref("hits_per_page");
                } else {
                    $limit = 40;
                }
            }
        }

        $this->setLimit($limit);
        $this->limit_determined = true;
    }

    /**
     * Get selectable columns
     * @return array key: column id, val: true/false -> default on/off
     */
    public function getSelectableColumns(): array
    {
        return [];
    }

    public function determineSelectedColumns(): void
    {
        if ($this->columns_determined) {
            return;
        }

        $old_sel = $this->loadProperty("selfields");
        $sel_fields = [];
        $stored = false;
        if ($old_sel != "") {
            $sel_fields = unserialize($old_sel);
            $stored = true;
        }
        if (!is_array($sel_fields)) {
            $stored = false;
            $sel_fields = array();
        }

        $this->selected_columns = array();
        $set = false;

        $fsh = false;
        $fs = [];
        if (isset($this->table_request)) {
            $fs = $this->table_request->getFS($this->getId());
            $fsh = $this->table_request->getFSH($this->getId());
        }

        foreach ($this->getSelectableColumns() as $k => $c) {
            $this->selected_column[$k] = false;

            $new_column = (!isset($sel_fields[$k]));

            if ($fsh) {
                $set = true;
                if (in_array($k, $fs)) {
                    $this->selected_column[$k] = true;
                }
            } elseif ($stored && !$new_column) {	// take stored values
                $this->selected_column[$k] = $sel_fields[$k];
            } else {	// take default values
                if ($new_column) {
                    $set = true;
                }
                if (isset($c["default"]) && $c["default"]) {
                    $this->selected_column[$k] = true;
                }
            }

            // Optional filters
            $ff = [];
            if (isset($this->table_request)) {
                $ff = $this->table_request->getFF($this->getId());
            }
            if (count($ff) > 0) {
                $set = true;
                if (in_array($k, $ff)) {
                    $this->selected_column[$k] = true;
                }
            }
        }

        if ($old_sel != serialize($this->selected_column) && $set) {
            $this->storeProperty("selfields", serialize($this->selected_column));
        }

        $this->columns_determined = true;
    }

    public function isColumnSelected(string $col): bool
    {
        return $this->selected_column[$col] ?? false;
    }

    public function getSelectedColumns(): array
    {
        $scol = array();
        foreach ($this->selected_column as $k => $v) {
            if ($v) {
                $scol[$k] = $k;
            }
        }
        return $scol;
    }

    public function executeCommand(): bool
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class) {
            case 'ilformpropertydispatchgui':
                $form_prop_dispatch = new ilFormPropertyDispatchGUI();
                $this->initFilter();
                /** @var ilFormPropertyGUI $item */
                $item = $this->getFilterItemByPostVar(
                    $this->table_request->getPostVar()
                );
                $form_prop_dispatch->setItem($item);
                return (bool) $ilCtrl->forwardCommand($form_prop_dispatch);
        }
        return false;
    }

    public function resetOffset(bool $a_in_determination = false): void
    {
        if (!$this->nav_determined && !$a_in_determination) {
            $this->determineOffsetAndOrder();
        }
        $this->nav_value = $this->getOrderField() . ":" . $this->getOrderDirection() . ":0";
        $this->requested_nav_par = $this->requested_nav_par1 = $this->nav_value;
        $this->setOffset(0);
    }

    public function initFilter(): void
    {
    }

    public function getParentObject(): ?object
    {
        return $this->parent_obj;
    }

    public function getParentCmd(): string
    {
        return $this->parent_cmd;
    }

    public function setTopAnchor(string $a_val): void
    {
        $this->top_anchor = $a_val;
    }

    public function getTopAnchor(): string
    {
        return $this->top_anchor;
    }

    public function setNoEntriesText(string $a_text): void
    {
        $this->noentriestext = $a_text;
    }

    public function getNoEntriesText(): string
    {
        return $this->noentriestext;
    }

    public function setIsDataTable(bool $a_val): void
    {
        $this->datatable = $a_val;
    }

    public function getIsDataTable(): bool
    {
        return $this->datatable;
    }

    public function setEnableTitle(bool $a_enabletitle): void
    {
        $this->enabled["title"] = $a_enabletitle;
    }

    public function getEnableTitle(): bool
    {
        return $this->enabled["title"];
    }

    public function setEnableHeader(bool $a_enableheader): void
    {
        $this->enabled["header"] = $a_enableheader;
    }

    public function getEnableHeader(): bool
    {
        return $this->enabled["header"];
    }

    public function setEnableNumInfo(bool $a_val): void
    {
        $this->num_info = $a_val;
    }

    public function getEnableNumInfo(): bool
    {
        return $this->num_info;
    }

    final public function setTitle(
        string $a_title,
        string $a_icon = "",
        string $a_icon_alt = ""
    ): void {
        parent::setTitle($a_title, $a_icon, $a_icon_alt);
    }

    public function setDescription(string $a_val): void
    {
        $this->description = $a_val;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setOrderField(string $a_order_field): void
    {
        $this->order_field = $a_order_field;
    }

    public function getOrderField(): string
    {
        return $this->order_field;
    }

    final public function setData(array $a_data): void
    {
        $this->row_data = $a_data;
    }

    final public function getData(): array
    {
        return $this->row_data;
    }

    final public function dataExists(): bool
    {
        return count($this->row_data) > 0;
    }

    final public function setPrefix(string $a_prefix): void
    {
        $this->prefix = $a_prefix;
        $this->getRequestedValues();
    }

    final public function getPrefix(): string
    {
        return $this->prefix;
    }

    final public function addFilterItem(
        ilTableFilterItem $a_input_item,
        bool $a_optional = false
    ): void {
        $a_input_item->setParentTable($this);
        if (!$a_optional) {
            $this->filters[] = $a_input_item;
        } else {
            $this->optional_filters[] = $a_input_item;
        }

        // restore filter values (from stored view)
        if ($this->restore_filter) {
            if (array_key_exists($a_input_item->getFieldId(), $this->restore_filter_values)) {
                $this->setFilterValue($a_input_item, $this->restore_filter_values[$a_input_item->getFieldId()]);
            } else {
                $this->setFilterValue($a_input_item, null); // #14949
            }
        }
    }

    /**
     * Add filter by standard type
     * @throws Exception
     */
    public function addFilterItemByMetaType(
        string $id,
        int $type = self::FILTER_TEXT,
        bool $a_optional = false,
        string $caption = ""
    ): ?ilTableFilterItem {
        global $DIC;

        $lng = $DIC->language();	// constructor may not be called here, if initFilter is being called in subclasses before parent::__construct

        if (!$caption) {
            $caption = $lng->txt($id);
        }

        switch ($type) {
            case self::FILTER_CHECKBOX:
                $item = new ilCheckboxInputGUI($caption, $id);
                break;

            case self::FILTER_SELECT:
                $item = new ilSelectInputGUI($caption, $id);
                break;

            case self::FILTER_DATE:
                $item = new ilDateTimeInputGUI($caption, $id);
                break;

            case self::FILTER_TEXT:
                $item = new ilTextInputGUI($caption, $id);
                $item->setMaxLength(64);
                $item->setSize(20);
                // $item->setSubmitFormOnEnter(true);
                break;

            case self::FILTER_LANGUAGE:
                $lng->loadLanguageModule("meta");
                $item = new ilSelectInputGUI($caption, $id);
                $options = array("" => $lng->txt("trac_all"));
                foreach ($lng->getInstalledLanguages() as $lang_key) {
                    $options[$lang_key] = $lng->txt("meta_l_" . $lang_key);
                }
                $item->setOptions($options);
                break;

            case self::FILTER_NUMBER_RANGE:
                $item = new ilCombinationInputGUI($caption, $id);
                $combi_item = new ilNumberInputGUI("", $id . "_from");
                $item->addCombinationItem("from", $combi_item, $lng->txt("from"));
                $combi_item = new ilNumberInputGUI("", $id . "_to");
                $item->addCombinationItem("to", $combi_item, $lng->txt("to"));
                $item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
                //$item->setMaxLength(7);
                //$item->setSize(20);
                break;

            case self::FILTER_DATE_RANGE:
                $item = new ilCombinationInputGUI($caption, $id);
                $combi_item = new ilDateTimeInputGUI("", $id . "_from");
                $item->addCombinationItem("from", $combi_item, $lng->txt("from"));
                $combi_item = new ilDateTimeInputGUI("", $id . "_to");
                $item->addCombinationItem("to", $combi_item, $lng->txt("to"));
                $item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
                break;

            case self::FILTER_DATETIME_RANGE:
                $item = new ilCombinationInputGUI($caption, $id);
                $combi_item = new ilDateTimeInputGUI("", $id . "_from");
                $combi_item->setShowTime(true);
                $item->addCombinationItem("from", $combi_item, $lng->txt("from"));
                $combi_item = new ilDateTimeInputGUI("", $id . "_to");
                $combi_item->setShowTime(true);
                $item->addCombinationItem("to", $combi_item, $lng->txt("to"));
                $item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
                break;

            case self::FILTER_DURATION_RANGE:
                $lng->loadLanguageModule("form");
                $item = new ilCombinationInputGUI($caption, $id);
                $combi_item = new ilDurationInputGUI("", $id . "_from");
                $combi_item->setShowMonths(false);
                $combi_item->setShowDays(true);
                $combi_item->setShowSeconds(true);
                $item->addCombinationItem("from", $combi_item, $lng->txt("from"));
                $combi_item = new ilDurationInputGUI("", $id . "_to");
                $combi_item->setShowMonths(false);
                $combi_item->setShowDays(true);
                $combi_item->setShowSeconds(true);
                $item->addCombinationItem("to", $combi_item, $lng->txt("to"));
                $item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
                break;

            default:
                return null;
        }

        $this->addFilterItem($item, $a_optional);
        $item->readFromSession();
        return $item;
    }

    final public function getFilterItems(bool $a_optionals = false): array
    {
        if (!$a_optionals) {
            return $this->filters;
        }
        return $this->optional_filters;
    }

    final public function getFilterItemByPostVar(string $a_post_var): ?ilTableFilterItem
    {
        foreach ($this->getFilterItems() as $item) {
            if ($item->getPostVar() == $a_post_var) {
                return $item;
            }
        }
        foreach ($this->getFilterItems(true) as $item) {
            if ($item->getPostVar() == $a_post_var) {
                return $item;
            }
        }
        return null;
    }

    public function setFilterCols(int $a_val): void
    {
        $this->filter_cols = $a_val;
    }

    public function getFilterCols(): int
    {
        return $this->filter_cols;
    }

    public function setDisableFilterHiding(bool $a_val = true): void
    {
        $this->disable_filter_hiding = $a_val;
    }

    public function getDisableFilterHiding(): bool
    {
        return $this->disable_filter_hiding;
    }

    /**
     * Is given filter selected?
     */
    public function isFilterSelected(string $a_col): bool
    {
        return (bool) $this->selected_filter[$a_col];
    }

    public function getSelectedFilters(): array
    {
        $sfil = array();
        foreach ($this->selected_filter as $k => $v) {
            if ($v) {
                $sfil[$k] = $k;
            }
        }
        return $sfil;
    }

    public function determineSelectedFilters(): void
    {
        if ($this->filters_determined) {
            return;
        }

        $old_sel = $this->loadProperty("selfilters");
        $stored = false;
        $sel_filters = null;
        if ($old_sel != "") {
            $sel_filters =
                unserialize($old_sel);
            $stored = true;
        }
        if (!is_array($sel_filters)) {
            $stored = false;
            $sel_filters = array();
        }

        $this->selected_filter = array();
        $set = false;
        foreach ($this->getFilterItems(true) as $item) {
            $k = $item->getPostVar();

            $this->selected_filter[$k] = false;

            if ($this->table_request->getFSF($this->getId())) {
                $set = true;
                if (in_array($k, $this->table_request->getFF($this->getId()))) {
                    $this->selected_filter[$k] = true;
                } else {
                    $item->setValue(null);
                    $item->writeToSession();
                }
            } elseif ($stored) {	// take stored values
                $this->selected_filter[$k] = $sel_filters[$k] ?? "";
            }
        }

        if ($old_sel != serialize($this->selected_filter) && $set) {
            $this->storeProperty("selfilters", serialize($this->selected_filter));
        }

        $this->filters_determined = true;
    }

    public function setCustomPreviousNext(
        string $a_prev_link,
        string $a_next_link
    ): void {
        $this->custom_prev_next = true;
        $this->custom_prev = $a_prev_link;
        $this->custom_next = $a_next_link;
    }

    final public function setFormAction(
        string $a_form_action,
        bool $a_multipart = false
    ): void {
        $this->form_action = $a_form_action;
        $this->form_multipart = $a_multipart;
    }

    final public function getFormAction(): string
    {
        return $this->form_action;
    }

    public function setFormName(string $a_name = ""): void
    {
        $this->formname = $a_name;
    }

    public function getFormName(): string
    {
        return $this->formname;
    }

    public function setId(string $a_val): void
    {
        $this->id = $a_val;
        if ($this->getPrefix() == "") {
            $this->setPrefix($a_val);
        }
        if (strlen($this->id) > 30) {
            throw new ilException("Table ID to long (max. 30 char): " . $this->id);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setDisplayAsBlock(bool $a_val): void
    {
        $this->display_as_block = $a_val;
    }

    public function getDisplayAsBlock(): bool
    {
        return $this->display_as_block;
    }

    public function setSelectAllCheckbox(
        string $a_select_all_checkbox,
        bool $a_select_all_on_top = false
    ): void {
        $this->select_all_checkbox = $a_select_all_checkbox;
        $this->select_all_on_top = $a_select_all_on_top;
    }

    public function setExternalSorting(bool $a_val): void
    {
        $this->ext_sort = $a_val;
    }

    public function getExternalSorting(): bool
    {
        return $this->ext_sort;
    }

    public function setFilterCommand(
        string $a_val,
        string $a_caption = ""
    ): void {
        $this->filter_cmd = $a_val;
        $this->filter_cmd_txt = $a_caption;
    }

    public function getFilterCommand(): string
    {
        return $this->filter_cmd;
    }

    public function setResetCommand(
        string $a_val,
        string $a_caption = ""
    ): void {
        $this->reset_cmd = $a_val;
        $this->reset_cmd_txt = $a_caption;
    }

    public function getResetCommand(): string
    {
        return $this->reset_cmd;
    }

    public function setExternalSegmentation(bool $a_val): void
    {
        $this->ext_seg = $a_val;
    }

    public function getExternalSegmentation(): bool
    {
        return $this->ext_seg;
    }

    /**
     * Set row template.
     * @param	string $a_template     Template file name.
     * @param	string $a_template_dir Service/Module directory.
     */
    final public function setRowTemplate(string $a_template, string $a_template_dir = ""): void
    {
        $this->row_template = $a_template;
        $this->row_template_dir = $a_template_dir;
    }

    public function setDefaultOrderField(string $a_defaultorderfield): void
    {
        $this->defaultorderfield = $a_defaultorderfield;
    }

    public function getDefaultOrderField(): string
    {
        return $this->defaultorderfield;
    }


    public function setDefaultOrderDirection(string $a_defaultorderdirection): void
    {
        $this->defaultorderdirection = $a_defaultorderdirection;
    }

    public function getDefaultOrderDirection(): string
    {
        return $this->defaultorderdirection;
    }

    public function setDefaultFilterVisiblity(bool $a_status): void
    {
        $this->default_filter_visibility = $a_status;
    }

    public function getDefaultFilterVisibility(): bool
    {
        return $this->default_filter_visibility;
    }

    public function clearCommandButtons(): void
    {
        $this->buttons = array();
    }

    public function addCommandButton(
        string $a_cmd,
        string $a_text,
        string $a_onclick = '',
        string $a_id = "",
        string $a_class = ""
    ): void {
        $this->buttons[] = array("cmd" => $a_cmd, "text" => $a_text, 'onclick' => $a_onclick,
            "id" => $a_id, "class" => $a_class);
    }

    public function addCommandButtonInstance(ilButtonBase $a_button): void
    {
        $this->buttons[] = $a_button;
    }

    /**
     * @param string $a_sel_var selection input variable name
     * @param array  $a_options selection options ("value" => text")
     * @param string $a_cmd command
     * @param string $a_text button text
     * @param string $a_default_selection
     * @return void
     */
    public function addMultiItemSelectionButton(
        string $a_sel_var,
        array $a_options,
        string $a_cmd,
        string $a_text,
        string $a_default_selection = ''
    ): void {
        $this->mi_sel_buttons[] = array("sel_var" => $a_sel_var, "options" => $a_options, "selected" => $a_default_selection, "cmd" => $a_cmd, "text" => $a_text);
        $this->addHiddenInput("cmd_sv[" . $a_cmd . "]", $a_sel_var);
    }

    public function setCloseCommand(string $a_link): void
    {
        $this->close_command = $a_link;
    }

    public function addMultiCommand(string $a_cmd, string $a_text): void
    {
        $this->multi[] = array("cmd" => $a_cmd, "text" => $a_text);
    }

    public function addHiddenInput(string $a_name, string $a_value): void
    {
        $this->hidden_inputs[] = array("name" => $a_name, "value" => $a_value);
    }

    public function addHeaderCommand(
        string $a_href,
        string $a_text,
        string $a_target = "",
        string $a_img = ""
    ): void {
        $this->header_commands[] = array("href" => $a_href, "text" => $a_text,
            "target" => $a_target, "img" => $a_img);
    }

    public function setTopCommands(bool $a_val): void
    {
        $this->top_commands = $a_val;
    }

    public function getTopCommands(): bool
    {
        return $this->top_commands;
    }

    final public function addColumn(
        string $a_text,
        string $a_sort_field = "",
        string $a_width = "",
        bool $a_is_checkbox_action_column = false,
        string $a_class = "",
        string $a_tooltip = "",
        bool $a_tooltip_with_html = false
    ): void {
        $this->column[] = array(
            "text" => $a_text,
            "sort_field" => $a_sort_field,
            "width" => $a_width,
            "is_checkbox_action_column" => $a_is_checkbox_action_column,
            "class" => $a_class,
            "tooltip" => $a_tooltip,
            "tooltip_html" => $a_tooltip_with_html
            );
        if ($a_sort_field != "") {
            $this->sortable_fields[] = $a_sort_field;
        }
        $this->column_count = count($this->column);
    }


    final public function getNavParameter(): string
    {
        return $this->prefix . "_table_nav";
    }

    public function setOrderLink(string $key, string $order_dir): void
    {
        global $DIC;

        $ilUser = $DIC->user();

        $ilCtrl = $this->ctrl;

        $hash = "";

        $old = $this->requested_nav_par ?? '';

        // set order link
        $ilCtrl->setParameter(
            $this->parent_obj,
            $this->getNavParameter(),
            $key . ":" . $order_dir . ":" . $this->offset
        );
        $this->tpl->setVariable(
            "TBL_ORDER_LINK",
            $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd) . $hash
        );

        // set old value of nav variable
        $ilCtrl->setParameter(
            $this->parent_obj,
            $this->getNavParameter(),
            $old
        );
    }

    public function fillHeader(): void
    {
        $lng = $this->lng;

        $allcolumnswithwidth = true;
        foreach ($this->column as $idx => $column) {
            if (!strlen($column["width"])) {
                $allcolumnswithwidth = false;
            } elseif ($column["width"] == "1") {
                // IE does not like 1 but seems to work with 1%
                $this->column[$idx]["width"] = "1%";
            }
        }
        if ($allcolumnswithwidth) {
            foreach ($this->column as $column) {
                $this->tpl->setCurrentBlock("tbl_colgroup_column");
                $width = (is_numeric($column["width"]))
                    ? $column["width"] . "px"
                    : $column["width"];
                $this->tpl->setVariable("COLGROUP_COLUMN_WIDTH", " style=\"width:" . $width . "\"");
                $this->tpl->parseCurrentBlock();
            }
        }
        $ccnt = 0;
        foreach ($this->column as $column) {
            $ccnt++;
            //tooltip
            if ($column["tooltip"] != "") {
                ilTooltipGUI::addTooltip(
                    "thc_" . $this->getId() . "_" . $ccnt,
                    $column["tooltip"],
                    "",
                    "bottom center",
                    "top center",
                    !$column["tooltip_html"]
                );
            }

            if ($column['is_checkbox_action_column'] && $this->select_all_on_top) {
                $this->tpl->setCurrentBlock('tbl_header_top_select_all');
                $this->tpl->setVariable("HEAD_SELECT_ALL_TXT_SELECT_ALL", $lng->txt("select_all"));
                $this->tpl->setVariable("HEAD_SELECT_ALL_CHECKBOX_NAME", $this->getSelectAllCheckbox());
                $this->tpl->setVariable("HEAD_SELECT_ALL_FORM_NAME", $this->getFormName());
                $this->tpl->setVariable("HEAD_CHECKBOXNAME", "chb_select_all_" . $this->unique_id . '_top');
                $this->tpl->parseCurrentBlock();
                continue;
            }
            if (
                !$this->enabled["sort"] ||
                (($column["sort_field"] == "") &&
                    !($column["is_checkbox_action_column"] && $this->select_all_on_top))
            ) {
                $this->tpl->setCurrentBlock("tbl_header_no_link");
                if ($column["width"] != "") {
                    $width = (is_numeric($column["width"]))
                        ? $column["width"] . "px"
                        : $column["width"];
                    $this->tpl->setVariable("TBL_COLUMN_WIDTH_NO_LINK", " style=\"width:" . $width . "\"");
                }
                if ($column["class"] != "") {
                    $this->tpl->setVariable("TBL_COLUMN_CLASS_NO_LINK", " class=\"" . $column["class"] . "\"");
                }
                if (!$column["is_checkbox_action_column"]) {
                    $this->tpl->setVariable(
                        "TBL_HEADER_CELL_NO_LINK",
                        $column["text"]
                    );
                } else {
                    $this->tpl->setVariable(
                        "TBL_HEADER_CELL_NO_LINK",
                        ilUtil::img(ilUtil::getImagePath("spacer.png"), $lng->txt("action"))
                    );
                }
                $this->tpl->setVariable("HEAD_CELL_NL_ID", "thc_" . $this->getId() . "_" . $ccnt);
                if ($column["class"] != "") {
                    $this->tpl->setVariable("TBL_HEADER_CLASS", " " . $column["class"]);
                }
                $this->tpl->parseCurrentBlock();
                $this->tpl->touchBlock("tbl_header_th");
                continue;
            }
            if (($column["sort_field"] == $this->order_field) && ($this->order_direction != "")) {
                $this->tpl->setCurrentBlock("tbl_order_image");
                if ($this->order_direction === "asc") {
                    $this->tpl->setVariable("ORDER_CLASS", "glyphicon glyphicon-arrow-up");
                } else {
                    $this->tpl->setVariable("ORDER_CLASS", "glyphicon glyphicon-arrow-down");
                }
                $this->tpl->setVariable("IMG_ORDER_ALT", $this->lng->txt("change_sort_direction"));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("tbl_header_cell");
            $this->tpl->setVariable("TBL_HEADER_CELL", $column["text"]);
            $this->tpl->setVariable("HEAD_CELL_ID", "thc_" . $this->getId() . "_" . $ccnt);

            // only set width if a value is given for that column
            if ($column["width"] != "") {
                $width = (is_numeric($column["width"]))
                    ? $column["width"] . "px"
                    : $column["width"];
                $this->tpl->setVariable("TBL_COLUMN_WIDTH", " style=\"width:" . $width . "\"");
            }
            if ($column["class"] != "") {
                $this->tpl->setVariable("TBL_COLUMN_CLASS", " class=\"" . $column["class"] . "\"");
            }

            $lng_sort_column = $this->lng->txt("sort_by_this_column");
            $this->tpl->setVariable("TBL_ORDER_ALT", $lng_sort_column);

            $order_dir = "asc";

            if ($column["sort_field"] == $this->order_field) {
                $order_dir = $this->sort_order;

                $lng_change_sort = $this->lng->txt("change_sort_direction");
                $this->tpl->setVariable("TBL_ORDER_ALT", $lng_change_sort);
            }

            if ($column["class"] != "") {
                $this->tpl->setVariable("TBL_HEADER_CLASS", " " . $column["class"]);
            }
            $this->setOrderLink($column["sort_field"], $order_dir);
            $this->tpl->parseCurrentBlock();
            $this->tpl->touchBlock("tbl_header_th");
        }

        $this->tpl->setCurrentBlock("tbl_header");
        $this->tpl->parseCurrentBlock();
    }

    /**
     * Anything that must be done before HTML is generated
     */
    protected function prepareOutput(): void
    {
    }

    public function determineOffsetAndOrder(bool $a_omit_offset = false): void
    {
        global $DIC;

        $ilUser = null;
        if (isset($DIC["ilUser"])) {
            $ilUser = $DIC["ilUser"];
        }

        if ($this->nav_determined) {
            return;
        }

        if ($this->requested_nav_par1 != "") {
            if ($this->requested_nav_par1 != ($this->requested_nav_par ?? "")) {
                $this->nav_value = $this->requested_nav_par1;
            } elseif (
                $this->requested_nav_par2 != "" &&
                $this->requested_nav_par2 != $this->requested_nav_par
            ) {
                $this->nav_value = $this->requested_nav_par2;
            }
        } elseif ($this->requested_nav_par != "") {
            $this->nav_value = $this->requested_nav_par;
        }

        if ($this->nav_value == "" && $this->getId() != "" && $ilUser->getId() != ANONYMOUS_USER_ID) {
            $order = $this->loadProperty("order");
            if (in_array($order, $this->sortable_fields)) {
                $direction = $this->loadProperty("direction");
            } else {
                $direction = $this->getDefaultOrderDirection();
            }
            // get order and direction from db
            $this->nav_value =
                $order . ":" .
                $direction . ":" .
                $this->loadProperty("offset");
        }
        $nav = explode(":", $this->nav_value);

        // $nav[0] is order by
        $req_order_field = $nav[0] ?? "";
        $req_order_dir = $nav[1] ?? "";
        $req_offset = (int) ($nav[2] ?? 0);
        $this->setOrderField(($req_order_field != "") ? $req_order_field : $this->getDefaultOrderField());
        $this->setOrderDirection(($req_order_dir != "") ? $req_order_dir : $this->getDefaultOrderDirection());

        if (!$a_omit_offset) {
            // #8904: offset must be discarded when no limit is given
            if (!$this->getExternalSegmentation() && $this->limit_determined && $this->limit == 9999) {
                $this->resetOffset(true);
            } elseif (!$this->getExternalSegmentation() && $req_offset >= $this->max_count) {
                $this->resetOffset(true);
            } else {
                $this->setOffset($req_offset);
            }
        }

        if (!$a_omit_offset) {
            $this->nav_determined = true;
        }
    }

    public function storeNavParameter(): void
    {
        if ($this->getOrderField() != "") {
            $this->storeProperty("order", $this->getOrderField());
        }
        if ($this->getOrderDirection() != "") {
            $this->storeProperty("direction", $this->getOrderDirection());
        }
        if ($this->getOffset() > 0) {
            $this->storeProperty("offset", (string) $this->getOffset());
        }
    }


    /**
     * Get HTML
     */
    public function getHTML(): string
    {
        global $DIC;

        $ilUser = null;
        if (isset($DIC["ilUser"])) {
            $ilUser = $DIC["ilUser"];
        }

        $lng = $this->lng;
        $ilCtrl = $this->ctrl;


        if ($this->getExportMode()) {
            $this->exportData($this->getExportMode(), true);
        }

        $this->prepareOutput();

        if (is_object($ilCtrl) && is_object($this->getParentObject()) && $this->getId() == "") {
            $ilCtrl->saveParameter($this->getParentObject(), $this->getNavParameter());
        }

        if (!$this->getPrintMode()) {
            // set form action
            if ($this->form_action != "" && $this->getOpenFormTag()) {
                $hash = "";

                if ($this->form_multipart) {
                    $this->tpl->touchBlock("form_multipart_bl");
                }

                if ($this->getPreventDoubleSubmission()) {
                    $this->tpl->touchBlock("pdfs");
                }

                $this->tpl->setCurrentBlock("tbl_form_header");
                $this->tpl->setVariable("FORMACTION", $this->getFormAction() . $hash);
                $this->tpl->setVariable("FORMNAME", $this->getFormName());
                $this->tpl->parseCurrentBlock();
            }

            if ($this->form_action != "" && $this->getCloseFormTag()) {
                $this->tpl->touchBlock("tbl_form_footer");
            }
        }

        if (!$this->enabled['content']) {
            return $this->render();
        }

        if (!$this->getExternalSegmentation()) {
            $this->setMaxCount(count($this->row_data));
        }

        $this->determineOffsetAndOrder();

        $this->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));

        $data = $this->getData();
        if ($this->dataExists()) {
            // sort
            if (!$this->getExternalSorting() && $this->enabled["sort"]) {
                $data = ilArrayUtil::sortArray(
                    $data,
                    $this->getOrderField(),
                    $this->getOrderDirection(),
                    $this->numericOrdering($this->getOrderField())
                );
            }

            // slice
            if (!$this->getExternalSegmentation()) {
                $data = array_slice($data, $this->getOffset(), $this->getLimit());
            }
        }

        // fill rows
        if ($this->dataExists()) {
            if ($this->getPrintMode()) {
                ilDatePresentation::setUseRelativeDates(false);
            }

            $this->tpl->addBlockFile(
                "TBL_CONTENT",
                "tbl_content",
                $this->row_template,
                $this->row_template_dir
            );

            foreach ($data as $set) {
                $this->tpl->setCurrentBlock("tbl_content");
                $this->css_row = ($this->css_row !== "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";
                $this->tpl->setVariable("CSS_ROW", $this->css_row);

                $this->fillRow($set);
                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->parseCurrentBlock();
            }
        } else {
            // add standard no items text (please tell me, if it messes something up, alex, 29.8.2008)
            $no_items_text = (trim($this->getNoEntriesText()) != '')
                ? $this->getNoEntriesText()
                : $lng->txt("no_items");

            $this->css_row = ($this->css_row !== "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";

            $this->tpl->setCurrentBlock("tbl_no_entries");
            $this->tpl->setVariable('TBL_NO_ENTRY_CSS_ROW', $this->css_row);
            $this->tpl->setVariable('TBL_NO_ENTRY_COLUMN_COUNT', $this->column_count);
            $this->tpl->setVariable('TBL_NO_ENTRY_TEXT', trim($no_items_text));
            $this->tpl->parseCurrentBlock();
        }


        if (!$this->getPrintMode()) {
            $this->fillFooter();

            $this->fillHiddenRow();

            $this->fillActionRow();

            $this->storeNavParameter();
        }

        return $this->render();
    }

    /**
     * Should this field be sorted numeric?
     */
    public function numericOrdering(string $a_field): bool
    {
        return false;
    }

    public function render(): string
    {
        $lng = $this->lng;

        $this->tpl->setVariable("CSS_TABLE", $this->getStyle("table"));
        if ($this->getId() != "") {
            $this->tpl->setVariable("ID", 'id="' . $this->getId() . '"');
        }

        // description
        if ($this->getDescription() != "") {
            $this->tpl->setCurrentBlock("tbl_header_description");
            $this->tpl->setVariable("TBL_DESCRIPTION", $this->getDescription());
            $this->tpl->parseCurrentBlock();
        }

        if (!$this->getPrintMode()) {
            $this->renderFilter();
        }

        if ($this->getDisplayAsBlock()) {
            $this->tpl->touchBlock("outer_start_1");
            $this->tpl->touchBlock("outer_end_1");
        } else {
            $this->tpl->touchBlock("outer_start_2");
            $this->tpl->touchBlock("outer_end_2");
        }

        // table title and icon
        if ($this->enabled["title"] && ($this->title != ""
            || $this->icon != "" || count($this->header_commands) > 0 ||
            $this->headerHTML != "" || $this->close_command != "")) {
            if ($this->enabled["icon"]) {
                $this->tpl->setCurrentBlock("tbl_header_title_icon");
                $this->tpl->setVariable("TBL_TITLE_IMG", ilUtil::getImagePath($this->icon));
                $this->tpl->setVariable("TBL_TITLE_IMG_ALT", $this->icon_alt);
                $this->tpl->parseCurrentBlock();
            }

            if (!$this->getPrintMode()) {
                foreach ($this->header_commands as $command) {
                    if ($command["img"] != "") {
                        $this->tpl->setCurrentBlock("tbl_header_img_link");
                        if ($command["target"] != "") {
                            $this->tpl->setVariable(
                                "TARGET_IMG_LINK",
                                'target="' . $command["target"] . '"'
                            );
                        }
                        $this->tpl->setVariable("ALT_IMG_LINK", $command["text"]);
                        $this->tpl->setVariable("HREF_IMG_LINK", $command["href"]);
                        $this->tpl->setVariable(
                            "SRC_IMG_LINK",
                            $command["img"]
                        );
                    } else {
                        $this->tpl->setCurrentBlock("head_cmd");
                        $this->tpl->setVariable("TXT_HEAD_CMD", $command["text"]);
                        $this->tpl->setVariable("HREF_HEAD_CMD", $command["href"]);
                    }
                    $this->tpl->parseCurrentBlock();
                }
            }

            if (isset($this->headerHTML)) {
                $this->tpl->setCurrentBlock("tbl_header_html");
                $this->tpl->setVariable("HEADER_HTML", $this->headerHTML);
                $this->tpl->parseCurrentBlock();
            }

            // close command
            if ($this->close_command != "") {
                $this->tpl->setCurrentBlock("tbl_header_img_link");
                $this->tpl->setVariable("ALT_IMG_LINK", $lng->txt("close"));
                $this->tpl->setVariable("HREF_IMG_LINK", $this->close_command);
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("tbl_header_title");
            $this->tpl->setVariable("TBL_TITLE", $this->title);
            $this->tpl->setVariable("TOP_ANCHOR", $this->getTopAnchor());
            if ($this->getDisplayAsBlock()) {
                $this->tpl->setVariable("BLK_CLASS", "Block");
            }
            $this->tpl->parseCurrentBlock();
        }

        // table header
        if ($this->enabled["header"]) {
            $this->fillHeader();
        }

        $this->tpl->touchBlock("tbl_table_end");

        return $this->tpl->get();
    }

    /**
     * Render Filter section
     */
    private function renderFilter(): void
    {
        global $DIC;

        $lng = $this->lng;
        $main_tpl = $DIC["tpl"];

        $filter = $this->getFilterItems();
        $opt_filter = $this->getFilterItems(true);

        $main_tpl->addJavascript("./Services/Table/js/ServiceTable.js");

        if (count($filter) == 0 && count($opt_filter) == 0) {
            return;
        }

        ilYuiUtil::initConnection();

        $ccnt = 0;

        // render standard filter
        if (count($filter) > 0) {
            foreach ($filter as $item) {
                if ($ccnt >= $this->getFilterCols()) {
                    $this->tpl->setCurrentBlock("filter_row");
                    $this->tpl->parseCurrentBlock();
                    $ccnt = 0;
                }
                $this->tpl->setCurrentBlock("filter_item");
                $this->tpl->setVariable(
                    "OPTION_NAME",
                    $item->getTitle()
                );
                $this->tpl->setVariable(
                    "F_INPUT_ID",
                    $item->getTableFilterLabelFor()
                );
                $this->tpl->setVariable(
                    "INPUT_HTML",
                    $item->getTableFilterHTML()
                );
                $this->tpl->parseCurrentBlock();
                $ccnt++;
            }
        }

        // render optional filter
        if (count($opt_filter) > 0) {
            $this->determineSelectedFilters();

            foreach ($opt_filter as $item) {
                if ($this->isFilterSelected($item->getPostVar())) {
                    if ($ccnt >= $this->getFilterCols()) {
                        $this->tpl->setCurrentBlock("filter_row");
                        $this->tpl->parseCurrentBlock();
                        $ccnt = 0;
                    }
                    $this->tpl->setCurrentBlock("filter_item");
                    $this->tpl->setVariable(
                        "OPTION_NAME",
                        $item->getTitle()
                    );
                    $this->tpl->setVariable(
                        "F_INPUT_ID",
                        $item->getFieldId()
                    );
                    $this->tpl->setVariable(
                        "INPUT_HTML",
                        $item->getTableFilterHTML()
                    );
                    $this->tpl->parseCurrentBlock();
                    $ccnt++;
                }
            }

            // filter selection
            $items = array();
            foreach ($opt_filter as $item) {
                $k = $item->getPostVar();
                $items[$k] = array("txt" => $item->getTitle(),
                    "selected" => $this->isFilterSelected($k));
            }

            $cb_over = new ilCheckboxListOverlayGUI("tbl_filters_" . $this->getId());
            $cb_over->setLinkTitle($lng->txt("optional_filters"));
            $cb_over->setItems($items);

            $cb_over->setFormCmd($this->getParentCmd());
            $cb_over->setFieldVar("tblff" . $this->getId());
            $cb_over->setHiddenVar("tblfsf" . $this->getId());

            $cb_over->setSelectionHeaderClass("ilTableMenuItem");
            $this->tpl->setCurrentBlock("filter_select");

            // apply should be the first submit because of enter/return, inserting hidden submit
            $this->tpl->setVariable("HIDDEN_CMD_APPLY", $this->filter_cmd);

            $this->tpl->setVariable("FILTER_SELECTOR", $cb_over->getHTML(false));
            $this->tpl->parseCurrentBlock();
        }

        // if any filter
        if ($ccnt > 0 || count($opt_filter) > 0) {
            $this->tpl->setVariable("TXT_FILTER", $lng->txt("filter"));

            if ($ccnt > 0) {
                if ($ccnt < $this->getFilterCols()) {
                    for ($i = $ccnt; $i <= $this->getFilterCols(); $i++) {
                        $this->tpl->touchBlock("filter_empty_cell");
                    }
                }
                $this->tpl->setCurrentBlock("filter_row");
                $this->tpl->parseCurrentBlock();

                $this->tpl->setCurrentBlock("filter_buttons");
                $this->tpl->setVariable("CMD_APPLY", $this->filter_cmd);
                $this->tpl->setVariable("TXT_APPLY", $this->filter_cmd_txt
                    ?: $lng->txt("apply_filter"));
                $this->tpl->setVariable("CMD_RESET", $this->reset_cmd);
                $this->tpl->setVariable("TXT_RESET", $this->reset_cmd_txt
                    ?: $lng->txt("reset_filter"));
            } elseif (count($opt_filter) > 0) {
                $this->tpl->setCurrentBlock("optional_filter_hint");
                $this->tpl->setVariable('TXT_OPT_HINT', $lng->txt('optional_filter_hint'));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("filter_section");
            $this->tpl->setVariable("FIL_ID", $this->getId());
            $this->tpl->parseCurrentBlock();

            // (keep) filter hidden?
            if (!$this->isFilterVisible()) {
                if (!$this->getDisableFilterHiding()) {
                    $id = $this->getId();
                    $this->main_tpl->addOnLoadCode("
                        ilTableHideFilter['atfil_$id'] = true;
                        ilTableHideFilter['tfil_$id'] = true;
                        ilTableHideFilter['dtfil_$id'] = true;
                    ");
                }
            }
        }
    }

    /**
     * Check if filter is visible: manually shown (session, db) or default value set
     */
    protected function isFilterVisible(): bool
    {
        $prop = $this->loadProperty('filter');
        if ($prop === '0' || $prop === '1') {
            return (bool) $prop;
        }
        return $this->getDefaultFilterVisibility();
    }

    /**
     * Check if filter element is based on adv md
     */
    protected function isAdvMDFilter(
        ilAdvancedMDRecordGUI $a_gui,
        ilTableFilterItem $a_element
    ): bool {
        foreach ($a_gui->getFilterElements(false) as $item) {
            if ($item === $a_element) {
                return true;
            }
        }
        return false;
    }

    public function writeFilterToSession(): void
    {
        $advmd_record_gui = null;
        if (method_exists($this, "getAdvMDRecordGUI")) {
            $advmd_record_gui = $this->getAdvMDRecordGUI();
        }

        foreach ($this->getFilterItems() as $item) {
            if ($advmd_record_gui &&
                $this->isAdvMDFilter($advmd_record_gui, $item)) {
                continue;
            }

            if ($item->checkInput()) {
                $item->setValueByArray($this->raw_post_data);
                $item->writeToSession();
            }
        }
        foreach ($this->getFilterItems(true) as $item) {
            if ($advmd_record_gui &&
                $this->isAdvMDFilter($advmd_record_gui, $item)) {
                continue;
            }

            if ($item->checkInput()) {
                $item->setValueByArray($this->raw_post_data);
                $item->writeToSession();
            }
        }

        if ($advmd_record_gui) {
            $advmd_record_gui->importFilter();
        }

        // #13209
        $this->requested_tmpl_create = "";
        $this->requested_tmpl_delete = "";
    }

    public function resetFilter(): void
    {
        $filter = $this->getFilterItems();
        $opt_filter = $this->getFilterItems(true);

        foreach ($filter as $item) {
            if ($item->checkInput()) {
                // see #26490
                $item->setValueByArray([]);
                $item->clearFromSession();
            }
        }
        foreach ($opt_filter as $item) {
            if ($item->checkInput()) {
                // see #26490
                $item->setValueByArray([]);
                $item->clearFromSession();
            }
        }

        // #13209
        $this->requested_tmpl_create = "";
        $this->requested_tmpl_delete = "";
    }

    /**
     * Standard Version of Fill Row. Most likely to
     * be overwritten by derived class.
     * @param array $a_set data array
     */
    protected function fillRow(array $a_set): void
    {
        foreach ($a_set as $key => $value) {
            $this->tpl->setVariable("VAL_" . strtoupper($key), $value);
        }
    }

    public function fillFooter(): void
    {
        global $DIC;

        $ilUser = null;
        if (isset($DIC["ilUser"])) {
            $ilUser = $DIC["ilUser"];
        }

        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $footer = false;
        $numinfo = '';
        $linkbar = '';
        $column_selector = '';

        // select all checkbox
        if ((strlen($this->getFormName())) && (strlen($this->getSelectAllCheckbox())) && $this->dataExists()) {
            $this->tpl->setCurrentBlock("select_all_checkbox");
            $this->tpl->setVariable("SELECT_ALL_TXT_SELECT_ALL", $lng->txt("select_all"));
            $this->tpl->setVariable("SELECT_ALL_CHECKBOX_NAME", $this->getSelectAllCheckbox());
            $this->tpl->setVariable("SELECT_ALL_FORM_NAME", $this->getFormName());
            $this->tpl->setVariable("CHECKBOXNAME", "chb_select_all_" . $this->unique_id);
            $this->tpl->parseCurrentBlock();
        }

        // table footer numinfo
        if ($this->enabled["numinfo"] && $this->enabled["footer"]) {
            $start = $this->offset + 1;				// compute num info
            if (!$this->dataExists()) {
                $start = 0;
            }
            $end = $this->offset + $this->limit;

            if ($end > $this->max_count or $this->limit == 0) {
                $end = $this->max_count;
            }

            if ($this->max_count > 0) {
                if ($this->lang_support) {
                    $numinfo = "(" . $start . " - " . $end . " " . strtolower($this->lng->txt("of")) . " " . $this->max_count . ")";
                } else {
                    $numinfo = "(" . $start . " - " . $end . " of " . $this->max_count . ")";
                }
            }
            if ($this->max_count > 0) {
                if ($this->getEnableNumInfo()) {
                    $this->tpl->setCurrentBlock("tbl_footer_numinfo");
                    $this->tpl->setVariable("NUMINFO", $numinfo);
                    $this->tpl->parseCurrentBlock();
                }
            }
            $footer = true;
        }

        // table footer linkbar
        if ($this->enabled["linkbar"] && $this->enabled["footer"] && $this->limit != 0
             && $this->max_count > 0) {
            $linkbar = $this->getLinkbar("1");
            $this->tpl->setCurrentBlock("tbl_footer_linkbar");
            $this->tpl->setVariable("LINKBAR", $linkbar);
            $this->tpl->parseCurrentBlock();
            $linkbar = true;
            $footer = true;
        }

        // column selector
        if (is_array($this->getSelectableColumns()) && count($this->getSelectableColumns()) > 0) {
            $items = array();
            foreach ($this->getSelectableColumns() as $k => $c) {
                $items[$k] = array("txt" => $c["txt"],
                    "selected" => $this->isColumnSelected($k));
            }
            $cb_over = new ilCheckboxListOverlayGUI("tbl_" . $this->getId());
            $cb_over->setLinkTitle($lng->txt("columns"));
            $cb_over->setItems($items);
            //$cb_over->setUrl("./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
            //		$this->getId()."&cmd=saveSelectedFields&user_id=".$ilUser->getId());
            $cb_over->setFormCmd($this->getParentCmd());
            $cb_over->setFieldVar("tblfs" . $this->getId());
            $cb_over->setHiddenVar("tblfsh" . $this->getId());
            $cb_over->setSelectionHeaderClass("ilTableMenuItem");
            $column_selector = $cb_over->getHTML();
            $footer = true;
        }

        if ($this->getShowTemplates() && is_object($ilUser)) {
            // template handling
            if ($this->requested_tmpl_create != "") {
                if ($this->saveTemplate($this->requested_tmpl_create)) {
                    $this->main_tpl->setOnScreenMessage('success', $lng->txt("tbl_template_created"));
                }
            } elseif ($this->requested_tmpl_delete != "") {
                if ($this->deleteTemplate($this->requested_tmpl_delete)) {
                    $this->main_tpl->setOnScreenMessage('success', $lng->txt("tbl_template_deleted"));
                }
            }

            $create_id = "template_create_overlay_" . $this->getId();
            $delete_id = "template_delete_overlay_" . $this->getId();
            $list_id = "template_stg_" . $this->getId();

            $storage = new ilTableTemplatesStorage();
            $templates = $storage->getNames($this->getContext(), $ilUser->getId());

            // form to delete template
            if (count($templates) > 0) {
                $overlay = new ilOverlayGUI($delete_id);
                $overlay->setTrigger($list_id . "_delete");
                $overlay->setAnchor("ilAdvSelListAnchorElement_" . $list_id);
                $overlay->setAutoHide(false);
                $overlay->add();

                $lng->loadLanguageModule("form");
                $this->tpl->setCurrentBlock("template_editor_delete_item");
                $this->tpl->setVariable("TEMPLATE_DELETE_OPTION_VALUE", "");
                $this->tpl->setVariable("TEMPLATE_DELETE_OPTION", "- " . $lng->txt("form_please_select") . " -");
                $this->tpl->parseCurrentBlock();
                foreach ($templates as $name) {
                    $this->tpl->setVariable("TEMPLATE_DELETE_OPTION_VALUE", $name);
                    $this->tpl->setVariable("TEMPLATE_DELETE_OPTION", $name);
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("template_editor_delete");
                $this->tpl->setVariable("TEMPLATE_DELETE_ID", $delete_id);
                $this->tpl->setVariable("TXT_TEMPLATE_DELETE", $lng->txt("tbl_template_delete"));
                $this->tpl->setVariable("TXT_TEMPLATE_DELETE_SUBMIT", $lng->txt("delete"));
                $this->tpl->setVariable("TEMPLATE_DELETE_CMD", $this->parent_cmd);
                $this->tpl->parseCurrentBlock();
            }


            // form to save new template
            $overlay = new ilOverlayGUI($create_id);
            $overlay->setTrigger($list_id . "_create");
            $overlay->setAnchor("ilAdvSelListAnchorElement_" . $list_id);
            $overlay->setAutoHide(false);
            $overlay->add();

            $this->tpl->setCurrentBlock("template_editor");
            $this->tpl->setVariable("TEMPLATE_CREATE_ID", $create_id);
            $this->tpl->setVariable("TXT_TEMPLATE_CREATE", $lng->txt("tbl_template_create"));
            $this->tpl->setVariable("TXT_TEMPLATE_CREATE_SUBMIT", $lng->txt("save"));
            $this->tpl->setVariable("TEMPLATE_CREATE_CMD", $this->parent_cmd);
            $this->tpl->parseCurrentBlock();

            // load saved template
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($list_id);
            $alist->addItem($lng->txt("tbl_template_create"), "create", "#");
            if (count($templates) > 0) {
                $alist->addItem($lng->txt("tbl_template_delete"), "delete", "#");
                foreach ($templates as $name) {
                    $ilCtrl->setParameter($this->parent_obj, $this->prefix . "_tpl", urlencode($name));
                    $alist->addItem($name, $name, $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
                    $ilCtrl->setParameter($this->parent_obj, $this->prefix . "_tpl", "");
                }
            }
            $alist->setListTitle($lng->txt("tbl_templates"));
            $alist->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK_BUTTON);
            $this->tpl->setVariable("TEMPLATE_SELECTOR", "&nbsp;" . $alist->getHTML());
        }

        if ($footer) {
            $this->tpl->setCurrentBlock("tbl_footer");
            $this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
            if ($this->getDisplayAsBlock()) {
                $this->tpl->setVariable("BLK_CLASS", "Block");
            }
            $this->tpl->parseCurrentBlock();

            // top navigation, if number info or linkbar given
            if ($numinfo != "" || $linkbar != "" || $column_selector != "" ||
                count($this->filters) > 0 || count($this->optional_filters) > 0) {
                if (is_object($ilUser) && (count($this->filters) || count($this->optional_filters))) {
                    $this->tpl->setCurrentBlock("filter_activation");
                    $this->tpl->setVariable("TXT_ACTIVATE_FILTER", $lng->txt("show_filter"));
                    $this->tpl->setVariable("FILA_ID", $this->getId());
                    if ($this->getId() != "") {
                        $this->tpl->setVariable("SAVE_URLA", "./ilias.php?baseClass=ilTablePropertiesStorageGUI&table_id=" .
                            $this->getId() . "&cmd=showFilter&user_id=" . $ilUser->getId());
                    }
                    $this->tpl->parseCurrentBlock();


                    if (!$this->getDisableFilterHiding()) {
                        $this->tpl->setCurrentBlock("filter_deactivation");
                        $this->tpl->setVariable("TXT_HIDE", $lng->txt("hide_filter"));
                        if ($this->getId() != "") {
                            $this->tpl->setVariable("SAVE_URL", "./ilias.php?baseClass=ilTablePropertiesStorageGUI&table_id=" .
                                $this->getId() . "&cmd=hideFilter&user_id=" . $ilUser->getId());
                            $this->tpl->setVariable("FILD_ID", $this->getId());
                        }
                        $this->tpl->parseCurrentBlock();
                    }
                }

                if ($numinfo != "" && $this->getEnableNumInfo()) {
                    $this->tpl->setCurrentBlock("top_numinfo");
                    $this->tpl->setVariable("NUMINFO", $numinfo);
                    $this->tpl->parseCurrentBlock();
                }
                if ($linkbar != "" && !$this->getDisplayAsBlock()) {
                    $linkbar = $this->getLinkbar("2");
                    $this->tpl->setCurrentBlock("top_linkbar");
                    $this->tpl->setVariable("LINKBAR", $linkbar);
                    $this->tpl->parseCurrentBlock();
                }

                // column selector
                $this->tpl->setVariable("COLUMN_SELECTOR", $column_selector);

                // row selector
                if ($this->getShowRowsSelector() &&
                    is_object($ilUser) &&
                    $this->getId() &&
                    !$this->rows_selector_off) { // JF, 2014-10-27
                    $alist = new ilAdvancedSelectionListGUI();
                    $alist->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK_BUTTON);
                    $alist->setId("sellst_rows_" . $this->getId());
                    $hpp = ($ilUser->getPref("hits_per_page") != 9999)
                        ? $ilUser->getPref("hits_per_page")
                        : $lng->txt("no_limit");

                    $options = array(0 => $lng->txt("default") . " (" . $hpp . ")",5 => 5, 10 => 10, 15 => 15, 20 => 20,
                                     30 => 30, 40 => 40, 50 => 50,
                                     100 => 100, 200 => 200, 400 => 400, 800 => 800);
                    foreach ($options as $k => $v) {
                        $ilCtrl->setParameter($this->parent_obj, $this->prefix . "_trows", $k);
                        $alist->addItem($v, $k, $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
                        $ilCtrl->setParameter($this->parent_obj, $this->prefix . "_trows", "");
                    }
                    $alist->setListTitle($this->getRowSelectorLabel() ?: $lng->txt("rows"));
                    $this->tpl->setVariable("ROW_SELECTOR", $alist->getHTML());
                }

                // export
                if (count($this->export_formats) > 0 && $this->dataExists()) {
                    $alist = new ilAdvancedSelectionListGUI();
                    $alist->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK_BUTTON);
                    $alist->setId("sellst_xpt");
                    foreach ($this->export_formats as $format => $caption_lng_id) {
                        $ilCtrl->setParameter($this->parent_obj, $this->prefix . "_xpt", $format);
                        $url = $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd);
                        $ilCtrl->setParameter($this->parent_obj, $this->prefix . "_xpt", "");
                        $alist->addItem($lng->txt($caption_lng_id), $format, $url);
                    }
                    $alist->setListTitle($lng->txt("export"));
                    $this->tpl->setVariable("EXPORT_SELECTOR", "&nbsp;" . $alist->getHTML());
                }

                $this->tpl->setCurrentBlock("top_navigation");
                $this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
                if ($this->getDisplayAsBlock()) {
                    $this->tpl->setVariable("BLK_CLASS", "Block");
                }
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    public function getLinkbar(string $a_num): ?string
    {
        global $DIC;

        $ilUser = $DIC->user();

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $hash = "";

        $link = $ilCtrl->getLinkTargetByClass(get_class($this->parent_obj), $this->parent_cmd) .
            "&" . $this->getNavParameter() . "=" .
            $this->getOrderField() . ":" . $this->getOrderDirection() . ":";

        $LinkBar = "";
        $layout_prev = $lng->txt("previous");
        $layout_next = $lng->txt("next");

        // if more entries then entries per page -> show link bar
        if ($this->max_count > $this->getLimit() || $this->custom_prev_next) {
            $sep = "<span>&nbsp;&nbsp;|&nbsp;&nbsp;</span>";

            // calculate number of pages
            $pages = intval($this->max_count / $this->getLimit());

            // add a page if a rest remains
            if (($this->max_count % $this->getLimit())) {
                $pages++;
            }

            // links to other pages
            $offset_arr = array();
            for ($i = 1 ;$i <= $pages ; $i++) {
                $newoffset = $this->getLimit() * ($i - 1);

                $nav_value = $this->getOrderField() . ":" . $this->getOrderDirection() . ":" . $newoffset;
                $offset_arr[$nav_value] = $i;
            }

            $sep = "<span>&nbsp;&nbsp;&nbsp;&nbsp;</span>";

            // previous link
            if ($this->custom_prev_next && $this->custom_prev != "") {
                $LinkBar .= "<a href=\"" . $this->custom_prev . $hash . "\">" . $layout_prev . "</a>";
            } elseif ($this->getOffset() >= 1 && !$this->custom_prev_next) {
                $prevoffset = $this->getOffset() - $this->getLimit();
                $LinkBar .= "<a href=\"" . $link . $prevoffset . $hash . "\">" . $layout_prev . "</a>";
            } else {
                $LinkBar .= '<span class="ilTableFootLight">' . $layout_prev . "</span>";
            }

            // current value
            if ($a_num == "1") {
                $LinkBar .= '<input type="hidden" name="' . $this->getNavParameter() .
                    '" value="' . $this->getOrderField() . ":" . $this->getOrderDirection() . ":" . $this->getOffset() . '" />';
            }

            $sep = "<span>&nbsp;&nbsp;|&nbsp;&nbsp;</span>";

            // show next link (if not last page)
            $LinkBar .= $sep;
            if ($this->custom_prev_next && $this->custom_next != "") {
                $LinkBar .= "<a href=\"" . $this->custom_next . $hash . "\">" . $layout_next . "</a>";
            } elseif (!(($this->getOffset() / $this->getLimit()) == ($pages - 1)) && ($pages != 1) &&
                !$this->custom_prev_next) {
                $newoffset = $this->getOffset() + $this->getLimit();
                $LinkBar .= "<a href=\"" . $link . $newoffset . $hash . "\">" . $layout_next . "</a>";
            } else {
                $LinkBar .= '<span class="ilTableFootLight">' . $layout_next . "</span>";
            }

            $sep = "<span>&nbsp;&nbsp;&nbsp;&nbsp;</span>";

            if (count($offset_arr) && !$this->getDisplayAsBlock() && !$this->custom_prev_next) {
                $LinkBar .= $sep;

                $LinkBar .=
                    '<label for="tab_page_sel_' . $a_num . '">' . $lng->txt("page") . '</label> ' .
                    ilLegacyFormElementsUtil::formSelect(
                        $this->nav_value,
                        $this->getNavParameter() . $a_num,
                        $offset_arr,
                        false,
                        true,
                        0,
                        "small",
                        array("id" => "tab_page_sel_" . $a_num,
                        "onchange" => "ilTablePageSelection(this, 'cmd[" . $this->parent_cmd . "]')")
                    );
            }

            return $LinkBar;
        } else {
            return null;
        }
    }

    public function fillHiddenRow(): void
    {
        $hidden_row = false;
        if (count($this->hidden_inputs)) {
            foreach ($this->hidden_inputs as $hidden_input) {
                $this->tpl->setCurrentBlock("tbl_hidden_field");
                $this->tpl->setVariable("FIELD_NAME", $hidden_input["name"]);
                $this->tpl->setVariable("FIELD_VALUE", $hidden_input["value"]);
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("tbl_hidden_row");
            $this->tpl->parseCurrentBlock();
        }
    }

    public function fillActionRow(): void
    {
        $lng = $this->lng;

        // action row
        $action_row = false;
        $arrow = false;
        $txt = "";
        $cmd = "";

        // add selection buttons
        if (count($this->sel_buttons) > 0) {
            foreach ($this->sel_buttons as $button) {
                $this->tpl->setCurrentBlock("sel_button");
                $this->tpl->setVariable(
                    "SBUTTON_SELECT",
                    ilLegacyFormElementsUtil::formSelect(
                        $button["selected"],
                        $button["sel_var"],
                        $button["options"],
                        false,
                        true
                    )
                );
                $this->tpl->setVariable("SBTN_NAME", $button["cmd"]);
                $this->tpl->setVariable("SBTN_VALUE", $button["text"]);
                $this->tpl->parseCurrentBlock();

                if ($this->getTopCommands()) {
                    $this->tpl->setCurrentBlock("sel_top_button");
                    $this->tpl->setVariable(
                        "SBUTTON_SELECT",
                        ilLegacyFormElementsUtil::formSelect(
                            $button["selected"],
                            $button["sel_var"],
                            $button["options"],
                            false,
                            true
                        )
                    );
                    $this->tpl->setVariable("SBTN_NAME", $button["cmd"]);
                    $this->tpl->setVariable("SBTN_VALUE", $button["text"]);
                    $this->tpl->parseCurrentBlock();
                }
            }
            $buttons = true;
            $action_row = true;
        }
        $this->sel_buttons[] = array("options" => [], "cmd" => '', "text" => '');

        // add buttons
        if (count($this->buttons) > 0) {
            foreach ($this->buttons as $button) {
                if (!is_array($button)) {
                    if ($button instanceof ilButtonBase) {
                        $this->tpl->setVariable('BUTTON_OBJ', $button->render());

                        // this will remove id - should be unique
                        $button = clone $button;

                        $this->tpl->setVariable('BUTTON_TOP_OBJ', $button->render());
                    }
                    continue;
                }

                if (strlen($button['onclick'])) {
                    $this->tpl->setCurrentBlock('cmdonclick');
                    $this->tpl->setVariable('CMD_ONCLICK', $button['onclick']);
                    $this->tpl->parseCurrentBlock();
                }
                $this->tpl->setCurrentBlock("plain_button");
                if ($button["id"] != "") {
                    $this->tpl->setVariable("PBID", ' id="' . $button["id"] . '" ');
                }
                if ($button["class"] != "") {
                    $this->tpl->setVariable("PBBT_CLASS", ' ' . $button["class"]);
                }
                $this->tpl->setVariable("PBTN_NAME", $button["cmd"]);
                $this->tpl->setVariable("PBTN_VALUE", $button["text"]);
                $this->tpl->parseCurrentBlock();

                if ($this->getTopCommands()) {
                    if (strlen($button['onclick'])) {
                        $this->tpl->setCurrentBlock('top_cmdonclick');
                        $this->tpl->setVariable('CMD_ONCLICK', $button['onclick']);
                        $this->tpl->parseCurrentBlock();
                    }
                    $this->tpl->setCurrentBlock("plain_top_button");
                    $this->tpl->setVariable("PBTN_NAME", $button["cmd"]);
                    $this->tpl->setVariable("PBTN_VALUE", $button["text"]);
                    if ($button["class"] != "") {
                        $this->tpl->setVariable("PBBT_CLASS", ' ' . $button["class"]);
                    }
                    $this->tpl->parseCurrentBlock();
                }
            }

            $buttons = true;
            $action_row = true;
        }

        // multi selection
        if (count($this->mi_sel_buttons)) {
            foreach ($this->mi_sel_buttons as $button) {
                $this->tpl->setCurrentBlock("mi_sel_button");
                $this->tpl->setVariable(
                    "MI_BUTTON_SELECT",
                    ilLegacyFormElementsUtil::formSelect(
                        $button["selected"],
                        $button["sel_var"],
                        $button["options"],
                        false,
                        true
                    )
                );
                $this->tpl->setVariable("MI_BTN_NAME", $button["cmd"]);
                $this->tpl->setVariable("MI_BTN_VALUE", $button["text"]);
                $this->tpl->parseCurrentBlock();

                if ($this->getTopCommands()) {
                    $this->tpl->setCurrentBlock("mi_top_sel_button");
                    $this->tpl->setVariable(
                        "MI_BUTTON_SELECT",
                        ilLegacyFormElementsUtil::formSelect(
                            $button["selected"],
                            $button["sel_var"] . "_2",
                            $button["options"],
                            false,
                            true
                        )
                    );
                    $this->tpl->setVariable("MI_BTN_NAME", $button["cmd"]);
                    $this->tpl->setVariable("MI_BTN_VALUE", $button["text"]);
                    $this->tpl->parseCurrentBlock();
                }
            }
            $arrow = true;
            $action_row = true;
        }


        if (count($this->multi) > 1 && $this->dataExists()) {
            if ($this->enable_command_for_all && $this->max_count <= self::getAllCommandLimit()) {
                $this->tpl->setCurrentBlock("tbl_cmd_select_all");
                $this->tpl->setVariable("TXT_SELECT_CMD_ALL", $lng->txt("all_objects"));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("tbl_cmd_select");
            $sel = array();
            foreach ($this->multi as $mc) {
                $sel[$mc["cmd"]] = $mc["text"];
            }
            $this->tpl->setVariable(
                "SELECT_CMDS",
                ilLegacyFormElementsUtil::formSelect("", "selected_cmd", $sel, false, true)
            );
            $this->tpl->setVariable("TXT_EXECUTE", $lng->txt("execute"));
            $this->tpl->parseCurrentBlock();
            $arrow = true;
            $action_row = true;

            if ($this->getTopCommands()) {
                if ($this->enable_command_for_all && $this->max_count <= self::getAllCommandLimit()) {
                    $this->tpl->setCurrentBlock("tbl_top_cmd_select_all");
                    $this->tpl->setVariable("TXT_SELECT_CMD_ALL", $lng->txt("all_objects"));
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("tbl_top_cmd_select");
                $sel = array();
                foreach ($this->multi as $mc) {
                    $sel[$mc["cmd"]] = $mc["text"];
                }
                $this->tpl->setVariable(
                    "SELECT_CMDS",
                    ilLegacyFormElementsUtil::formSelect("", "selected_cmd2", $sel, false, true)
                );
                $this->tpl->setVariable("TXT_EXECUTE", $lng->txt("execute"));
                $this->tpl->parseCurrentBlock();
            }
        } elseif (count($this->multi) == 1 && $this->dataExists()) {
            $this->tpl->setCurrentBlock("tbl_single_cmd");
            foreach ($this->multi as $mc) {
                $cmd = $mc['cmd'];
                $txt = $mc['text'];
            }
            $this->tpl->setVariable("TXT_SINGLE_CMD", $txt);
            $this->tpl->setVariable("SINGLE_CMD", $cmd);
            $this->tpl->parseCurrentBlock();
            $arrow = true;
            $action_row = true;

            if ($this->getTopCommands()) {
                $this->tpl->setCurrentBlock("tbl_top_single_cmd");
                foreach ($this->multi as $mc) {
                    $cmd = $mc['cmd'];
                    $txt = $mc['text'];
                }
                $this->tpl->setVariable("TXT_SINGLE_CMD", $txt);
                $this->tpl->setVariable("SINGLE_CMD", $cmd);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($arrow) {
            $this->tpl->setCurrentBlock("tbl_action_img_arrow");
            $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
            $this->tpl->setVariable("ALT_ARROW", $lng->txt("action"));
            $this->tpl->parseCurrentBlock();

            if ($this->getTopCommands()) {
                $this->tpl->setCurrentBlock("tbl_top_action_img_arrow");
                $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_upright.svg"));
                $this->tpl->setVariable("ALT_ARROW", $lng->txt("action"));
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($action_row) {
            $this->tpl->setCurrentBlock("tbl_action_row");
            $this->tpl->parseCurrentBlock();
            if ($this->getTopCommands()) {
                $this->tpl->setCurrentBlock("tbl_top_action_row");
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    public function setHeaderHTML(string $html): void
    {
        $this->headerHTML = $html;
    }

    public function storeProperty(string $type, string $value): void
    {
        global $DIC;

        $ilUser = null;
        if (isset($DIC["ilUser"])) {
            $ilUser = $DIC["ilUser"];
        }

        if (is_object($ilUser) && $this->getId() != "") {
            $tab_prop = new ilTablePropertiesStorageGUI();

            $tab_prop->storeProperty($this->getId(), $ilUser->getId(), $type, $value);
        }
    }

    public function loadProperty(string $type): ?string
    {
        global $DIC;

        $ilUser = null;
        if (isset($DIC["ilUser"])) {
            $ilUser = $DIC["ilUser"];
        }

        if (is_object($ilUser) && $this->getId() != "") {
            $tab_prop = new ilTablePropertiesStorageGUI();

            return $tab_prop->getProperty($this->getId(), $ilUser->getId(), $type);
        }
        return null;
    }

    /**
     * get current settings for order, limit, columns and filter
     */
    public function getCurrentState(): array
    {
        $this->determineOffsetAndOrder();
        $this->determineLimit();
        $this->determineSelectedColumns();
        $this->determineSelectedFilters();

        // "filter" show/hide is not saved

        $result = array();
        $result["order"] = $this->getOrderField();
        $result["direction"] = $this->getOrderDirection();
        $result["offset"] = $this->getOffset();
        $result["rows"] = $this->getLimit();
        $result["selfilters"] = $this->getSelectedFilters();

        // #9514 - $this->getSelectedColumns() will omit deselected, leading to
        // confusion on restoring template
        $result["selfields"] = $this->selected_column;

        // gather filter values
        if ($this->filters) {
            foreach ($this->filters as $item) {
                $result["filter_values"][$item->getFieldId()] = $this->getFilterValue($item);
            }
        }
        if ($this->optional_filters && $result["selfilters"]) {
            foreach ($this->optional_filters as $item) {
                if (in_array($item->getFieldId(), $result["selfilters"])) {
                    $result["filter_values"][$item->getFieldId()] = $this->getFilterValue($item);
                }
            }
        }

        return $result;
    }

    /**
     * Get current filter value
     * @return mixed
     */
    protected function getFilterValue(ilTableFilterItem $a_item)
    {
        if (method_exists($a_item, "getChecked")) {
            return (string) $a_item->getChecked();
        } elseif (method_exists($a_item, "getValue")) {
            return $a_item->getValue() ?: "";
        } elseif (method_exists($a_item, "getDate")) {
            return $a_item->getDate()->get(IL_CAL_DATE);
        }
        return "";
    }

    /**
     * @param string|array|null $a_value
     * @throws ilDateTimeException
     */
    protected function setFilterValue(ilTableFilterItem $a_item, $a_value): void
    {
        if (method_exists($a_item, "setChecked")) {
            $a_item->setChecked((bool) $a_value);
        } elseif (method_exists($a_item, "setValue")) {
            $a_item->setValue($a_value);
        } elseif (method_exists($a_item, "setDate")) {
            $a_item->setDate(new ilDate($a_value, IL_CAL_DATE));
        }
        $a_item->writeToSession();
    }

    public function setContext(string $id): void
    {
        if (trim($id)) {
            $this->context = $id;
        }
    }

    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Toggle rows-per-page selector
     */
    public function setShowRowsSelector(bool $a_value): void
    {
        $this->show_rows_selector = $a_value;
    }

    public function getShowRowsSelector(): bool
    {
        return $this->show_rows_selector;
    }

    public function setShowTemplates(bool $a_value): void
    {
        $this->show_templates = $a_value;
    }

    public function getShowTemplates(): bool
    {
        return $this->show_templates;
    }

    /**
     * Restore state from template
     */
    public function restoreTemplate(string $a_name): bool
    {
        global $DIC;

        $ilUser = $DIC->user();

        $a_name = ilUtil::stripSlashes($a_name);

        if (trim($a_name) && $this->getContext() != "" && is_object($ilUser) && $ilUser->getId() != ANONYMOUS_USER_ID) {
            $storage = new ilTableTemplatesStorage();

            $data = $storage->load($this->getContext(), $ilUser->getId(), $a_name);
            if (is_array($data)) {
                foreach ($data as $property => $value) {
                    $this->storeProperty($property, $value);
                }
            }

            $data["filter_values"] = unserialize($data["filter_values"]);
            if ($data["filter_values"]) {
                $this->restore_filter_values = $data["filter_values"];
            }

            $this->restore_filter = true;

            return true;
        }
        return false;
    }

    /**
     * Save current state as template
     */
    public function saveTemplate(string $a_name): bool
    {
        global $DIC;

        $ilUser = $DIC->user();

        $a_name = ilLegacyFormElementsUtil::prepareFormOutput($a_name, true);

        if (trim($a_name) && $this->getContext() != "" && is_object($ilUser) && $ilUser->getId() != ANONYMOUS_USER_ID) {
            $storage = new ilTableTemplatesStorage();

            $state = $this->getCurrentState();
            $state["filter_values"] = serialize($state["filter_values"] ?? null);
            $state["selfields"] = serialize($state["selfields"] ?? null);
            $state["selfilters"] = serialize($state["selfilters"] ?? null);

            $storage->store($this->getContext(), $ilUser->getId(), $a_name, $state);
            return true;
        }
        return false;
    }

    public function deleteTemplate(string $a_name): bool
    {
        global $DIC;

        $ilUser = $DIC->user();

        $a_name = ilLegacyFormElementsUtil::prepareFormOutput($a_name, true);

        if (trim($a_name) && $this->getContext() != "" && is_object($ilUser) && $ilUser->getId() != ANONYMOUS_USER_ID) {
            $storage = new ilTableTemplatesStorage();
            $storage->delete($this->getContext(), $ilUser->getId(), $a_name);
            return true;
        }
        return false;
    }

    public function getLimit(): int
    {
        if ($this->getExportMode() || $this->getPrintMode()) {
            return 9999;
        }
        return parent::getLimit();
    }

    public function getOffset(): int
    {
        if ($this->getExportMode() || $this->getPrintMode()) {
            return 0;
        }
        return parent::getOffset();
    }

    /**
     * Set available export formats
     */
    public function setExportFormats(array $formats): void
    {
        $this->export_formats = array();

        // #11339
        $valid = array(self::EXPORT_EXCEL => "tbl_export_excel",
            self::EXPORT_CSV => "tbl_export_csv");

        foreach ($formats as $format) {
            if (array_key_exists($format, $valid)) {
                $this->export_formats[$format] = $valid[$format];
            }
        }
    }

    public function setPrintMode(bool $a_value = false): void
    {
        $this->print_mode = $a_value;
    }

    public function getPrintMode(): bool
    {
        return $this->print_mode;
    }

    public function getExportMode(): bool
    {
        return $this->export_mode;
    }

    /**
     * Export and optionally send current table data
     */
    public function exportData(string $format, bool $send = false): void
    {
        if ($this->dataExists()) {
            // #9640: sort
            if (!$this->getExternalSorting() && $this->enabled["sort"]) {
                $this->determineOffsetAndOrder(true);

                $this->row_data = ilArrayUtil::sortArray(
                    $this->row_data,
                    $this->getOrderField(),
                    $this->getOrderDirection(),
                    $this->numericOrdering($this->getOrderField())
                );
            }

            $filename = "export";

            switch ($format) {
                case self::EXPORT_EXCEL:
                    $excel = new ilExcel();
                    $excel->addSheet($this->title
                        ?: $this->lng->txt("export"));
                    $row = 1;

                    ob_start();
                    $this->fillMetaExcel($excel, $row); // row must be increment in fillMetaExcel()! (optional method)

                    // #14813
                    $pre = $row;
                    $this->fillHeaderExcel($excel, $row); // row should NOT be incremented in fillHeaderExcel()! (required method)
                    if ($pre == $row) {
                        $row++;
                    }

                    foreach ($this->row_data as $set) {
                        $this->fillRowExcel($excel, $row, $set);
                        $row++; // #14760
                    }
                    ob_end_clean();

                    if ($send) {
                        $excel->sendToClient($filename);
                    } else {
                        $excel->writeToFile($filename);
                    }
                    break;

                case self::EXPORT_CSV:
                    $csv = new ilCSVWriter();
                    $csv->setSeparator(";");

                    ob_start();
                    $this->fillMetaCSV($csv);
                    $this->fillHeaderCSV($csv);
                    foreach ($this->row_data as $set) {
                        $this->fillRowCSV($csv, $set);
                    }
                    ob_end_clean();

                    if ($send) {
                        $filename .= ".csv";
                        header("Content-type: text/comma-separated-values");
                        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
                        header("Expires: 0");
                        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
                        header("Pragma: public");
                        echo $csv->getCSVString();
                    } else {
                        file_put_contents($filename, $csv->getCSVString());
                    }
                    break;
            }

            if ($send) {
                exit();
            }
        }
    }

    /**
     * Add meta information to excel export. Likely to
     * be overwritten by derived class.
     * @param	ilExcel	$a_excel excel wrapper
     * @param	int		$a_row   row counter
     */
    protected function fillMetaExcel(ilExcel $a_excel, int &$a_row): void
    {
    }

    /**
     * Excel Version of Fill Header. Likely to
     * be overwritten by derived class.
     * @param	ilExcel	$a_excel excel wrapper
     * @param	int		$a_row   row counter
     */
    protected function fillHeaderExcel(ilExcel $a_excel, int &$a_row): void
    {
        $col = 0;
        foreach ($this->column as $column) {
            $title = strip_tags($column["text"]);
            if ($title) {
                $a_excel->setCell($a_row, $col++, $title);
            }
        }
        $a_excel->setBold("A" . $a_row . ":" . $a_excel->getColumnCoord($col - 1) . $a_row);
    }

    /**
    * Excel Version of Fill Row. Most likely to
    * be overwritten by derived class.
    * @param	ilExcel $a_excel excel wrapper
    * @param	int     $a_row   row counter
    * @param	array   $a_set   data array
    */
    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        $col = 0;
        foreach ($a_set as $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $a_excel->setCell($a_row, $col++, $value);
        }
    }

    /**
     * Add meta information to csv export. Likely to
     * be overwritten by derived class.
     * @param	ilCSVWriter $a_csv current file
     */
    protected function fillMetaCSV(ilCSVWriter $a_csv): void
    {
    }

    /**
     * CSV Version of Fill Header. Likely to
     * be overwritten by derived class.
     * @param	ilCSVWriter $a_csv current file
     */
    protected function fillHeaderCSV(ilCSVWriter $a_csv): void
    {
        foreach ($this->column as $column) {
            $title = strip_tags($column["text"]);
            if ($title) {
                $a_csv->addColumn($title);
            }
        }
        $a_csv->addRow();
    }

    /**
     * CSV Version of Fill Row. Most likely to
     * be overwritten by derived class.
     * @param	ilCSVWriter $a_csv current file
     * @param	array       $a_set data array
     */
    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        foreach ($a_set as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $a_csv->addColumn(strip_tags($value));
        }
        $a_csv->addRow();
    }

    public function setEnableAllCommand(bool $a_value): void
    {
        $this->enable_command_for_all = $a_value;
    }

    public static function getAllCommandLimit(): int
    {
        global $DIC;

        $ilClientIniFile = $DIC["ilClientIniFile"];

        $limit = $ilClientIniFile->readVariable("system", "TABLE_ACTION_ALL_LIMIT");
        if (!$limit) {
            $limit = self::ACTION_ALL_LIMIT;
        }

        return $limit;
    }

    public function setRowSelectorLabel(string $row_selector_label): void
    {
        $this->row_selector_label = $row_selector_label;
    }

    public function getRowSelectorLabel(): string
    {
        return $this->row_selector_label;
    }

    public function setPreventDoubleSubmission(bool $a_val): void
    {
        $this->prevent_double_submission = $a_val;
    }

    public function getPreventDoubleSubmission(): bool
    {
        return $this->prevent_double_submission;
    }

    public function setLimit(int $a_limit = 0, int $a_default_limit = 0): void
    {
        parent::setLimit($a_limit, $a_default_limit);

        // #17077 - if limit is set "manually" to 9999, force rows selector off
        if ($a_limit == 9999 &&
            $this->limit_determined) {
            $this->rows_selector_off = true;
        }
    }
}
