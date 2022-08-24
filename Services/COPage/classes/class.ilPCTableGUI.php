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
 * User Interface for Table Editing
 * @author Alexander Killing <killing@leifos.de>

 * See https://mantis.ilias.de/view.php?id=32856
 * @ilCtrl_Calls ilPCTableGUI: ilAssGenFeedbackPageGUI
 */
class ilPCTableGUI extends ilPageContentGUI
{
    protected ilPropertyFormGUI $form;
    protected ilTabsGUI $tabs;
    protected ilObjUser $user;
    protected \ILIAS\GlobalScreen\ScreenContext\ContextServices $tool_context;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        $this->setCharacteristics(array("StandardTable" => $this->lng->txt("cont_StandardTable")));
        $this->tool_context = $DIC->globalScreen()->tool()->context();
    }

    public function setBasicTableCellStyles(): void
    {
        $this->setCharacteristics(array("Cell1" => "Cell1", "Cell2" => "Cell2",
            "Cell3" => "Cell3", "Cell4" => "Cell4"));
    }

    /**
     * @return mixed
     */
    public function executeCommand()
    {
        $this->getCharacteristicsOfCurrentStyle(["table"]);	// scorm-2004

        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
        return "";
    }

    public function setTabs(
        string $data_tab_txt_key = ""
    ): void {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilTabs->setBackTarget(
            $lng->txt("pg"),
            (string) $this->ctrl->getParentReturn($this)
        );

        if ($data_tab_txt_key == "") {
            $data_tab_txt_key = "cont_table_edit_cells";
        }

        $ilTabs->addTarget(
            $data_tab_txt_key,
            $ilCtrl->getLinkTarget($this, "editData"),
            "editData",
            get_class($this)
        );

        $ilTabs->addTarget(
            "cont_table_properties",
            $ilCtrl->getLinkTarget($this, "editProperties"),
            "editProperties",
            get_class($this)
        );

        $ilTabs->addTarget(
            "cont_table_cell_properties",
            $ilCtrl->getLinkTarget($this, "editCellStyle"),
            "editCellStyle",
            get_class($this)
        );
    }

    public function setCellPropertiesSubTabs(): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubTabTarget(
            "cont_style",
            $ilCtrl->getLinkTarget($this, "editCellStyle"),
            "editCellStyle",
            get_class($this)
        );

        $ilTabs->addSubTabTarget(
            "cont_width",
            $ilCtrl->getLinkTarget($this, "editCellWidth"),
            "editCellWidth",
            get_class($this)
        );

        $ilTabs->addSubTabTarget(
            "cont_alignment",
            $ilCtrl->getLinkTarget($this, "editCellAlignment"),
            "editCellAlignment",
            get_class($this)
        );

        $ilTabs->addSubTabTarget(
            "cont_span",
            $ilCtrl->getLinkTarget($this, "editCellSpan"),
            "editCellSpan",
            get_class($this)
        );
    }

    public function getTemplateOptions(string $a_type = ""): array
    {
        return parent::getTemplateOptions("table");
    }

    public function edit(): void
    {
        $this->ctrl->redirect($this, "editData");
    }

    public function editProperties(): void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();
        $this->setTabs();

        $this->initPropertiesForm();
        $this->getPropertiesFormValues();
        $html = $this->form->getHTML();
        $html .= "<br />" . $this->renderTable("");
        $tpl->setContent($html);
    }

    protected function getFormTitle(string $a_mode = "edit"): string
    {
        if ($a_mode === "create") {
            return $this->lng->txt("cont_insert_table");
        }
        return $this->lng->txt("cont_table_properties");
    }

    public function initPropertiesForm(
        string $a_mode = "edit"
    ): void {
        $a_seleted_value = "";
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $this->form->setTitle($this->getFormTitle($a_mode));

        if ($a_mode == "create") {
            $nr = array();
            for ($i = 1; $i <= 20; $i++) {
                $nr[$i] = $i;
            }

            // cols
            $cols = new ilSelectInputGUI($this->lng->txt("cont_nr_cols"), "nr_cols");
            $cols->setOptions($nr);
            $cols->setValue(2);
            $this->form->addItem($cols);

            // rows
            $rows = new ilSelectInputGUI($this->lng->txt("cont_nr_rows"), "nr_rows");
            $rows->setOptions($nr);
            $rows->setValue(2);
            $this->form->addItem($rows);
        }

        // width
        $width = new ilTextInputGUI($this->lng->txt("cont_table_width"), "width");
        $width->setSize(6);
        $width->setMaxLength(6);
        $this->form->addItem($width);

        // border
        $border = new ilTextInputGUI($this->lng->txt("cont_table_border"), "border");
        $border->setInfo($this->lng->txt("cont_table_border_info"));
        $border->setValue("1px");
        $border->setSize(6);
        $border->setMaxLength(6);
        $this->form->addItem($border);

        // padding
        $padding = new ilTextInputGUI($this->lng->txt("cont_table_cellpadding"), "padding");
        $padding->setInfo($this->lng->txt("cont_table_cellpadding_info"));
        $padding->setValue("2px");
        $padding->setSize(6);
        $padding->setMaxLength(6);
        $this->form->addItem($padding);

        // spacing (deprecated, only hidden)
        $spacing = new ilHiddenInputGUI("spacing");
        $spacing->setValue("0px");
        $this->form->addItem($spacing);

        // table templates and table classes
        $char_prop = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_characteristic"),
            "characteristic"
        );
        $chars = $this->getCharacteristics();
        $templates = $this->getTemplateOptions();
        $chars = array_merge($templates, $chars);
        if (is_object($this->content_obj)) {
            if (($chars[$a_seleted_value] ?? "") == "" && ($this->content_obj->getClass() != "")) {
                $chars = array_merge(
                    array($this->content_obj->getClass() => $this->content_obj->getClass()),
                    $chars
                );
            }
        }
        foreach ($chars as $k => $char) {
            if (strpos($k, ":") > 0) {
                $t = explode(":", $k);
                $html = $this->style->lookupTemplatePreview($t[1]) . '<div style="clear:both;" class="small">' . $char . "</div>";
            } else {
                $html = '<table class="ilc_table_' . $k . '"><tr><td class="small">' .
                    $char . '</td></tr></table>';
            }
            $char_prop->addOption($k, $char, $html);
        }
        $char_prop->setValue("StandardTable");
        $this->form->addItem($char_prop);

        $nr = array();
        for ($i = 0; $i <= 3; $i++) {
            $nr[$i] = $i;
        }

        // row header
        $rh = new ilSelectInputGUI($this->lng->txt("cont_nr_row_header"), "row_header");
        $rh->setOptions($nr);
        $rh->setValue(1);
        $this->form->addItem($rh);

        // row footer
        $rf = new ilSelectInputGUI($this->lng->txt("cont_nr_row_footer"), "row_footer");
        $rf->setOptions($nr);
        $rf->setValue(0);
        $this->form->addItem($rf);

        // col header
        $ch = new ilSelectInputGUI($this->lng->txt("cont_nr_col_header"), "col_header");
        $ch->setOptions($nr);
        $ch->setValue(0);
        $this->form->addItem($ch);

        // col footer
        $cf = new ilSelectInputGUI($this->lng->txt("cont_nr_col_footer"), "col_footer");
        $cf->setOptions($nr);
        $cf->setValue(0);
        $this->form->addItem($cf);

        if ($a_mode == "create") {
            // first row style
            $fr_style = new ilAdvSelectInputGUI(
                $this->lng->txt("cont_first_row_style"),
                "first_row_style"
            );
            $this->setBasicTableCellStyles();
            $this->getCharacteristicsOfCurrentStyle(["table_cell"]);
            $chars = $this->getCharacteristics();
            $options = array_merge(array("" => $this->lng->txt("none")), $chars);
            foreach ($options as $k => $option) {
                $html = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="ilc_table_cell_' . $k . '">' .
                    $option . '</td></tr></table>';
                $fr_style->addOption($k, $option, $html);
            }

            $fr_style->setValue("");
            $this->form->addItem($fr_style);
        }

        // alignment
        $align_opts = array("Left" => $lng->txt("cont_left"),
            "Right" => $lng->txt("cont_right"), "Center" => $lng->txt("cont_center"),
            "LeftFloat" => $lng->txt("cont_left_float"),
            "RightFloat" => $lng->txt("cont_right_float"));
        $align = new ilSelectInputGUI($this->lng->txt("cont_align"), "align");
        $align->setOptions($align_opts);
        $align->setValue("Center");
        $this->form->addItem($align);

        // caption
        $caption = new ilTextInputGUI($this->lng->txt("cont_caption"), "caption");
        $caption->setSize(60);
        $this->form->addItem($caption);

        // caption align
        $ca_opts = array("top" => $lng->txt("cont_top"),
            "bottom" => $lng->txt("cont_bottom"));
        $ca = new ilSelectInputGUI(
            $this->lng->txt("cont_align"),
            "cap_align"
        );
        $ca->setOptions($ca_opts);
        $caption->addSubItem($ca);

        // import
        if ($a_mode == "create") {
            // import table
            $import = new ilRadioGroupInputGUI($this->lng->txt("cont_paste_table"), "import_type");
            $op = new ilRadioOption($this->lng->txt("cont_html_table"), "html");
            $import->addOption($op);
            $op2 = new ilRadioOption($this->lng->txt("cont_spreadsheet_table"), "spreadsheet");

            $import_data = new ilTextAreaInputGUI("", "import_table");
            $import_data->setRows(8);
            $import_data->setCols(50);
            $op2->addSubItem($import_data);

            $import->addOption($op2);
            $import->setValue("html");
            $this->form->addItem($import);
        }

        // language
        if ($this->getCurrentTextLang() != "") {
            $s_lang = $this->getCurrentTextLang();
        } else {
            $s_lang = $ilUser->getLanguage();
        }
        $lang = ilMDLanguageItem::_getLanguages();
        $language = new ilSelectInputGUI($this->lng->txt("language"), "language");
        $language->setOptions($lang);
        $language->setValue($s_lang);
        $this->form->addItem($language);

        if ($a_mode == "create") {
            $this->form->addCommandButton("create_tab", $lng->txt("save"));
            $this->form->addCommandButton("cancelCreate", $lng->txt("cancel"));
        } else {
            $this->form->addCommandButton("saveProperties", $lng->txt("save"));
        }
    }

    public function getPropertiesFormValues(): void
    {
        $values = array();
        $values["width"] = $this->content_obj->getWidth();
        $values["border"] = $this->content_obj->getBorder();
        $values["padding"] = $this->content_obj->getCellPadding();
        $values["spacing"] = $this->content_obj->getCellSpacing();
        $values["row_header"] = $this->content_obj->getHeaderRows();
        $values["row_footer"] = $this->content_obj->getFooterRows();
        $values["col_header"] = $this->content_obj->getHeaderCols();
        $values["col_footer"] = $this->content_obj->getFooterCols();
        if ($this->content_obj->getTemplate() != "") {
            $values["characteristic"] = "t:" .
                ilObjStyleSheet::_lookupTemplateIdByName($this->getStyleId(), $this->content_obj->getTemplate()) . ":" .
                $this->content_obj->getTemplate();
        } else {
            $values["characteristic"] = $this->content_obj->getClass();
        }
        $values["align"] = $this->content_obj->getHorizontalAlign();
        $values["caption"] = $this->content_obj->getCaption();
        $values["cap_align"] = $this->content_obj->getCaptionAlign();
        $values["language"] = $this->content_obj->getLanguage();

        $this->form->setValuesByArray($values);

        $ca = $this->form->getItemByPostVar("cap_align");
        $ca->setValue($this->content_obj->getCaptionAlign());
    }

    public function renderTable(
        string $a_mode = "table_edit",
        string $a_submode = ""
    ): string {
        $template_xml = "";
        $tab_node = $this->content_obj->getNode();
        $tab_node->set_attribute("Enabled", "True");
        $content = $this->dom->dump_node($tab_node);

        $trans = $this->pg_obj->getLanguageVariablesXML();
        $mobs = $this->pg_obj->getMultimediaXML();
        if ($this->getStyleId() > 0) {
            if (ilObject::_lookupType($this->getStyleId()) == "sty") {
                $style = new ilObjStyleSheet($this->getStyleId());
                $template_xml = $style->getTemplateXML();
            }
        }

        $content = $content . $mobs . $trans . $template_xml;

        /** @var ilPCTable $tab */
        $tab = $this->content_obj;
        return ilPCTableGUI::_renderTable(
            $content,
            $a_mode,
            $a_submode,
            $tab,
            !$this->pg_obj->getPageConfig()->getPreventHTMLUnmasking(),
            $this->getPage()
        );
    }

    public static function _renderTable(
        string $content,
        string $a_mode = "table_edit",
        string $a_submode = "",
        ilPCTable $a_table_obj = null,
        bool $unmask = true,
        ilPageObject $page_object = null
    ): string {
        global $DIC;

        $ilUser = $DIC->user();

        $content = "<dummy>" . $content . "</dummy>";

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $content, '/_xsl' => $xsl );
        $xh = xslt_create();
        //echo "<b>XML</b>:".htmlentities($content).":<br>";
        //echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
        $wb_path = ilFileUtils::getWebspaceDir("output") . "/";
        $enlarge_path = ilUtil::getImagePath("enlarge.svg");
        $params = array('mode' => $a_mode,
            'webspace_path' => $wb_path, 'enlarge_path' => $enlarge_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        xslt_error($xh);
        xslt_free($xh);

        // unmask user html
        if ($unmask) {
            $output = str_replace("&lt;", "<", $output);
            $output = str_replace("&gt;", ">", $output);
            $output = str_replace("&amp;", "&", $output);
        }

        if ($a_mode == "table_edit" && !is_null($a_table_obj)) {
            switch ($a_submode) {
                case "style":
                    $output = ilPCTableGUI::_addStyleCheckboxes($output, $a_table_obj);
                    break;

                case "alignment":
                    $output = ilPCTableGUI::_addAlignmentCheckboxes($output, $a_table_obj);
                    break;

                case "width":
                    $output = ilPCTableGUI::_addWidthInputs($output, $a_table_obj);
                    break;

                case "span":
                    $output = ilPCTableGUI::_addSpanInputs($output, $a_table_obj);
                    break;
            }
        }

        // for all page components...
        if (isset($page_object)) {
            $defs = ilCOPagePCDef::getPCDefinitions();
            foreach ($defs as $def) {
                $pc_class = $def["pc_class"];
                $pc_obj = new $pc_class($page_object);

                // post xsl page content modification by pc elements
                $output = $pc_obj->modifyPageContentPostXsl($output, "presentation", false);
            }
        }

        return $output;
    }

    /**
     * Add style checkboxes in edit mode
     */
    public static function _addStyleCheckboxes(
        string $a_output,
        ilPCTable $a_table
    ): string {
        global $DIC;

        $lng = $DIC->language();

        $classes = $a_table->getAllCellClasses();

        foreach ($classes as $k => $v) {
            if ($v == "") {
                $v = $lng->txt("none");
            }
            if (substr($v, 0, 4) == "ilc_") {
                $v = substr($v, 4);
            }
            $check = $lng->txt("cont_style") . ": " .
                '<input type="checkbox" value="1"' .
                ' name="target[' . $k . ']">' . '</input> ' . $v;

            $a_output = str_replace("{{{{{TableEdit;" . $k . "}}}}}", $check, $a_output);
        }
        return $a_output;
    }

    /**
     * Add alignment checkboxes in edit mode
     */
    public static function _addAlignmentCheckboxes(
        string $a_output,
        ilPCTable $a_table
    ): string {
        global $DIC;

        $lng = $DIC->language();

        $classes = $a_table->getAllCellAlignments();

        foreach ($classes as $k => $v) {
            if ($v == "") {
                $v = $lng->txt("default");
            }
            $check = $lng->txt("cont_alignment") . ": " .
                '<input type="checkbox" value="1"' .
                ' name="target[' . $k . ']">' . '</input> ' . $v;

            $a_output = str_replace("{{{{{TableEdit;" . $k . "}}}}}", $check, $a_output);
        }
        return $a_output;
    }

    /**
     * Add width inputs
     */
    public static function _addWidthInputs(
        string $a_output,
        ilPCTable $a_table
    ): string {
        global $DIC;

        $lng = $DIC->language();

        $widths = $a_table->getAllCellWidths();

        foreach ($widths as $k => $v) {
            $check = $lng->txt("cont_width") . ": " .
                '<input class="small" type="text" size="5" maxlength="10"' .
                ' name="width[' . $k . ']" value="' . $v . '">' . '</input>';

            $a_output = str_replace("{{{{{TableEdit;" . $k . "}}}}}", $check, $a_output);
        }
        return $a_output;
    }

    /**
     * Add span inputs
     */
    public static function _addSpanInputs(
        string $a_output,
        ilPCTable $a_table
    ): string {
        global $DIC;

        $lng = $DIC->language();

        $spans = $a_table->getAllCellSpans();

        foreach ($spans as $k => $v) {
            // colspans
            $selects = '<div style="white-space:nowrap;">' . $lng->txt("cont_colspan") . ": " .
                '<select class="small" name="colspan[' . $k . ']">';
            for ($i = 1; $i <= $v["max_x"] - $v["x"] + 1; $i++) {
                $sel_str = ($i == $v["colspan"])
                    ? 'selected="selected"'
                    : '';
                $selects .= '<option value="' . $i . '" ' . $sel_str . '>' . $i . '</option>';
            }
            $selects .= "</select></div>";

            // rowspans
            $selects .= '<div style="margin-top:3px; white-space:nowrap;">' . $lng->txt("cont_rowspan") . ": " .
                '<select class="small" name="rowspan[' . $k . ']">';
            for ($i = 1; $i <= $v["max_y"] - $v["y"] + 1; $i++) {
                $sel_str = ($i == $v["rowspan"])
                    ? 'selected="selected"'
                    : '';
                $selects .= '<option value="' . $i . '" ' . $sel_str . '>' . $i . '</option>';
            }
            $selects .= "</select></div>";

            $a_output = str_replace("{{{{{TableEdit;" . $k . "}}}}}", $selects, $a_output);
        }
        return $a_output;
    }

    public function editCellStyle(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $this->displayValidationError();
        $this->setTabs();
        $this->setCellPropertiesSubTabs();
        $ilTabs->setSubTabActive("cont_style");

        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->lng->txt("cont_table_cell_properties"));

        // first row style
        $style = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_style"),
            "style"
        );
        $this->setBasicTableCellStyles();
        $this->getCharacteristicsOfCurrentStyle(["table_cell"]);	// scorm-2004
        $chars = $this->getCharacteristics();	// scorm-2004
        $options = array_merge(array("" => $this->lng->txt("none")), $chars);	// scorm-2004
        foreach ($options as $k => $option) {
            $html = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="ilc_table_cell_' . $k . '">' .
                $option . '</td></tr></table>';
            $style->addOption($k, $option, $html);
        }

        $style->setValue("");
        $style->setInfo($lng->txt("cont_set_tab_style_info"));
        $form->addItem($style);
        $form->setKeepOpen(true);

        $form->addCommandButton("setStyles", $lng->txt("cont_set_styles"));

        $html = $form->getHTML();
        $html .= "<br />" . $this->renderTable("table_edit", "style") . "</form>";
        $tpl->setContent($html);
    }

    public function editCellWidth(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $this->displayValidationError();
        $this->setTabs();
        $this->setCellPropertiesSubTabs();
        $ilTabs->setSubTabActive("cont_width");
        $ilTabs->setTabActive("cont_table_cell_properties");

        $ctpl = new ilTemplate("tpl.table_cell_properties.html", true, true, "Services/COPage");
        $ctpl->setVariable("BTN_NAME", "setWidths");
        $ctpl->setVariable("BTN_TEXT", $lng->txt("cont_save_widths"));
        $ctpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));

        $html = $ctpl->get();
        $html .= "<br />" . $this->renderTable("table_edit", "width") . "</form>";
        $tpl->setContent($html);
    }

    public function editCellSpan(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $this->displayValidationError();
        $this->setTabs();
        $this->setCellPropertiesSubTabs();
        $ilTabs->setSubTabActive("cont_span");
        $ilTabs->setTabActive("cont_table_cell_properties");

        $ctpl = new ilTemplate("tpl.table_cell_properties.html", true, true, "Services/COPage");
        $ctpl->setVariable("BTN_NAME", "setSpans");
        $ctpl->setVariable("BTN_TEXT", $lng->txt("cont_save_spans"));
        $ctpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));

        $html = $ctpl->get();
        $html .= "<br />" . $this->renderTable("table_edit", "span") . "</form>";
        $tpl->setContent($html);
    }

    /**
     * Set cell styles
     */
    public function setStyles(): void
    {
        $lng = $this->lng;

        $target = $this->request->getStringArray("target");
        if (count($target)) {
            foreach ($target as $k => $value) {
                if ($value > 0) {
                    $cid = explode(":", $k);
                    $this->content_obj->setTDClass(
                        ilUtil::stripSlashes($cid[0]),
                        $this->request->getString("style"),
                        ilUtil::stripSlashes($cid[1])
                    );
                }
            }
        }
        $this->updated = $this->pg_obj->update();
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editCellStyle");
    }

    /**
     * Set cell widths
     */
    public function setWidths(): void
    {
        $lng = $this->lng;

        $widths = $this->request->getStringArray("width");
        if (count($widths) > 0) {
            foreach ($widths as $k => $width) {
                $cid = explode(":", $k);
                $this->content_obj->setTDWidth(
                    ilUtil::stripSlashes($cid[0]),
                    ilUtil::stripSlashes($width),
                    ilUtil::stripSlashes($cid[1])
                );
            }
        }
        $this->updated = $this->pg_obj->update();
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editCellWidth");
    }

    /**
     * Set cell spans
     */
    public function setSpans(): void
    {
        $lng = $this->lng;

        $colspans = $this->request->getStringArray("colspan");
        $rowspans = $this->request->getStringArray("rowspan");
        $cs = [];
        $rs = [];
        if (count($colspans) > 0) {
            foreach ($colspans as $k => $span) {
                $cs[$k] = $span;
                $rs[$k] = $rowspans[$k];
            }
            $this->content_obj->setTDSpans($cs, $rs);
        }
        $this->updated = $this->pg_obj->update();
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editCellSpan");
    }

    /**
     * Set properties from input form
     */
    public function setProperties(): void
    {
        $this->initPropertiesForm();
        $this->form->checkInput();

        // mask html
        $caption = $this->form->getInput("caption");
        $caption = str_replace("&", "&amp;", $caption);
        $caption = str_replace("<", "&lt;", $caption);
        $caption = str_replace(">", "&gt;", $caption);

        $this->content_obj->setLanguage($this->form->getInput("language"));
        $this->content_obj->setWidth($this->form->getInput("width"));
        $this->content_obj->setBorder($this->form->getInput("border"));
        $this->content_obj->setCellSpacing($this->form->getInput("spacing"));
        $this->content_obj->setCellPadding($this->form->getInput("padding"));
        $this->content_obj->setHorizontalAlign($this->form->getInput("align"));
        $this->content_obj->setHeaderRows($this->form->getInput("row_header"));
        $this->content_obj->setHeaderCols($this->form->getInput("col_header"));
        $this->content_obj->setFooterRows($this->form->getInput("row_footer"));
        $this->content_obj->setFooterCols($this->form->getInput("col_footer"));
        if (strpos($this->form->getInput("characteristic"), ":") > 0) {
            $t = explode(":", $this->form->getInput("characteristic"));
            $this->content_obj->setTemplate($t[2]);
            $this->content_obj->setClass("");
        } else {
            $this->content_obj->setClass($this->form->getInput("characteristic"));
            $this->content_obj->setTemplate("");
        }
        $this->content_obj->setCaption(
            $caption,
            $this->form->getInput("cap_align")
        );
    }

    /**
     * save table properties in db and return to page edit screen
     */
    public function saveProperties(): void
    {
        $this->setProperties();
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->redirect($this, "editProperties");
        //$this->ctrl->returnToParent($this, "jump".$this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }

    public function rightAlign(): void
    {
        $this->content_obj->setHorizontalAlign("Right");
        $this->updateAndReturn();
    }

    public function leftAlign(): void
    {
        $this->content_obj->setHorizontalAlign("Left");
        $this->updateAndReturn();
    }

    public function centerAlign(): void
    {
        $this->content_obj->setHorizontalAlign("Center");
        $this->updateAndReturn();
    }

    public function leftFloatAlign(): void
    {
        $this->content_obj->setHorizontalAlign("LeftFloat");
        $this->updateAndReturn();
    }

    public function rightFloatAlign(): void
    {
        $this->content_obj->setHorizontalAlign("RightFloat");
        $this->updateAndReturn();
    }

    public function insert(): void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();
        $this->initPropertiesForm("create");
        $html = $this->form->getHTML();
        $tpl->setContent($html);
    }

    public function getNewTableObject(): ilPCTable
    {
        return new ilPCTable($this->getPage());
    }

    /**
     * create new table in dom and update page in db
     */
    public function create(): void
    {
        $this->content_obj = $this->getNewTableObject();
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);

        $this->initPropertiesForm("create");
        $this->form->checkInput();

        $import_table = trim($this->form->getInput("import_table"));

        // import xhtml or spreadsheet table
        if (!empty($import_table)) {
            switch ($this->form->getInput("import_type")) {
                // xhtml import
                case "html":
                    $res = $this->content_obj->importHtml(
                        $this->form->getInput("language"),
                        $import_table
                    );
                    if ($res !== true) {
                        $this->tpl->setOnScreenMessage('failure', $res);
                        $this->insert();
                        return;
                    }
                    break;

                // spreadsheet
                case "spreadsheet":
                    $this->content_obj->importSpreadsheet($this->form->getInput("language"), $import_table);
                    break;
            }
        } else {
            $this->content_obj->addRows(
                $this->form->getInput("nr_rows"),
                $this->form->getInput("nr_cols")
            );
        }

        $this->setProperties();

        $frtype = $this->form->getInput("first_row_style");
        if ($frtype != "") {
            $this->content_obj->setFirstRowStyle($frtype);
        }

        $this->updated = $this->pg_obj->update();

        if ($this->updated === true) {
            $this->afterCreation();
        } else {
            $this->insert();
        }
    }

    public function afterCreation(): void
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }


    //
    // Edit cell alignments
    //

    public function editCellAlignment(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $this->displayValidationError();
        $this->setTabs();
        $this->setCellPropertiesSubTabs();
        $ilTabs->setSubTabActive("cont_alignment");
        $ilTabs->setTabActive("cont_table_cell_properties");

        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->lng->txt("cont_table_cell_properties"));

        // alignment
        $options = array(
            "" => $lng->txt("default"),
            "Left" => $lng->txt("cont_left"),
            "Center" => $lng->txt("cont_center"),
            "Right" => $lng->txt("cont_right")
        );
        $si = new ilSelectInputGUI($lng->txt("cont_alignment"), "alignment");
        $si->setOptions($options);
        $si->setInfo($lng->txt(""));
        $form->addItem($si);

        $form->setKeepOpen(true);

        $form->addCommandButton("setAlignment", $lng->txt("cont_set_alignment"));

        $html = $form->getHTML();
        $html .= "<br />" . $this->renderTable("table_edit", "alignment") . "</form>";
        $tpl->setContent($html);
    }

    /**
     * Set cell alignments
     */
    public function setAlignment(): void
    {
        $lng = $this->lng;

        $targets = $this->request->getStringArray("target");
        if (count($targets) > 0) {
            foreach ($targets as $k => $value) {
                if ($value > 0) {
                    $cid = explode(":", $k);
                    $this->content_obj->setTDAlignment(
                        ilUtil::stripSlashes($cid[0]),
                        $this->request->getString("alignment"),
                        ilUtil::stripSlashes($cid[1])
                    );
                }
            }
        }
        $this->updated = $this->pg_obj->update();
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, "editCellAlignment");
    }

    /**
     * Set editor tool context
     */
    protected function setEditorToolContext(): void
    {
        $collection = $this->tool_context->current()->getAdditionalData();
        if ($collection->exists(ilCOPageEditGSToolProvider::SHOW_EDITOR)) {
            $collection->replace(ilCOPageEditGSToolProvider::SHOW_EDITOR, true);
        } else {
            $collection->add(ilCOPageEditGSToolProvider::SHOW_EDITOR, true);
        }
    }

    /**
     * Edit data of table
     */
    public function editData(): void
    {
        $this->setEditorToolContext();

        $this->setTabs();

        $this->displayValidationError();

        $editor_init = new \ILIAS\COPage\Editor\UI\Init();
        $editor_init->initUI($this->tpl);

        $this->tpl->setContent($this->getEditDataTable(true));
    }

    public function getEditDataTable(bool $initial = false): string
    {
        $ilCtrl = $this->ctrl;

        $dtpl = new ilTemplate("tpl.tabledata2.html", true, true, "Services/COPage");
        $dtpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "tableAction"));
        $dtpl->setVariable("HIERID", $this->hier_id);
        $dtpl->setVariable("PCID", $this->pc_id);

        $dtpl->setVariable(
            "WYSIWYG_ACTION",
            $ilCtrl->getFormAction($this, "updateJS")
        );

        // get all rows
        $xpc = xpath_new_context($this->dom);
        $path = "//PageContent[@HierId='" . $this->getHierId() . "']" .
            "/Table/TableRow";
        $res = xpath_eval($xpc, $path);

        for ($i = 0; $i < count($res->nodeset); $i++) {
            $xpc2 = xpath_new_context($this->dom);
            $path2 = "//PageContent[@HierId='" . $this->getHierId() . "']" .
                "/Table/TableRow[$i+1]/TableData";
            $res2 = xpath_eval($xpc2, $path2);

            // if this is the first row -> col icons
            if ($i == 0) {
                for ($j = 0; $j < count($res2->nodeset); $j++) {
                    if ($j == 0) {
                        $dtpl->touchBlock("empty_td");
                    }

                    $move_forward = false;
                    $move_backward = false;
                    if ($j == 0) {
                        if (count($res2->nodeset) == 1) {
                            //
                        } else {
                            $move_forward = true;
                        }
                    } elseif ($j == (count($res2->nodeset) - 1)) {
                        $move_backward = true;
                    } else {
                        $move_forward = true;
                        $move_backward = true;
                    }
                    $dtpl->setCurrentBlock("col_icon");
                    $dtpl->setVariable("NR_COLUMN", $j + 1);
                    $dtpl->setVariable("PCID_COLUMN", $res2->nodeset[$j]->get_attribute("PCID"));
                    $dtpl->setVariable("COLUMN_CAPTION", $this->getColumnCaption($j + 1));
                    $dtpl->parseCurrentBlock();
                }
                $dtpl->setCurrentBlock("row");
                $dtpl->parseCurrentBlock();
            }

            for ($j = 0; $j < count($res2->nodeset); $j++) {
                // first col: row icons
                if ($j == 0) {
                    if ($i == 0) {
                        if (count($res->nodeset) == 1) {
                            $move_type = "none";
                        } else {
                            $move_type = "forward";
                        }
                    } elseif ($i == (count($res->nodeset) - 1)) {
                        $move_type = "backward";
                    } else {
                        $move_type = "both";
                    }
                    $dtpl->setCurrentBlock("row_icon");
                    $dtpl->setVariable("NR_ROW", $i + 1);
                    $dtpl->setVariable("PCID_ROW", $res2->nodeset[$j]->get_attribute("PCID"));
                    $dtpl->setVariable("ROW_CAPTION", $i + 1);
                    $dtpl->parseCurrentBlock();
                }

                // cell
                if ($res2->nodeset[$j]->get_attribute("Hidden") != "Y") {
                    if ($this->content_obj->getType() == "dtab") {
                        $dtpl->touchBlock("cell_type");
                        //$dtpl->setCurrentBlock("cell_type");
                        //$dtpl->parseCurrentBlock();
                    }

                    $dtpl->setCurrentBlock("cell");

                    $dtpl->setVariable("PAR_TA_NAME", "cell[" . $i . "][" . $j . "]");
                    $dtpl->setVariable("PAR_TA_ID", "cell_" . $i . "_" . $j);
                    $dtpl->setVariable("PAR_ROW", (string) $i);
                    $dtpl->setVariable("PAR_COLUMN", (string) $j);

                    $dtpl->setVariable(
                        "PAR_TA_CONTENT",
                        $this->getCellContent($i, $j)
                    );

                    $cs = $res2->nodeset[$j]->get_attribute("ColSpan");
                    $rs = $res2->nodeset[$j]->get_attribute("RowSpan");
                    $dtpl->setVariable("WIDTH", "140");
                    $dtpl->setVariable("HEIGHT", "80");
                    if ($cs > 1) {
                        $dtpl->setVariable("COLSPAN", 'colspan="' . $cs . '"');
                        $dtpl->setVariable("WIDTH", (140 + ($cs - 1) * 146));
                    }
                    if ($rs > 1) {
                        $dtpl->setVariable("ROWSPAN", 'rowspan="' . $rs . '"');
                        $dtpl->setVariable("HEIGHT", (80 + ($rs - 1) * 86));
                    }
                    $dtpl->parseCurrentBlock();
                }
            }
            $dtpl->setCurrentBlock("row");
            $dtpl->parseCurrentBlock();
        }

        $dtpl->setVariable("TXT_ACTION", $this->lng->txt("cont_table"));

        // add int link parts
        $dtpl->setCurrentBlock("int_link_prep");
        $dtpl->setVariable(
            "INT_LINK_PREP",
            ilInternalLinkGUI::getInitHTML(
                $ilCtrl->getLinkTargetByClass(
                    array("ilpageeditorgui", "ilinternallinkgui"),
                    "",
                    false,
                    true,
                    false
                )
            )
        );
        $dtpl->parseCurrentBlock();

        if ($initial) {
            $dtpl->touchBlock("script");
        }

        return $dtpl->get();
    }

    protected function getColumnCaption(int $nr): string
    {
        $cap = "";
        $base = 26;
        while ($nr > 0) {
            $chr = ($nr - 1) % $base;
            $cap = chr($chr + 65) . $cap;
            $nr = ($nr - 1 - $chr) / $base;
        }
        return $cap;
    }

    protected function getCellContent(int $i, int $j): string
    {
        $tab_node = $this->content_obj->getNode();
        $cnt_i = 0;
        $content = "";
        $template_xml = "";
        // get correct cell and dump content of all its childrem
        foreach ($tab_node->first_child()->child_nodes() as $child) {
            if ($i == $cnt_i) {
                $cnt_j = 0;
                foreach ($child->child_nodes() as $child2) {
                    if ($j == $cnt_j) {
                        foreach ($child2->child_nodes() as $cell_content_node) {
                            $content .= $this->dom->dump_node($cell_content_node);
                        }
                    }
                    $cnt_j++;
                }
            }
            $cnt_i++;
        }
        $trans = $this->pg_obj->getLanguageVariablesXML();
        $mobs = $this->pg_obj->getMultimediaXML();
        if ($this->getStyleId() > 0) {
            if (ilObject::_lookupType($this->getStyleId()) == "sty") {
                $style = new ilObjStyleSheet($this->getStyleId());
                $template_xml = $style->getTemplateXML();
            }
        }

        $content = $content . $mobs . $trans . $template_xml;

        return $this->renderCell(
            $content,
            !$this->pg_obj->getPageConfig()->getPreventHTMLUnmasking(),
            $this->getPage()
        );
    }

    /**
     * Static render table function
     */
    protected function renderCell(
        $content,
        $unmask = true,
        $page_object = null
    ): string {
        global $DIC;

        $ilUser = $DIC->user();
        $content = "<dummy>" . $content . "</dummy>";

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $content, '/_xsl' => $xsl );
        $xh = xslt_create();
        $wb_path = ilFileUtils::getWebspaceDir("output") . "/";
        $enlarge_path = ilUtil::getImagePath("enlarge.svg");
        $params = array('webspace_path' => $wb_path, 'enlarge_path' => $enlarge_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        xslt_free($xh);

        // unmask user html
        if ($unmask) {
            $output = str_replace("&lt;", "<", $output);
            $output = str_replace("&gt;", ">", $output);
            $output = str_replace("&amp;", "&", $output);
        }

        // for all page components...
        if (isset($page_object)) {
            $defs = ilCOPagePCDef::getPCDefinitions();
            foreach ($defs as $def) {
                //ilCOPagePCDef::requirePCClassByName($def["name"]);
                $pc_class = $def["pc_class"];
                $pc_obj = new $pc_class($page_object);

                // post xsl page content modification by pc elements
                $output = $pc_obj->modifyPageContentPostXsl((string) $output, "presentation", false);
            }
        }


        return $output;
    }
}
