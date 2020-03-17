<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCParagraph.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCParagraphGUI
*
* User Interface for Paragraph Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCParagraphGUI extends ilPageContentGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        
        // characteristics (should be flexible in the future)
        $this->setCharacteristics(ilPCParagraphGUI::_getStandardCharacteristics());
    }
    
    /**
    * Get standard characteristics
    */
    public static function _getStandardCharacteristics()
    {
        global $DIC;

        $lng = $DIC->language();
        
        return array("Standard" => $lng->txt("cont_standard"),
            "Headline1" => $lng->txt("cont_Headline1"),
            "Headline2" => $lng->txt("cont_Headline2"),
            "Headline3" => $lng->txt("cont_Headline3"),
            "Citation" => $lng->txt("cont_Citation"),
            "Mnemonic" => $lng->txt("cont_Mnemonic"),
            "Example" => $lng->txt("cont_Example"),
            "Additional" => $lng->txt("cont_Additional"),
            "Attention" => $lng->txt("cont_Attention"),
            "Confirmation" => $lng->txt("cont_Confirmation"),
            "Information" => $lng->txt("cont_Information"),
            "Link" => $lng->txt("cont_Link"),
            "Literature" => $lng->txt("cont_Literature"),
            "Separator" => $lng->txt("cont_Separator"),
            "StandardCenter" => $lng->txt("cont_StandardCenter"),
            "Remark" => $lng->txt("cont_Remark"),
            "List" => $lng->txt("cont_List"),
            "TableContent" => $lng->txt("cont_TableContent")
        );
    }

    /**
     * Get standard characteristics
     */
    public static function _getStandardTextCharacteristics()
    {
        global $DIC;

        $lng = $DIC->language();
        return ["Mnemonic", "Attention"];
        return array("Mnemonic" => $lng->txt("cont_Mnemonic"),
            "Attention" => $lng->txt("cont_Attention")
        );
    }

    /**
    * Get characteristics
    */
    public static function _getCharacteristics($a_style_id)
    {
        $st_chars = ilPCParagraphGUI::_getStandardCharacteristics();
        $chars = ilPCParagraphGUI::_getStandardCharacteristics();

        if ($a_style_id > 0 &&
            ilObject::_lookupType($a_style_id) == "sty") {
            include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            $style = new ilObjStyleSheet($a_style_id);
            $types = array("text_block", "heading1", "heading2", "heading3");
            $chars = array();
            foreach ($types as $t) {
                $chars = array_merge($chars, $style->getCharacteristics($t));
            }
            $new_chars = array();
            foreach ($chars as $char) {
                if ($st_chars[$char] != "") {	// keep lang vars for standard chars
                    $new_chars[$char] = $st_chars[$char];
                } else {
                    $new_chars[$char] = $char;
                }
                asort($new_chars);
            }
            $chars = $new_chars;
        }

        return $chars;
    }

    /**
     * Get text characteristics
     *
     * @param int $a_style_id
     * @param bool $a_include_core include core styles
     * @return string[]
     */
    public static function _getTextCharacteristics($a_style_id, $a_include_core = false)
    {
        $chars = array();

        if ($a_style_id > 0 &&
            ilObject::_lookupType($a_style_id) == "sty") {
            include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            $style = new ilObjStyleSheet($a_style_id);
            $types = array("text_inline");
            foreach ($types as $t) {
                $chars = array_merge($chars, $style->getCharacteristics($t, false, $a_include_core));
            }
        } else {
            return self::_getStandardTextCharacteristics();
        }

        return $chars;
    }


    /**
    * execute command
    */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        $this->getCharacteristicsOfCurrentStyle(
            array("text_block", "heading1", "heading2", "heading3")
        );	// scorm-2004
        
        // get current command
        $cmd = $this->ctrl->getCmd();

        $this->log->debug("ilPCParagraphGUI: executeCommand " . $cmd);

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
    * edit paragraph form
    */
    public function edit($a_insert = false)
    {
        $ilUser = $this->user;
        
        // add paragraph edit template
        $tpl = new ilTemplate("tpl.paragraph_edit.html", true, true, "Services/COPage");

        // help text
        $this->insertHelp($tpl);
        
        // operations
        if ($a_insert) {
            $tpl->setCurrentBlock("commands");
            $tpl->setVariable("BTN_NAME", "create_par");
            $tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
            $tpl->setVariable("BTN_CANCEL", "cancelCreate");
            $tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
            $tpl->parseCurrentBlock();
            /*$tpl->setCurrentBlock("commands2");
            $tpl->setVariable("BTN_NAME", "create_par");
            $tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
            $tpl->setVariable("BTN_CANCEL", "cancelCreate");
            $tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
            $tpl->parseCurrentBlock();*/
            $tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_par"));
        } else {
            $tpl->setCurrentBlock("commands");
            $tpl->setVariable("BTN_NAME", "update");
            $tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
            $tpl->setVariable("BTN_CANCEL", "cancelUpdate");
            $tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
            $tpl->parseCurrentBlock();
            /*$tpl->setCurrentBlock("commands2");
            $tpl->setVariable("BTN_NAME", "update");
            $tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
            $tpl->setVariable("BTN_CANCEL", "cancelUpdate");
            $tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
            $tpl->parseCurrentBlock();*/
            $tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_par"));
        }

        // language and characteristic selection
        $s_char = $this->determineCharacteristic($a_insert);
        if (!$a_insert) {
            if (key($_POST["cmd"]) == "update") {
                $s_lang = $_POST["par_language"];
            } else {
                $s_lang = $this->content_obj->getLanguage();
            }
        } else {
            if (key($_POST["cmd"]) == "create_par") {
                $s_lang = $_POST["par_language"];
            } else {
                if ($_SESSION["il_text_lang_" . $_GET["ref_id"]] != "") {
                    $s_lang = $_SESSION["il_text_lang_" . $_GET["ref_id"]];
                } else {
                    $s_lang = $ilUser->getLanguage();
                }
            }
        }

        $this->insertStyleSelectionList($tpl, $s_char);
        //		$this->insertCharacteristicTable($tpl, $s_char);
        
        
        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        
        $tpl->setVariable("PAR_TA_NAME", "par_content");
        $tpl->setVariable("BB_MENU", $this->getBBMenu());
        $this->tpl->addJavascript("./Services/COPage/phpBB/3_0_5/editor.js");
        $this->tpl->addJavascript("./Services/COPage/js/paragraph_editing.js");
        $this->setStyle();

        $this->displayValidationError();

        $tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
        $tpl->setVariable("TXT_ANCHOR", $this->lng->txt("cont_anchor"));

        require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
        $lang = ilMDLanguageItem::_getLanguages();
        $select_lang = ilUtil::formSelect($s_lang, "par_language", $lang, false, true);
        $tpl->setVariable("SELECT_LANGUAGE", $select_lang);
        
        $tpl->setVariable("TXT_CHARACTERISTIC", $this->lng->txt("cont_characteristic"));
        //		$select_char = ilUtil::formSelect ($s_char,
        //			"par_characteristic",$this->chars,false,true);
        //		$tpl->setVariable("SELECT_CHARACTERISTIC", $select_char);

        if (key($_POST["cmd"]) == "update" || key($_POST["cmd"]) == "create_par") {
            $s_text = ilUtil::stripSlashes($_POST["par_content"], false);
            // prevent curly brackets from being swallowed up by template engine
            $s_text = str_replace("{", "&#123;", $s_text);
            $s_text = str_replace("}", "&#125;", $s_text);
        } elseif (!$a_insert) {
            $s_text = $this->content_obj->xml2output($this->content_obj->getText());
        }

        $tpl->setVariable("PAR_TA_CONTENT", $s_text);

        $tpl->parseCurrentBlock();
        
        $this->tpl->setContent($tpl->get());
        return $tpl->get();
    }

    /**
     * Determine current characteristic
     *
     * @param
     * @return
     */
    public function determineCharacteristic($a_insert = false)
    {
        // language and characteristic selection
        if (!$a_insert) {
            if (key($_POST["cmd"]) == "update") {
                $s_char = $_POST["par_characteristic"];
            } else {
                $s_char = $this->content_obj->getCharacteristic();
                if ($s_char == "") {
                    $s_char = "Standard";
                }
            }
        } else {
            if (key($_POST["cmd"]) == "create_par") {
                $s_char = $_POST["par_characteristic"];
            } else {
                $s_char = "Standard";

                // set characteristic of new paragraphs in list items to "List"
                $cont_obj = $this->pg_obj->getContentObject($this->getHierId());
                if (is_object($cont_obj)) {
                    if ($cont_obj->getType() == "li" ||
                        ($cont_obj->getType() == "par" && $cont_obj->getCharacteristic() == "List")) {
                        $s_char = "List";
                    }

                    if ($cont_obj->getType() == "td" ||
                        ($cont_obj->getType() == "par" && $cont_obj->getCharacteristic() == "TableContent")) {
                        $s_char = "TableContent";
                    }
                }
            }
        }
        return $s_char;
    }

    /**
     * Edit paragraph (Ajax mode, sends the content of the paragraph)
     */
    public function editJS()
    {
        $s_text = $this->content_obj->getText();
        $this->log->debug("step 1: " . substr($s_text, 0, 1000));

        //echo "\n<br><br>".htmlentities($s_text);
        $s_text = $this->content_obj->xml2output($s_text, true, false);
        $this->log->debug("step 2: " . substr($s_text, 0, 1000));

        //echo "\n<br><br>".htmlentities($s_text);
        $char = $this->determineCharacteristic(false);
        $s_text = ilPCParagraphGUI::xml2outputJS($s_text, $char, $this->content_obj->readPCId());
        $this->log->debug("step 3: " . substr($s_text, 0, 1000));

        //echo "\n<br><br>".htmlentities($s_text);
        $ids = "###" . $this->content_obj->readHierId() . ":" . $this->content_obj->readPCId() . "###" .
            $char . "###";
        echo $ids . $s_text;
        $this->log->debug("step 4: " . substr($ids . $s_text, 0, 1000));
        exit;
    }

    /**
     * Edit multiple paragraphs (Ajax mode, sends the content of the paragraphs)
     */
    public function editMultipleJS()
    {
        echo $this->content_obj->getParagraphSequenceContent($this->pg_obj);
        exit;
    }

    /**
     * Prepare content for js output
     */
    public static function xml2outputJS($s_text, $char, $a_pc_id)
    {
        //		$s_text = "<div class='ilc_text_block_".$char."' id='$a_pc_id'>".$s_text."</div>";
        $s_text = $s_text;
        // lists
        $s_text = str_replace(
            array("<SimpleBulletList>", "</SimpleBulletList>"),
            array("<ul class='ilc_list_u_BulletedList'>", "</ul>"),
            $s_text
        );
        $s_text = str_replace(
            array("<SimpleNumberedList>", "</SimpleNumberedList>"),
            array("<ol class='ilc_list_o_NumberedList'>", "</ol>"),
            $s_text
        );
        $s_text = str_replace(
            array("<SimpleListItem>", "</SimpleListItem>"),
            array("<li class='ilc_list_item_StandardListItem'>", "</li>"),
            $s_text
        );
        $s_text = str_replace(
            array("<SimpleListItem/>"),
            array("<li class='ilc_list_item_StandardListItem'></li>"),
            $s_text
        );
        //$s_text = str_replace("<SimpleBulletList><br />", "<SimpleBulletList>", $s_text);
        //$s_text = str_replace("<SimpleNumberedList><br />", "<SimpleNumberedList>", $s_text);
        //$s_text = str_replace("</SimpleListItem><br />", "</SimpleListItem>", $s_text);


        // spans
        include_once("./Services/COPage/classes/class.ilPageContentGUI.php");
        foreach (ilPageContentGUI::_getCommonBBButtons() as $bb => $cl) {
            if (!in_array($bb, array("code", "tex", "fn", "xln", "sub", "sup"))) {
                $s_text = str_replace(
                    "[" . $bb . "]",
                    '<span class="ilc_text_inline_' . $cl . '">',
                    $s_text
                );
                $s_text = str_replace(
                    "[/" . $bb . "]",
                    '</span>',
                    $s_text
                );
            }
        }

        // marked text spans
        $ws = "[ \t\r\f\v\n]*";
        while (preg_match("~\[(marked$ws(class$ws=$ws\"([^\"])*\")$ws)\]~i", $s_text, $found)) {
            $attribs = ilUtil::attribsToArray($found[2]);
            if (isset($attribs["class"])) {
                $s_text = str_replace("[" . $found[1] . "]", "<span class=\"ilc_text_inline_" . $attribs["class"] . "\">", $s_text);
            } else {
                $s_text = str_replace("[" . $found[1] . "]", "[error:marked" . $found[1] . "]", $s_text);
            }
        }
        $s_text = preg_replace('~\[\/marked\]~i', "</span>", $s_text);


        // code
        $s_text = str_replace(
            array("[code]", "[/code]"),
            array("<code>", "</code>"),
            $s_text
        );

        // sup
        $s_text = str_replace(
            array("[sup]", "[/sup]"),
            array('<sup class="ilc_sup_Sup">', "</sup>"),
            $s_text
        );

        // sub
        $s_text = str_replace(
            array("[sub]", "[/sub]"),
            array('<sub class="ilc_sub_Sub">', "</sub>"),
            $s_text
        );

        return $s_text;
    }


    /**
     * Save paragraph by JS call
     *
     * @param
     * @return
     */
    public function saveJS()
    {
        $ilCtrl = $this->ctrl;

        $this->log->debug("start");

        $this->updated = $this->content_obj->saveJS(
            $this->pg_obj,
            $_POST["ajaxform_content"],
            ilUtil::stripSlashes($_POST["ajaxform_char"]),
            ilUtil::stripSlashes($_POST["pc_id_str"])
        );

        $this->log->debug("ilPCParagraphGUI, saveJS: got updated value " . $this->updated);

        if ($_POST["quick_save"]) {
            if ($this->updated === true) {
                $a_pc_id_str = $this->content_obj->getLastSavedPcId($this->pg_obj, true);
                $this->log->debug("ilPCParagraphGUI, saveJS: echoing pc_id_str " . $a_pc_id_str . " (and exit)");
                echo $a_pc_id_str;
                exit;
            }
        }

        if ($this->updated !== true && is_array($this->updated)) {
            $this->outputError($this->updated);
        }

        $a_pc_id_str = $this->content_obj->getLastSavedPcId($this->pg_obj, true);

        $ilCtrl->setParameterByClass(
            $ilCtrl->getReturnClass($this),
            "updated_pc_id_str",
            urlencode($a_pc_id_str)
        );
        $this->log->debug("ilPCParagraphGUI, saveJS: redirecting to edit command of " . $ilCtrl->getReturnClass($this) . ".");
        $ilCtrl->redirectByClass($ilCtrl->getReturnClass($this), "edit", "", true);
    }

    /**
     * Output error
     *
     * @param array $a_err error array
     */
    public function outputError($a_err)
    {
        $err_str = "";
        foreach ($a_err as $err) {
            $err_str .= $err[1] . "<br />";
        }
        echo $err_str;
        $this->log->debug("ilPCParagraphGUI, outputError() and exit: " . substr($err_str, 0, 100));
        exit;
    }

    
    /**
     * Cancel
     */
    public function cancel()
    {
        $this->log->debug("ilPCParagraphGUI, cancel(): return to parent: jump" . $this->hier_id);
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * Insert characteristic table
    */
    public function insertCharacteristicTable($a_tpl, $a_seleted_value)
    {
        $i = 0;

        $chars = $this->getCharacteristics();

        if ($chars[$a_seleted_value] == "" && ($a_seleted_value != "")) {
            $chars = array_merge(
                array($a_seleted_value => $a_seleted_value),
                $chars
            );
        }

        foreach ($chars as $char => $char_lang) {
            $a_tpl->setCurrentBlock("characteristic_cell");
            $a_tpl->setVariable(
                "CHAR_HTML",
                '<div class="ilc_text_block_' . $char . '" style="margin-top:2px; margin-bottom:2px; position:static;">' . $char_lang . "</div>"
            );
            $a_tpl->setVariable("CHAR_VALUE", $char);
            if ($char == $a_seleted_value) {
                $a_tpl->setVariable(
                    "SELECTED",
                    ' checked="checked" '
                );
            }
            $a_tpl->parseCurrentBlock();
            if ((($i + 1) % 3) == 0) {	//
                $a_tpl->touchBlock("characteristic_row");
            }
            $i++;
        }
        $a_tpl->touchBlock("characteristic_table");
    }

    /**
     * Insert style selection list
     *
     * @param object $a_tpl
     * @param string $a_selected
     */
    public function insertStyleSelectionList($a_tpl, $a_selected)
    {
        $a_tpl->setVariable("ADV_SEL_STYLE", self::getStyleSelector(
            $a_selected,
            $this->getCharacteristics()
        ));
    }

    /**
     * Get style selector
     */
    public static function getStyleSelector($a_selected, $a_chars, $a_use_callback = false)
    {
        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setPullRight(false);
        $selection->setFormSelectMode(
            "par_characteristic",
            "",
            false,
            "",
            "",
            "",
            "",
            "",
            "",
            ""
        );
        $selection->setId("style_selection");
        $selection->setSelectionHeaderClass("ilEditSubmit ilTinyMenuDropDown");
        $selection->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $selection->setSelectedValue($a_selected);
        $selection->setUseImages(false);
        $selection->setOnClickMode(ilAdvancedSelectionListGUI::ON_ITEM_CLICK_FORM_SELECT);
        if ($a_use_callback) {
            $selection->setSelectCallback("ilCOPage.setParagraphClass");
        }
        
        $chars = $a_chars;
        $title_char = ($chars[$a_selected] != "")
            ? $chars[$a_selected]
            : $a_selected;
        $selection->setListTitle($title_char);

        if ($chars[$a_seleted] == "" && ($a_seleted != "")) {
            $chars = array_merge(
                array($a_seleted => $a_seleted),
                $chars
            );
        }

        foreach ($chars as $char => $char_lang) {
            $t = "text_block";
            $tag = "div";
            switch ($char) {
                case "Headline1": $t = "heading1"; $tag = "h1"; break;
                case "Headline2": $t = "heading2"; $tag = "h2"; break;
                case "Headline3": $t = "heading3"; $tag = "h3"; break;
            }
            $html = '<div class="ilCOPgEditStyleSelectionItem"><' . $tag . ' class="ilc_' . $t . '_' . $char . '" style="' . self::$style_selector_reset . '">' . $char_lang . "</" . $tag . "></div>";
            $selection->addItem(
                $char_lang,
                $char,
                "",
                "",
                $char,
                "",
                $html
            );
        }
        return $selection->getHTML();
    }
    
    /**
     * Get character style selector
     */
    public static function getCharStyleSelector($a_par_type, $a_use_callback = true, $a_style_id = 0)
    {
        global $DIC;

        $lng = $DIC->language();

        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setPullRight(false);
        $selection->setFormSelectMode(
            "char_characteristic",
            "",
            false,
            "",
            "",
            "",
            "",
            "",
            "",
            ""
        );
        $selection->setId("char_style_selection");
        $selection->setSelectionHeaderClass("ilEditSubmit");
        $selection->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        //$selection->setSelectedValue($a_selected);
        $selection->setUseImages(false);
        $selection->setOnClickMode(ilAdvancedSelectionListGUI::ON_ITEM_CLICK_NOP);
        if (is_string($a_use_callback)) {
            $selection->setSelectCallback($a_use_callback);
        } elseif ($a_use_callback === true) {
            $selection->setSelectCallback("ilCOPage.setCharacterClass");
        }

        //$chars = $a_chars;
        //$title_char = ($chars[$a_selected] != "")
        //	? $chars[$a_selected]
        //	: $a_selected;
        $selection->setListTitle("&nbsp;<i>A</i>");

        /*if ($chars[$a_seleted] == "" && ($a_seleted != ""))
        {
            $chars = array_merge(array($a_seleted => $a_seleted),
                $chars);
        }*/

        $chars = array(
            "Comment" => array("code" => "com", "txt" => $lng->txt("cont_char_style_com")),
            "Quotation" => array("code" => "quot", "txt" => $lng->txt("cont_char_style_quot")),
            "Accent" => array("code" => "acc", "txt" => $lng->txt("cont_char_style_acc")),
            "Code" => array("code" => "code", "txt" => $lng->txt("cont_char_style_code"))
            );

        foreach (ilPCParagraphGUI::_getTextCharacteristics($a_style_id) as $c) {
            if (!isset($chars[$c])) {
                $chars[$c] = array("code" => "", "txt" => $c);
            }
        }

        foreach ($chars as $key => $char) {
            if (ilPageEditorSettings::lookupSettingByParentType(
                $a_par_type,
                "active_" . $char["code"],
                true
            )) {
                $t = "text_inline";
                $tag = "span";
                switch ($key) {
                    case "Code": $tag = "code"; break;
                }
                $html = '<' . $tag . ' class="ilc_' . $t . '_' . $key . '" style="font-size:90%; margin-top:2px; margin-bottom:2px; position:static;">' . $char["txt"] . "</" . $tag . ">";
                
                // this next line is very important for IE. The real onclick event is on the surrounding <tr> of the
                // advanced selection list. But it is impossible to prevent the tr-event from removing the focus
                // on tiny withouth the following line, that receives the click event before and stops the faulty default
                // bevaviour of IE, see bug report #8723
                $html = '<a class="nostyle" style="display:block;" href="#" onclick="return false;">' . $html . "</a>";
                $selection->addItem(
                    $char["txt"],
                    $key,
                    "",
                    "",
                    $key,
                    "",
                    $html
                );
            }
        }
        return $selection->getHTML();
    }

    /**
    * Set Style
    */
    private function setStyle()
    {
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        
        if ($this->pg_obj->getParentType() == "gdf" ||
            $this->pg_obj->getParentType() == "lm") {
            if ($this->pg_obj->getParentType() != "gdf") {
                $this->tpl->setContentStylesheet(ilObjStyleSheet::getContentStylePath(
                    ilObjContentObject::_lookupStyleSheetId($this->pg_obj->getParentId())
                ));
            } else {
                $this->tpl->setContentStylesheet(ilObjStyleSheet::getContentStylePath(0));
            }
        } else {
            if ($this->pg_obj->getParentType() != "sahs") {
                //				$this->tpl->setContentStylesheet(ilObjStyleSheet::getContentStylePath(0));
            }
        }
    }
    
    /**
    * insert paragraph form
    */
    public function insert()
    {
        $this->log->debug("ilPCParagraphGUI, saveJS: got updated value " . $this->updated);
        return $this->edit(true);
    }
    
    /**
    * update paragraph in dom and update page in db
    */
    public function update()
    {
        $this->log->debug("ilPCParagraphGUI, update(): start");

        // set language and characteristic
        $this->content_obj->setLanguage($_POST["par_language"]);
        $this->content_obj->setCharacteristic($_POST["par_characteristic"]);

        $this->updated = $this->content_obj->setText(
            $this->content_obj->input2xml(
                $_POST["par_content"],
                $_POST["usedwsiwygeditor"]
            ),
            true
        );
        if ($this->updated !== true) {
            $this->edit();
            return;
        }

        $this->updated = $this->content_obj->updatePage($this->pg_obj);


        if ($this->updated === true) {
            $this->log->debug("ilPCParagraphGUI, update(): return to parent: jump" . $this->hier_id);
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->log->debug("ilPCParagraphGUI, update(): call edit.");
            $this->edit();
        }
    }
    

    /**
    * create new paragraph in dom and update page in db
    */
    public function create()
    {
        $this->log->debug("ilPCParagraphGUI, create(): start.");

        if ($_POST["ajaxform_hier_id"] != "") {
            return $this->createJS();
        }

        $this->content_obj = new ilPCParagraph($this->getPage());
        //echo "+".$this->pc_id."+";
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);

        $this->content_obj->setLanguage($_POST["par_language"]);
        $_SESSION["il_text_lang_" . $_GET["ref_id"]] = $_POST["par_language"];
        $this->content_obj->setCharacteristic($_POST["par_characteristic"]);

        $this->updated = $this->content_obj->setText(
            $this->content_obj->input2xml(
                $_POST["par_content"],
                $_POST["usedwsiwygeditor"]
            ),
            true
        );

        if ($this->updated !== true) {
            $this->insert();
            return;
        }
        $this->updated = $this->content_obj->updatePage($this->pg_obj);

        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }

    /**
     * Create paragraph per JS
     */
    public function createJS()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        $this->log->debug("ilPCParagraphGUI, createJS(): start");

        $this->content_obj = new ilPCParagraph($this->getPage());
        $this->updated = $this->content_obj->saveJS(
            $this->pg_obj,
            $_POST["ajaxform_content"],
            ilUtil::stripSlashes($_POST["ajaxform_char"]),
            ilUtil::stripSlashes($_POST["pc_id_str"]),
            $_POST["insert_at_id"]
        );
        if ($_POST["quick_save"]) {
            if ($this->updated) {
                $a_pc_id_str = $this->content_obj->getLastSavedPcId($this->pg_obj, true);
                echo $a_pc_id_str;
                $this->log->debug("ilPCParagraphGUI, createJS(): echo pc id and exit: " . $a_pc_id_str);
                exit;
            }
        }

        if ($this->updated !== true && is_array($this->updated)) {
            $this->outputError($this->updated);
        }

        // e.g. e.g. ###3:110dad8bad6df8620071a0a693a2d328###
        $a_pc_id_str = $this->content_obj->getLastSavedPcId($this->pg_obj, true);
        $ilCtrl->setParameterByClass(
            $ilCtrl->getReturnClass($this),
            "updated_pc_id_str",
            urlencode($a_pc_id_str)
        );
        $this->log->debug("ilPCParagraphGUI, createJS(): return to edit cmd of " . $ilCtrl->getReturnClass($this));

        $ilCtrl->redirectByClass($ilCtrl->getReturnClass($this), "edit", "", true);
    }

    /**
    * Insert Help
    */
    public function insertHelp($a_tpl)
    {
        $lng = $this->lng;
        
        $a_tpl->setCurrentBlock("help_item");
        $a_tpl->setVariable("TXT_HELP", "<b>" . $lng->txt("cont_syntax_help") . "</b>");
        $a_tpl->parseCurrentBlock();
        $a_tpl->setCurrentBlock("help_item");
        $a_tpl->setVariable("TXT_HELP", "* " . $lng->txt("cont_bullet_list"));
        $a_tpl->parseCurrentBlock();
        $a_tpl->setCurrentBlock("help_item");
        $a_tpl->setVariable("TXT_HELP", "# " . $lng->txt("cont_numbered_list"));
        $a_tpl->parseCurrentBlock();
        $a_tpl->setCurrentBlock("help_item");
        $a_tpl->setVariable("TXT_HELP", "=" . $lng->txt("cont_Headline1") . "=<br />" .
            "==" . $lng->txt("cont_Headline2") . "==<br />" .
            "===" . $lng->txt("cont_Headline3") . "===");
        $a_tpl->parseCurrentBlock();
        
        if ($this->getPageConfig()->getEnableWikiLinks()) {
            $a_tpl->setCurrentBlock("help_item");
            $a_tpl->setVariable("TXT_HELP", "[[" . $lng->txt("cont_wiki_page_link") . "]]");
            $a_tpl->parseCurrentBlock();
        }

        $a_tpl->setCurrentBlock("help");
        $a_tpl->parseCurrentBlock();
    }
}
