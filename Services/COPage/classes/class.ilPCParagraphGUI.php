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

use ILIAS\Style;

/**
 * Class ilPCParagraphGUI
 * User Interface for Paragraph Editing
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCParagraphGUI extends ilPageContentGUI
{
    protected ilObjUser $user;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
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
    public static function _getStandardCharacteristics(): array
    {
        global $DIC;

        $lng = $DIC->language();

        return array("Standard" => $lng->txt("cont_standard"),
            "Headline1" => $lng->txt("cont_Headline1"),
            "Headline2" => $lng->txt("cont_Headline2"),
            "Headline3" => $lng->txt("cont_Headline3"),
            "Book" => $lng->txt("cont_Book"),
            "Numbers" => $lng->txt("cont_Numbers"),
            "Verse" => $lng->txt("cont_Verse"),
            "List" => $lng->txt("cont_List"),
            "TableContent" => $lng->txt("cont_TableContent")
        );
    }

    /**
     * Get standard characteristics
     */
    public static function _getStandardTextCharacteristics(): array
    {
        return ["Mnemonic", "Attention"];
    }

    /**
     * Get characteristics
     */
    public static function _getCharacteristics(int $a_style_id): array
    {
        global $DIC;
        $request = $DIC->copage()->internal()
            ->gui()
            ->pc()
            ->editRequest();
        $requested_ref_id = $request->getRefId();

        $service = $DIC->contentStyle()->internal();

        $st_chars = ilPCParagraphGUI::_getStandardCharacteristics();
        $chars = ilPCParagraphGUI::_getStandardCharacteristics();
        if ($a_style_id > 0 &&
            ilObject::_lookupType($a_style_id) == "sty") {
            $access_manager = $service->domain()->access(
                $requested_ref_id,
                $DIC->user()->getId()
            );
            $char_manager = $service->domain()->characteristic(
                $a_style_id,
                $access_manager
            );

            $chars = $char_manager->getByTypes(
                ["text_block", "heading1", "heading2", "heading3"],
                false,
                false
            );
            $new_chars = array();
            foreach ($chars as $char) {
                if (($st_chars[$char->getCharacteristic()] ?? "") != "") {	// keep lang vars for standard chars
                    $title = $char_manager->getPresentationTitle(
                        $char->getType(),
                        $char->getCharacteristic()
                    );
                    if ($title == "") {
                        $title = $st_chars[$char->getCharacteristic()];
                    }
                    $new_chars[$char->getCharacteristic()] = $title;
                } else {
                    $new_chars[$char->getCharacteristic()] = $char_manager->getPresentationTitle(
                        $char->getType(),
                        $char->getCharacteristic()
                    );
                }
            }
            $chars = $new_chars;
        }

        return $chars;
    }

    /**
     * Get text characteristics
     */
    public static function _getTextCharacteristics(
        int $a_style_id,
        bool $a_include_core = false
    ): array {
        $chars = array();

        if ($a_style_id > 0 &&
            ilObject::_lookupType($a_style_id) == "sty") {
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
     * @return mixed
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
     * Determine current characteristic
     */
    public function determineCharacteristic(bool $a_insert = false): string
    {
        $cmd = $this->ctrl->getCmd();
        // language and characteristic selection
        if (!$a_insert) {
            if ($cmd == "update") {
                $s_char = $this->request->getString("par_characteristic");
            } else {
                $s_char = $this->content_obj->getCharacteristic();
                if ($s_char == "") {
                    $s_char = "Standard";
                }
            }
        } else {
            if ($cmd == "create_par") {
                $s_char = $this->request->getString("par_characteristic");
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
    public function editJS(): void
    {
        $s_text = $this->content_obj->getText();
        $this->log->debug("step 1: " . substr($s_text, 0, 1000));

        //echo "\n<br><br>".htmlentities($s_text);
        $s_text = $this->content_obj->xml2output($s_text, true, false);
        $this->log->debug("step 2: " . substr($s_text, 0, 1000));

        //echo "\n<br><br>".htmlentities($s_text);
        $char = $this->determineCharacteristic(false);
        $s_text = ilPCParagraphGUI::xml2outputJS($s_text);
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
    public function editMultipleJS(): void
    {
        echo $this->content_obj->getParagraphSequenceContent($this->pg_obj);
        exit;
    }

    /**
     * Prepare content for js output
     */
    public static function xml2outputJS(string $s_text): string
    {
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

        // spans
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
            $attribs = ilPCParagraph::attribsToArray($found[2]);
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
     * Output error
     */
    public function outputError(array $a_err): void
    {
        $err_str = "";
        foreach ($a_err as $err) {
            $err_str .= $err[1] . "<br />";
        }
        echo $err_str;
        $this->log->debug("ilPCParagraphGUI, outputError() and exit: " . substr($err_str, 0, 100));
        exit;
    }

    public function cancel(): void
    {
        $this->log->debug("ilPCParagraphGUI, cancel(): return to parent: jump" . $this->hier_id);
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
     * Insert characteristic table
     */
    public function insertCharacteristicTable(
        ilTemplate $a_tpl,
        string $a_seleted_value
    ): void {
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
     */
    public function insertStyleSelectionList(
        ilTemplate $a_tpl,
        string $a_selected
    ): void {
        $a_tpl->setVariable("ADV_SEL_STYLE", self::getStyleSelector(
            $a_selected,
            $this->getCharacteristics()
        ));
    }

    /**
     * Get style selector
     */
    public static function getStyleSelector(
        string $a_selected,
        array $a_chars,
        bool $a_use_callback = false
    ): string {
        $a_seleted = "";
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
                case "Headline1": $t = "heading1";
                    $tag = "h1";
                    break;
                case "Headline2": $t = "heading2";
                    $tag = "h2";
                    break;
                case "Headline3": $t = "heading3";
                    $tag = "h3";
                    break;
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
    public static function getCharStyleSelector(
        string $a_par_type,
        bool $a_use_callback = true,
        int $a_style_id = 0
    ): string {
        global $DIC;

        $lng = $DIC->language();

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
        $selection->setSelectCallback("ilCOPage.setCharacterClass");

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
                    case "Code": $tag = "code";
                        break;
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

    private function setStyle(): void
    {
        if ($this->pg_obj->getParentType() == "gdf" ||
            $this->pg_obj->getParentType() == "lm") {
            if ($this->pg_obj->getParentType() != "gdf") {
                $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(
                    ilObjContentObject::_lookupStyleSheetId($this->pg_obj->getParentId())
                ));
            } else {
                $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
            }
        }
    }

    /**
     * insert paragraph form
     */
    public function insert(): string
    {
        $this->log->debug("ilPCParagraphGUI, saveJS: got updated value " . $this->updated);
        return $this->edit(true);
    }

    /**
     * update paragraph in dom and update page in db
     */
    public function update(): void
    {
        $this->log->debug("ilPCParagraphGUI, update(): start");

        // set language and characteristic
        $this->content_obj->setLanguage(
            $this->request->getString("par_language")
        );
        $this->content_obj->setCharacteristic(
            $this->request->getString("par_characteristic")
        );

        $this->updated = $this->content_obj->setText(
            $this->content_obj->input2xml(
                $this->request->getRaw("par_content"),
                $this->request->getString("usedwsiwygeditor")
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
    public function create(): void
    {
        $this->log->debug("ilPCParagraphGUI, create(): start.");

        if ($this->request->getString("ajaxform_hier_id") != "") {
            $this->createJS();
            return;
        }

        $this->content_obj = new ilPCParagraph($this->getPage());
        //echo "+".$this->pc_id."+";
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);

        $this->content_obj->setLanguage(
            $this->request->getString("par_language")
        );
        $this->setCurrentTextLang($this->request->getString("par_language"));
        $this->content_obj->setCharacteristic(
            $this->request->getString("par_characteristic")
        );

        $this->updated = $this->content_obj->setText(
            $this->content_obj->input2xml(
                $this->request->getRaw("par_content"),
                $this->request->getString("usedwsiwygeditor")
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
     * Insert Help
     */
    public function insertHelp(ilTemplate $a_tpl): void
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
