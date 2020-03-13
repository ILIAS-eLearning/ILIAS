<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCTable.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
 * Class ilPCTableGUI
 *
 * User Interface for Table Editing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCTableGUI extends ilPageContentGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilObjUser
     */
    protected $user;


    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        $this->setCharacteristics(array("StandardTable" => $this->lng->txt("cont_StandardTable")));
    }

    /**
    * Set basic table cell styles
    */
    public function setBasicTableCellStyles()
    {
        $this->setCharacteristics(array("Cell1" => "Cell1", "Cell2" => "Cell2",
            "Cell3" => "Cell3", "Cell4" => "Cell4"));
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $this->getCharacteristicsOfCurrentStyle("table");	// scorm-2004
        
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }


    /**
    * Set tabs
    */
    public function setTabs()
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilTabs->setBackTarget(
            $lng->txt("pg"),
            $this->ctrl->getParentReturn($this)
        );
        
        $ilTabs->addTarget(
            "cont_table_properties",
            $ilCtrl->getLinkTarget($this, "edit"),
            "edit",
            get_class($this)
        );

        $ilTabs->addTarget(
            "cont_table_cell_properties",
            $ilCtrl->getLinkTarget($this, "editCellStyle"),
            "editCellStyle",
            get_class($this)
        );
    }
    
    /**
    * Set tabs
    */
    public function setCellPropertiesSubTabs()
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

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

    /**
    * Get table templates
    */
    public function getTemplateOptions($a_type = "")
    {
        return parent::getTemplateOptions("table");
    }

    /**
    * edit properties form
    */
    public function edit()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        $this->displayValidationError();
        $this->setTabs();
        
        $this->initPropertiesForm();
        $this->getPropertiesFormValues();
        $html = $this->form->getHTML();
        $html.= "<br />" . $this->renderTable("");
        $tpl->setContent($html);
    }
    
    /**
    * Init properties form
    */
    public function initPropertiesForm($a_mode = "edit")
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilUser = $this->user;
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_mode == "create") {
            $this->form->setTitle($this->lng->txt("cont_insert_table"));
        } else {
            $this->form->setTitle($this->lng->txt("cont_table_properties"));
        }

        if ($a_mode == "create") {
            $nr = array();
            for ($i=1; $i<=20; $i++) {
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
        /*$spacing = new ilTextInputGUI($this->lng->txt("cont_table_cellspacing"), "spacing");
        $spacing->setValue("0px");
        $spacing->setSize(6);
        $spacing->setMaxLength(6);
        $this->form->addItem($spacing);*/

        // table templates and table classes
        require_once("./Services/Form/classes/class.ilAdvSelectInputGUI.php");
        $char_prop = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_characteristic"),
            "characteristic"
        );
        $chars = $this->getCharacteristics();
        $templates = $this->getTemplateOptions();
        $chars = array_merge($templates, $chars);
        if (is_object($this->content_obj)) {
            if ($chars[$a_seleted_value] == "" && ($this->content_obj->getClass() != "")) {
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
        for ($i=0; $i<=3; $i++) {
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
            require_once("./Services/Form/classes/class.ilAdvSelectInputGUI.php");
            $fr_style = new ilAdvSelectInputGUI(
                $this->lng->txt("cont_first_row_style"),
                "first_row_style"
            );
            $this->setBasicTableCellStyles();
            $this->getCharacteristicsOfCurrentStyle("table_cell");
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
        if ($_SESSION["il_text_lang_" . $_GET["ref_id"]] != "") {
            $s_lang = $_SESSION["il_text_lang_" . $_GET["ref_id"]];
        } else {
            $s_lang = $ilUser->getLanguage();
        }
        require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
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

    /**
    * Get properties form
    */
    public function getPropertiesFormValues()
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

    /**
    * Render the table
    */
    public function renderTable($a_mode = "table_edit", $a_submode = "")
    {
        $tab_node = $this->content_obj->getNode();
        $tab_node->set_attribute("Enabled", "True");
        $content = $this->dom->dump_node($tab_node);

        $trans = $this->pg_obj->getLanguageVariablesXML();
        $mobs = $this->pg_obj->getMultimediaXML();
        if ($this->getStyleId() > 0) {
            if (ilObject::_lookupType($this->getStyleId()) == "sty") {
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $style = new ilObjStyleSheet($this->getStyleId());
                $template_xml = $style->getTemplateXML();
            }
        }

        $content = $content . $mobs . $trans . $template_xml;
        
        return ilPCTableGUI::_renderTable($content, $a_mode, $a_submode, $this->content_obj,
            !$this->pg_obj->getPageConfig()->getPreventHTMLUnmasking());
    }
        
    /**
    * Static render table function
    */
    public static function _renderTable($content, $a_mode = "table_edit", $a_submode = "", $a_table_obj = null,
        $unmask = true)
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        $content = "<dummy>" . $content . "</dummy>";

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $content, '/_xsl' => $xsl );
        $xh = xslt_create();
        //echo "<b>XML</b>:".htmlentities($content).":<br>";
        //echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
        $wb_path = ilUtil::getWebspaceDir("output");
        $enlarge_path = ilUtil::getImagePath("enlarge.svg");
        $params = array('mode' => $a_mode,
            'media_mode' => $ilUser->getPref("ilPageEditor_MediaMode"),
            'media_mode' => 'disable',
            'webspace_path' => $wb_path, 'enlarge_path' => $enlarge_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        echo xslt_error($xh);
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
        
        
        return '<div class="ilFloatLeft">' . $output . '</div>';
    }
    
    /**
     * Add style checkboxes in edit mode
     */
    public static function _addStyleCheckboxes($a_output, $a_table)
    {
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
    public static function _addAlignmentCheckboxes($a_output, $a_table)
    {
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
    public static function _addWidthInputs($a_output, $a_table)
    {
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
    public static function _addSpanInputs($a_output, $a_table)
    {
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
                $selects.= '<option value="' . $i . '" ' . $sel_str . '>' . $i . '</option>';
            }
            $selects.= "</select></div>";

            // rowspans
            $selects.= '<div style="margin-top:3px; white-space:nowrap;">' . $lng->txt("cont_rowspan") . ": " .
                '<select class="small" name="rowspan[' . $k . ']">';
            for ($i = 1; $i <= $v["max_y"] - $v["y"] + 1; $i++) {
                $sel_str = ($i == $v["rowspan"])
                    ? 'selected="selected"'
                    : '';
                $selects.= '<option value="' . $i . '" ' . $sel_str . '>' . $i . '</option>';
            }
            $selects.= "</select></div>";

            $a_output = str_replace("{{{{{TableEdit;" . $k . "}}}}}", $selects, $a_output);
        }
        return $a_output;
    }
    
    /**
     * Edit cell styles
     */
    public function editCellStyle()
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
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->lng->txt("cont_table_cell_properties"));

        // first row style
        require_once("./Services/Form/classes/class.ilAdvSelectInputGUI.php");
        $style = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_style"),
            "style"
        );
        $this->setBasicTableCellStyles();
        $this->getCharacteristicsOfCurrentStyle("table_cell");	// scorm-2004
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
        $html.= "<br />" . $this->renderTable("table_edit", "style") . "</form>";
        $tpl->setContent($html);
    }

    /**
    * Edit cell widths
    */
    public function editCellWidth()
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
        $html.= "<br />" . $this->renderTable("table_edit", "width") . "</form>";
        $tpl->setContent($html);
    }

    /**
    * Edit cell spans
    */
    public function editCellSpan()
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
        $html.= "<br />" . $this->renderTable("table_edit", "span") . "</form>";
        $tpl->setContent($html);
    }

    /**
     * Set cell styles and
     */
    public function setStyles()
    {
        $lng = $this->lng;
        
        if (is_array($_POST["target"])) {
            foreach ($_POST["target"] as $k => $value) {
                if ($value > 0) {
                    $cid = explode(":", $k);
                    $this->content_obj->setTDClass(
                        ilUtil::stripSlashes($cid[0]),
                        ilUtil::stripSlashes($_POST["style"]),
                        ilUtil::stripSlashes($cid[1])
                    );
                }
            }
        }
        $this->updated = $this->pg_obj->update();
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editCellStyle");
    }
    
    /**
    * Set cell widths
    */
    public function setWidths()
    {
        $lng = $this->lng;
        
        if (is_array($_POST["width"])) {
            foreach ($_POST["width"] as $k => $width) {
                $cid = explode(":", $k);
                $this->content_obj->setTDWidth(
                    ilUtil::stripSlashes($cid[0]),
                    ilUtil::stripSlashes($width),
                    ilUtil::stripSlashes($cid[1])
                );
            }
        }
        $this->updated = $this->pg_obj->update();
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editCellWidth");
    }

    /**
    * Set cell spans
    */
    public function setSpans()
    {
        $lng = $this->lng;
        
        if (is_array($_POST["colspan"])) {
            foreach ($_POST["colspan"] as $k => $span) {
                $_POST["colspan"][$k] = ilUtil::stripSlashes($span);
                $_POST["rowspan"][$k] = ilUtil::stripSlashes($_POST["rowspan"][$k]);
            }
            $this->content_obj->setTDSpans($_POST["colspan"], $_POST["rowspan"]);
        }
        $this->updated = $this->pg_obj->update();
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editCellSpan");
    }

    /**
    * Set properties from input form
    */
    public function setProperties()
    {
        // mask html
        $caption = ilUtil::stripSlashes($_POST["caption"]);
        $caption = str_replace("&", "&amp;", $caption);
        $caption = str_replace("<", "&lt;", $caption);
        $caption = str_replace(">", "&gt;", $caption);

        $this->content_obj->setLanguage(ilUtil::stripSlashes($_POST["language"]));
        $this->content_obj->setWidth(ilUtil::stripSlashes($_POST["width"]));
        $this->content_obj->setBorder(ilUtil::stripSlashes($_POST["border"]));
        $this->content_obj->setCellSpacing(ilUtil::stripSlashes($_POST["spacing"]));
        $this->content_obj->setCellPadding(ilUtil::stripSlashes($_POST["padding"]));
        $this->content_obj->setHorizontalAlign(ilUtil::stripSlashes($_POST["align"]));
        $this->content_obj->setHeaderRows(ilUtil::stripSlashes($_POST["row_header"]));
        $this->content_obj->setHeaderCols(ilUtil::stripSlashes($_POST["col_header"]));
        $this->content_obj->setFooterRows(ilUtil::stripSlashes($_POST["row_footer"]));
        $this->content_obj->setFooterCols(ilUtil::stripSlashes($_POST["col_footer"]));
        if (strpos($_POST["characteristic"], ":") > 0) {
            $t = explode(":", $_POST["characteristic"]);
            $this->content_obj->setTemplate(ilUtil::stripSlashes($t[2]));
            $this->content_obj->setClass("");
        } else {
            $this->content_obj->setClass(ilUtil::stripSlashes($_POST["characteristic"]));
            $this->content_obj->setTemplate("");
        }
        $this->content_obj->setCaption(
            $caption,
            ilUtil::stripSlashes($_POST["cap_align"])
        );
    }
    
    /**
    * save table properties in db and return to page edit screen
    */
    public function saveProperties()
    {
        $this->setProperties();
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->redirect($this, "edit");
        //$this->ctrl->returnToParent($this, "jump".$this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }

    /**
    * align table to right
    */
    public function rightAlign()
    {
        $this->content_obj->setHorizontalAlign("Right");
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * align table to left
    */
    public function leftAlign()
    {
        $this->content_obj->setHorizontalAlign("Left");
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * align table to left
    */
    public function centerAlign()
    {
        $this->content_obj->setHorizontalAlign("Center");
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * align table to left float
    */
    public function leftFloatAlign()
    {
        $this->content_obj->setHorizontalAlign("LeftFloat");
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * align table to left
    */
    public function rightFloatAlign()
    {
        $this->content_obj->setHorizontalAlign("RightFloat");
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * insert new table form
    */
    public function insert()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        $this->displayValidationError();

        $this->initPropertiesForm("create");
        $html = $this->form->getHTML();
        $tpl->setContent($html);
    }

    /**
    * Get new table object
    */
    public function getNewTableObject()
    {
        return new ilPCTable($this->getPage());
    }

    /**
    * create new table in dom and update page in db
    */
    public function create()
    {
        global	$lng;
        
        $this->content_obj = $this->getNewTableObject();
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $import_table = trim($_POST["import_table"]);
        
        // import xhtml or spreadsheet table
        if (!empty($import_table)) {
            switch ($_POST["import_type"]) {
                // xhtml import
                case "html":
                    if (!$this->content_obj->importHtml($_POST["language"], $import_table)) {
                        $this->insert();
                        return;
                    }
                    break;
                    
                // spreadsheet
                case "spreadsheet":
                    $this->content_obj->importSpreadsheet($_POST["language"], $import_table);
                    break;
            }
        } else {
            $this->content_obj->addRows(
                ilUtil::stripSlashes($_POST["nr_rows"]),
                ilUtil::stripSlashes($_POST["nr_cols"])
            );
        }
        
        $this->setProperties();
        
        $frtype = ilUtil::stripSlashes($_POST["first_row_style"]);
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
    
    /**
    * After creation processing
    */
    public function afterCreation()
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }


    //
    // Edit cell alignments
    //

    /**
     * Edit cell styles
     */
    public function editCellAlignment()
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
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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
        $html.= "<br />" . $this->renderTable("table_edit", "alignment") . "</form>";
        $tpl->setContent($html);
    }

    /**
     * Set cell alignments
     */
    public function setAlignment()
    {
        $lng = $this->lng;

        if (is_array($_POST["target"])) {
            foreach ($_POST["target"] as $k => $value) {
                if ($value > 0) {
                    $cid = explode(":", $k);
                    $this->content_obj->setTDAlignment(
                        ilUtil::stripSlashes($cid[0]),
                        ilUtil::stripSlashes($_POST["alignment"]),
                        ilUtil::stripSlashes($cid[1])
                    );
                }
            }
        }
        $this->updated = $this->pg_obj->update();
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, "editCellAlignment");
    }
}
