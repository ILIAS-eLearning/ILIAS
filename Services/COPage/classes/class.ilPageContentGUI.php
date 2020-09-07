<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* User Interface for Editing of Page Content Objects (Paragraphs, Tables, ...)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPageContentGUI
{
    /**
     * @var ilErrorHandling
     */
    protected $error;

    public $content_obj;
    public $tpl;
    public $lng;

    /**
     * @var ilCtrl
     */
    public $ctrl;
    public $pg_obj;
    public $hier_id;
    public $dom;
    public $updated;
    public $target_script;
    public $return_location;
    public $page_config = null;

    /**
     * @var ilLogger
     */
    protected $log;

    public static $style_selector_reset = "margin-top:2px; margin-bottom:2px; text-indent:0px; position:static; float:none; width: auto;";

    // common bb buttons (special ones are iln and wln)
    protected static $common_bb_buttons = array(
        "str" => "Strong", "emp" => "Emph", "imp" => "Important",
        "sup" => "Sup", "sub" => "Sub",
        "com" => "Comment",
        "quot" => "Quotation", "acc" => "Accent", "code" => "Code", "tex" => "Tex",
        "fn" => "Footnote", "xln" => "ExternalLink"
        );

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id = 0, $a_pc_id = "")
    {
        global $DIC;

        $this->error = $DIC["ilErr"];
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->log = ilLoggerFactory::getLogger('copg');

        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->pg_obj = $a_pg_obj;
        $this->ctrl = $ilCtrl;
        $this->content_obj = $a_content_obj;

        if ($a_hier_id !== 0) {
            $this->hier_id = $a_hier_id;
            $this->pc_id = $a_pc_id;
            //echo "-".$this->pc_id."-";
            $this->dom = $a_pg_obj->getDom();
        }
    }

    /**
     * Set content object
     *
     * @param object $a_val content object
     */
    public function setContentObject($a_val)
    {
        $this->content_obj = $a_val;
    }
    
    /**
     * Get content object
     *
     * @return object content object
     */
    public function getContentObject()
    {
        return $this->content_obj;
    }
    
    /**
     * Set page
     *
     * @param object $a_val page object
     */
    public function setPage($a_val)
    {
        $this->pg_obj = $a_val;
    }
    
    /**
     * Get page
     *
     * @return object page object
     */
    public function getPage()
    {
        return $this->pg_obj;
    }

    /**
     * Set Page Config
     *
     * @param	object	Page Config
     */
    public function setPageConfig($a_val)
    {
        $this->page_config = $a_val;
    }

    /**
     * Get Page Config
     *
     * @return	object	Page Config
     */
    public function getPageConfig()
    {
        return $this->page_config;
    }

    /**
    * Get common bb buttons
    */
    public static function _getCommonBBButtons()
    {
        return self::$common_bb_buttons;
    }

    // scorm2004-start
    /**
    * Set Style Id.
    *
    * @param	int	$a_styleid	Style Id
    */
    public function setStyleId($a_styleid)
    {
        $this->styleid = $a_styleid;
    }

    /**
    * Get Style Id.
    *
    * @return	int	Style Id
    */
    public function getStyleId()
    {
        return $this->styleid;
    }

    /**
    * Get style object
    */
    public function getStyle()
    {
        if ((!is_object($this->style) || $this->getStyleId() != $this->style->getId()) && $this->getStyleId() > 0) {
            if (ilObject::_lookupType($this->getStyleId()) == "sty") {
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $this->style = new ilObjStyleSheet($this->getStyleId());
            }
        }
        
        return $this->style;
    }
    
    /**
    * Get characteristics of current style
    */
    protected function getCharacteristicsOfCurrentStyle($a_type)
    {
        if ($this->getStyleId() > 0 &&
            ilObject::_lookupType($this->getStyleId()) == "sty") {
            include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            $style = new ilObjStyleSheet($this->getStyleId());
            $chars = array();
            if (!is_array($a_type)) {
                $a_type = array($a_type);
            }
            foreach ($a_type as $at) {
                $chars = array_merge($chars, $style->getCharacteristics($at, true));
            }
            $new_chars = array();
            if (is_array($chars)) {
                foreach ($chars as $char) {
                    if ($this->chars[$char] != "") {	// keep lang vars for standard chars
                        $new_chars[$char] = $this->chars[$char];
                    } else {
                        $new_chars[$char] = $char;
                    }
                    asort($new_chars);
                }
            }
            $this->setCharacteristics($new_chars);
        }
    }

    /**
    * Set Characteristics
    */
    public function setCharacteristics($a_chars)
    {
        $this->chars = $a_chars;
    }

    /**
    * Get characteristics
    */
    public function getCharacteristics()
    {
        return $this->chars ? $this->chars : array();
    }
    // scorm2004-end


    /**
    * get hierarchical id in dom object
    */
    public function getHierId()
    {
        return $this->hier_id;
    }

    /**
    * get hierarchical id in dom object
    */
    public function setHierId($a_hier_id)
    {
        $this->hier_id = $a_hier_id;
    }

    /**
    * Get the bb menu incl. script
    */
    public function getBBMenu($a_ta_name = "par_content")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        include_once("./Services/COPage/classes/class.ilPageEditorSettings.php");
        
        $btpl = new ilTemplate("tpl.bb_menu.html", true, true, "Services/COPage");

        // not nice, should be set by context per method
        if ($this->getPageConfig()->getEnableInternalLinks()) {
            $btpl->setCurrentBlock("bb_ilink_button");
            $btpl->setVariable(
                "BB_LINK_ILINK",
                $this->ctrl->getLinkTargetByClass("ilInternalLinkGUI", "showLinkHelp")
            );
            $btpl->parseCurrentBlock();
            
            // add int link parts
            include_once("./Services/Link/classes/class.ilInternalLinkGUI.php");
            $btpl->setCurrentBlock("int_link_prep");
            $btpl->setVariable("INT_LINK_PREP", ilInternalLinkGUI::getInitHTML(
                $ilCtrl->getLinkTargetByClass(
                    array("ilpageeditorgui", "ilinternallinkgui"),
                    "",
                    false,
                    true,
                    false
                ),
                true
            ));
            $btpl->parseCurrentBlock();
        }

        if ($this->getPageConfig()->getEnableKeywords()) {
            $btpl->touchBlock("bb_kw_button");
            $btpl->setVariable("TXT_KW", $this->lng->txt("cont_text_keyword"));
        }
        if ($this->pg_obj->getParentType() == "wpg") {
            $btpl->setCurrentBlock("bb_wikilink_button2");
            $btpl->setVariable("TXT_WIKI_BUTTON2", $lng->txt("obj_wiki"));
            $btpl->setVariable("WIKI_BUTTON2_URL", $ilCtrl->getLinkTargetByClass("ilwikipagegui", ""));
            $btpl->parseCurrentBlock();

            $btpl->setCurrentBlock("bb_wikilink_button");
            $btpl->setVariable("TXT_WLN2", $lng->txt("wiki_wiki_page"));
            $btpl->parseCurrentBlock();
        }
        $mathJaxSetting = new ilSetting("MathJax");
        $style = $this->getStyle();
        //echo URL_TO_LATEX;
        foreach (self::$common_bb_buttons as $c => $st) {
            if (ilPageEditorSettings::lookupSettingByParentType($this->pg_obj->getParentType(), "active_" . $c, true)) {
                if ($c != "tex" || $mathJaxSetting->get("enable") || defined("URL_TO_LATEX")) {
                    if (!in_array($c, array("acc", "com", "quot", "code"))) {
                        $btpl->touchBlock("bb_" . $c . "_button");
                        $btpl->setVariable("TXT_" . strtoupper($c), $this->lng->txt("cont_text_" . $c));
                        $lng->toJS("cont_text_" . $c);
                    }
                }
            }
        }
        
        if ($this->getPageConfig()->getEnableAnchors()) {
            $btpl->touchBlock("bb_anc_button");
            $btpl->setVariable("TXT_ANC", $lng->txt("cont_anchor") . ":");
            $lng->toJS("cont_anchor");
        }

        include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");
        $btpl->setVariable("CHAR_STYLE_SELECT", ilPCParagraphGUI::getCharStyleSelector($this->pg_obj->getParentType(), "il.COPageBB.setCharacterClass", $this->getStyleId()));
        
        // footnote
        //		$btpl->setVariable("TXT_FN", $this->lng->txt("cont_text_fn"));
        
        //		$btpl->setVariable("TXT_CODE", $this->lng->txt("cont_text_code"));
        $btpl->setVariable("TXT_ILN", $this->lng->txt("cont_text_iln"));
        $lng->toJS("cont_text_iln");
        //		$btpl->setVariable("TXT_XLN", $this->lng->txt("cont_text_xln"));
        //		$btpl->setVariable("TXT_TEX", $this->lng->txt("cont_text_tex"));
        $btpl->setVariable("TXT_BB_TIP", $this->lng->txt("cont_bb_tip"));
        $btpl->setVariable("TXT_WLN", $lng->txt("wiki_wiki_page"));
        $lng->toJS("wiki_wiki_page");
        
        $btpl->setVariable("PAR_TA_NAME", $a_ta_name);
        
        return $btpl->get();
    }

    /**
    * delete content element
    */
    public function delete()
    {
        $updated = $this->pg_obj->deleteContent($this->hier_id);
        if ($updated !== true) {
            $_SESSION["il_pg_error"] = $updated;
        } else {
            unset($_SESSION["il_pg_error"]);
        }
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * move content element after another element
    */
    public function moveAfter()
    {
        $ilErr = $this->error;

        // check if a target is selected
        if (!isset($_POST["target"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        // check if only one target is selected
        if (count($_POST["target"]) > 1) {
            $ilErr->raiseError($this->lng->txt("only_one_target"), $ilErr->MESSAGE);
        }

        $a_hid = explode(":", $_POST["target"][0]);
        //echo "-".$a_hid[0]."-".$a_hid[1]."-";

        // check if target is within source
        if ($this->hier_id == substr($a_hid[0], 0, strlen($this->hier_id))) {
            $ilErr->raiseError($this->lng->txt("cont_target_within_source"), $ilErr->MESSAGE);
        }

        // check whether target is allowed
        $curr_node = $this->pg_obj->getContentNode($a_hid[0], $a_hid[1]);
        if (is_object($curr_node) && $curr_node->node_name() == "FileItem") {
            $ilErr->raiseError($this->lng->txt("cont_operation_not_allowed"), $ilErr->MESSAGE);
        }

        // strip "c" "r" of table ids from hierarchical id
        $first_hier_character = substr($a_hid[0], 0, 1);
        if ($first_hier_character == "c" ||
            $first_hier_character == "r" ||
            $first_hier_character == "i") {
            $a_hid[0] = substr($a_hid[0], 1);
        }

        // move
        $updated = $this->pg_obj->moveContentAfter(
            $this->hier_id,
            $a_hid[0],
            $this->content_obj->getPcId(),
            $a_hid[1]
        );
        if ($updated !== true) {
            $_SESSION["il_pg_error"] = $updated;
        } else {
            unset($_SESSION["il_pg_error"]);
        }
        $this->log->debug("return to parent jump" . $this->hier_id);
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * move content element before another element
    */
    public function moveBefore()
    {
        $ilErr = $this->error;

        // check if a target is selected
        if (!isset($_POST["target"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        // check if target is within source
        if (count($_POST["target"]) > 1) {
            $ilErr->raiseError($this->lng->txt("only_one_target"), $ilErr->MESSAGE);
        }

        $a_hid = explode(":", $_POST["target"][0]);
        
        // check if target is within source
        if ($this->hier_id == substr($a_hid[0], 0, strlen($this->hier_id))) {
            $ilErr->raiseError($this->lng->txt("cont_target_within_source"), $ilErr->MESSAGE);
        }

        // check whether target is allowed
        $curr_node = $this->pg_obj->getContentNode($a_hid[0], $a_hid[1]);
        if (is_object($curr_node) && $curr_node->node_name() == "FileItem") {
            $ilErr->raiseError($this->lng->txt("cont_operation_not_allowed"), $ilErr->MESSAGE);
        }

        // strip "c" "r" of table ids from hierarchical id
        $first_hier_character = substr($a_hid[0], 0, 1);
        if ($first_hier_character == "c" ||
            $first_hier_character == "r" ||
            $first_hier_character == "i") {
            $a_hid[0] = substr($a_hid[0], 1);
        }

        // move
        $updated = $this->pg_obj->moveContentBefore(
            $this->hier_id,
            $a_hid[0],
            $this->content_obj->getPcId(),
            $a_hid[1]
        );
        if ($updated !== true) {
            $_SESSION["il_pg_error"] = $updated;
        } else {
            unset($_SESSION["il_pg_error"]);
        }
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
    
    
    /**
    * split page to new page at specified position
    */
    public function splitPage()
    {
        $ilErr = $this->error;
        
        if ($this->pg_obj->getParentType() != "lm") {
            $ilErr->raiseError("Split method called for wrong parent type (" .
            $this->pg_obj->getParentType() . ")", $ilErr->FATAL);
        } else {
            $lm_page = ilLMPageObject::_splitPage(
                $this->pg_obj->getId(),
                $this->pg_obj->getParentType(),
                $this->hier_id
            );
                
            // jump to new page
            $this->ctrl->setParameterByClass("illmpageobjectgui", "obj_id", $lm_page->getId());
            $this->ctrl->redirectByClass("illmpageobjectgui", "edit");
        }
        
        $this->ctrl->returnToParent($this, "jump" . ($this->hier_id - 1));
    }

    /**
    * split page to next page at specified position
    */
    public function splitPageNext()
    {
        $ilErr = $this->error;
        
        if ($this->pg_obj->getParentType() != "lm") {
            $ilErr->raiseError("Split method called for wrong parent type (" .
            $this->pg_obj->getParentType() . ")", $ilErr->FATAL);
        } else {
            $succ_id = ilLMPageObject::_splitPageNext(
                $this->pg_obj->getId(),
                $this->pg_obj->getParentType(),
                $this->hier_id
            );
            
            // jump to successor page
            if ($succ_id > 0) {
                $this->ctrl->setParameterByClass("illmpageobjectgui", "obj_id", $succ_id);
                $this->ctrl->redirectByClass("illmpageobjectgui", "edit");
            }
        }
        $this->ctrl->returnToParent($this, "jump" . ($this->hier_id - 1));
    }

    /**
    * display validation errors
    */
    public function displayValidationError()
    {
        if (is_array($this->updated)) {
            $error_str = "<b>Error(s):</b><br>";
            foreach ($this->updated as $error) {
                $err_mess = implode($error, " - ");
                if (!is_int(strpos($err_mess, ":0:"))) {
                    $error_str .= htmlentities($err_mess) . "<br />";
                }
            }
            ilUtil::sendFailure($error_str);
        } elseif ($this->updated != "" && $this->updated !== true) {
            ilUtil::sendFailure("<b>Error(s):</b><br />" .
                $this->updated);
        }
    }
    
    /**
    * cancel creating page content
    */
    public function cancelCreate()
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * cancel update
    */
    public function cancelUpdate()
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
     * Cancel
     */
    public function cancel()
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
     * gui function
     * set enabled if is not enabled and vice versa
     */
    public function deactivate()
    {
        $obj = &$this->content_obj;
        
        if ($obj->isEnabled()) {
            $obj->disable();
        } else {
            $obj->enable();
        }
        
        $updated = $this->pg_obj->update($this->hier_id);
        if ($updated !== true) {
            $_SESSION["il_pg_error"] = $updated;
        } else {
            unset($_SESSION["il_pg_error"]);
        }
    
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
     * Cut single element
     */
    public function cut()
    {
        $lng = $this->lng;
        
        $obj = $this->content_obj;
        
        $updated = $this->pg_obj->cutContents(array($this->hier_id . ":" . $this->pc_id));
        if ($updated !== true) {
            $_SESSION["il_pg_error"] = $updated;
        } else {
            unset($_SESSION["il_pg_error"]);
        }
    
        //ilUtil::sendSuccess($lng->txt("cont_sel_el_cut_use_paste"), true);
        $this->log->debug("return to parent jump" . $this->hier_id);
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
     * Copy single element
     */
    public function copy()
    {
        $lng = $this->lng;
        
        $obj = $this->content_obj;
        
        //ilUtil::sendSuccess($lng->txt("cont_sel_el_copied_use_paste"), true);
        $this->pg_obj->copyContents(array($this->hier_id . ":" . $this->pc_id));
  
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }


    /**
    * Get table templates
    */
    public function getTemplateOptions($a_type)
    {
        $style = $this->getStyle();

        if (is_object($style)) {
            $ts = $style->getTemplates($a_type);
            $options = array();
            foreach ($ts as $t) {
                $options["t:" . $t["id"] . ":" . $t["name"]] = $t["name"];
            }
            return $options;
        }
        return array();
    }
}
