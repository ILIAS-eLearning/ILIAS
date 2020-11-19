<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/RTE/classes/class.ilRTE.php';

/**
* Tiny MCE editor class
*
* This class provides access methods for Tiny MCE
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.ilTinyMCE.php
*/
class ilTinyMCE extends ilRTE
{
    protected $mode = 'textareas';
    protected $version = ''; // set default version here
    protected $vd = ''; // version directory suffix

    /**
     * @var bool
     */
    protected $styleselect = false;

    /**
     * A flag whether the "edit image" context menu item should appear or not
     * @var bool
     */
    protected $remove_img_context_menu_item = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($a_version = '')
    {
        if (!$a_version) {
            $a_version = '3.5.11';
        }

        parent::__construct($a_version);

        switch ($a_version) {
            case '3.4.7':
            case '3.5.11':
                $this->version = $a_version;
                $this->vd = '_' . str_replace('.', '_', $a_version);
                break;

            default:
                // unknown/unsupported version?
                break;
        }

        $this->plugins = array(
            'xhtmlxtras',
            'style',
            'layer',
            'table',
            'save',
            'advhr',
            'advlink',
            'emotions',
            'iespell',
            'insertdatetime',
            'preview',
            'searchreplace',
            'print',
            'contextmenu',
            'paste',
            'directionality',
            'fullscreen',
            'nonbreaking',
            'noneditable',
            'style'
        );

        $this->setStyleSelect(false);
        $this->addInternalTinyMCEImageManager();
    }

    /**
     *
     */
    protected function addInternalTinyMCEImageManager()
    {
        if (!$this->client_init->readVariable('tinymce', 'use_advanced_img_mng')) {
            parent::addPlugin('ilimgupload');
            parent::addButton('ilimgupload');
            parent::removePlugin('ibrowser');
            parent::removePlugin('image');

            $this->disableButtons(array(
                'ibrowser',
                'image'
            ));

            $this->setRemoveImgContextMenuItem(true);
        } else {
            parent::addPlugin('ibrowser');
            parent::removePlugin('ilimgupload');
            $this->disableButtons('ilimgupload');

            $this->setRemoveImgContextMenuItem(false);
        }
    }

    /**
     * @param array $tags
     */
    protected function handleImagePluginsBeforeRendering(array $tags)
    {
        if (!in_array('img', $tags)) {
            $this->setRemoveImgContextMenuItem(true);
            parent::removePlugin('ilimgupload');
            parent::removePlugin('ibrowser');
            parent::removePlugin('image');
            $this->disableButtons(array(
                'ibrowser',
                'image',
                'ilimgupload'
            ));
        }
    }
    
    /**
     *
     */
    protected function handleIliasImageManagerAdded()
    {
        $this->addInternalTinyMCEImageManager();
    }

    protected function handleIliasImageManagerRemoved()
    {
        if (!$this->client_init->readVariable('tinymce', 'use_advanced_img_mng')) {
            parent::removePlugin('ilimgupload');
            $this->disableButtons('ilimgupload');
        } else {
            parent::removePlugin('ibrowser');
            $this->disableButtons('ibrowser');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addPlugin($a_plugin_name)
    {
        if (self::ILIAS_IMG_MANAGER_PLUGIN == $a_plugin_name) {
            $this->handleIliasImageManagerAdded();
        } else {
            parent::addPlugin($a_plugin_name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removePlugin($a_plugin_name)
    {
        if (self::ILIAS_IMG_MANAGER_PLUGIN == $a_plugin_name) {
            $this->handleIliasImageManagerRemoved();
        } else {
            parent::removePlugin($a_plugin_name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addRTESupport($obj_id, $obj_type, $a_module = "", $allowFormElements = false, $cfg_template = null, $hide_switch = false)
    {
        if ($this->browser->isMobile()) {
            ilObjAdvancedEditing::_setRichTextEditorUserState(0);
        } else {
            ilObjAdvancedEditing::_setRichTextEditorUserState(1);
        }

        include_once "./Services/UICore/classes/class.ilTemplate.php";
        if ((ilObjAdvancedEditing::_getRichTextEditorUserState() != 0) && (strcmp(ilObjAdvancedEditing::_getRichTextEditor(), "0") != 0)) {
            $tpl = new ilTemplate(($cfg_template === null ? "tpl.tinymce.html" : $cfg_template), true, true, "Services/RTE");
            $this->handleImgContextMenuItem($tpl);
            $tags = ilObjAdvancedEditing::_getUsedHTMLTags($a_module);
            $this->handleImagePluginsBeforeRendering($tags);
            if ($allowFormElements) {
                $tpl->touchBlock("formelements");
            }
            if ($this->getInitialWidth() !== null && $tpl->blockExists('initial_width')) {
                $tpl->setCurrentBlock("initial_width");
                $tpl->setVariable('INITIAL_WIDTH', $this->getInitialWidth());
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("tinymce");
            $tpl->setVariable("JAVASCRIPT_LOCATION", "./Services/RTE/tiny_mce" . $this->vd . "/tiny_mce.js");
            include_once "./Services/Object/classes/class.ilObject.php";
            $tpl->setVariable("OBJ_ID", $obj_id);
            $tpl->setVariable("OBJ_TYPE", $obj_type);
            $tpl->setVariable("CLIENT_ID", CLIENT_ID);
            $tpl->setVariable("SESSION_ID", $_COOKIE[session_name()]);
            $tpl->setVariable("BLOCKFORMATS", $this->_buildAdvancedBlockformatsFromHTMLTags($tags));
            $tpl->setVariable("VALID_ELEMENTS", $this->_getValidElementsFromHTMLTags($tags));

            $buttons_1 = $this->_buildAdvancedButtonsFromHTMLTags(1, $tags);
            $buttons_2 = $this->_buildAdvancedButtonsFromHTMLTags(2, $tags)
                       . ',' . $this->_buildAdvancedTableButtonsFromHTMLTags($tags)
                       . ($this->getStyleSelect() ? ',styleselect' : '');
            $buttons_3 = $this->_buildAdvancedButtonsFromHTMLTags(3, $tags);
            $tpl->setVariable('BUTTONS_1', self::removeRedundantSeparators($buttons_1));
            $tpl->setVariable('BUTTONS_2', self::removeRedundantSeparators($buttons_2));
            $tpl->setVariable('BUTTONS_3', self::removeRedundantSeparators($buttons_3));
            
            $tpl->setVariable("ADDITIONAL_PLUGINS", join(",", $this->plugins));
            include_once "./Services/Utilities/classes/class.ilUtil.php";
            //$tpl->setVariable("STYLESHEET_LOCATION", $this->getContentCSS());
            $tpl->setVariable("STYLESHEET_LOCATION", ilUtil::getNewContentStyleSheetLocation() . "," . ilUtil::getStyleSheetLocation("output", "delos.css"));
            $tpl->setVariable("LANG", $this->_getEditorLanguage());

            if ($this->getRTERootBlockElement() !== null) {
                $tpl->setVariable('FORCED_ROOT_BLOCK', $this->getRTERootBlockElement());
            }
            
            $tpl->parseCurrentBlock();
            
            $this->tpl->setVariable("CONTENT_BLOCK", $tpl->get());
        }
    }

    /**
     * @param ilTemplate $tpl
     */
    protected function handleImgContextMenuItem(ilTemplate $tpl)
    {
        if ($this->getRemoveImgContextMenuItem() && $tpl->blockExists('remove_img_context_menu_item')) {
            $tpl->touchBlock('remove_img_context_menu_item');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomRTESupport($obj_id, $obj_type, array $tags)
    {
        $this->handleImagePluginsBeforeRendering($tags);
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $tpl = new ilTemplate("tpl.tinymce.html", true, true, "Services/RTE");
        $this->handleImgContextMenuItem($tpl);
        $tpl->setCurrentBlock("tinymce");
        $tpl->setVariable("JAVASCRIPT_LOCATION", "./Services/RTE/tiny_mce" . $this->vd . "/tiny_mce.js");
        include_once "./Services/Object/classes/class.ilObject.php";
        $tpl->setVariable("OBJ_ID", $obj_id);
        $tpl->setVariable("OBJ_TYPE", $obj_type);
        $tpl->setVariable("CLIENT_ID", CLIENT_ID);
        $tpl->setVariable("SESSION_ID", $_COOKIE[session_name()]);
        $tpl->setVariable("BLOCKFORMATS", $this->_buildAdvancedBlockformatsFromHTMLTags($tags));
        $tpl->setVariable("VALID_ELEMENTS", $this->_getValidElementsFromHTMLTags($tags));
        
        $this->disableButtons('charmap');
        $buttons_1 = $this->_buildAdvancedButtonsFromHTMLTags(1, $tags);
        $buttons_2 = $this->_buildAdvancedButtonsFromHTMLTags(2, $tags)
                   . ',' . $this->_buildAdvancedTableButtonsFromHTMLTags($tags)
                   . ($this->getStyleSelect() ? ',styleselect' : '');
        $buttons_3 = $this->_buildAdvancedButtonsFromHTMLTags(3, $tags);
        $tpl->setVariable('BUTTONS_1', self::removeRedundantSeparators($buttons_1));
        $tpl->setVariable('BUTTONS_2', self::removeRedundantSeparators($buttons_2));
        $tpl->setVariable('BUTTONS_3', self::removeRedundantSeparators($buttons_3));
        
        $tpl->setVariable("ADDITIONAL_PLUGINS", join(",", $this->plugins));
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        //$tpl->setVariable("STYLESHEET_LOCATION", $this->getContentCSS());
        $tpl->setVariable("STYLESHEET_LOCATION", ilUtil::getNewContentStyleSheetLocation());
        $tpl->setVariable("LANG", $this->_getEditorLanguage());
        
        if ($this->getRTERootBlockElement() !== null) {
            $tpl->setVariable('FORCED_ROOT_BLOCK', $this->getRTERootBlockElement());
        }
        
        $tpl->parseCurrentBlock();
        $this->tpl->setVariable("CONTENT_BLOCK", $tpl->get());
    }

    /**
     * {@inheritdoc}
     */
    public function addUserTextEditor($editor_selector)
    {
        $validtags = array("strong","em","p", "br", "div", "span");
        $buttontags = array("strong","em");
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.usereditor.html", true, true, "Services/RTE");
        $this->handleImgContextMenuItem($template);
        $template->setCurrentBlock("tinymce");
        $template->setVariable("JAVASCRIPT_LOCATION", "./Services/RTE/tiny_mce" . $this->vd . "/tiny_mce.js");
        include_once "./Services/Object/classes/class.ilObject.php";
        $template->setVariable("SELECTOR", $editor_selector);
        $template->setVariable("BLOCKFORMATS", "");
        $template->setVariable("VALID_ELEMENTS", $this->_getValidElementsFromHTMLTags($validtags));
        if ($this->getStyleSelect()) {
            $template->setVariable("STYLE_SELECT", ",styleselect");
        }
        $template->setVariable("BUTTONS", $this->getButtonsForUserTextEditor($buttontags) . ",backcolor,removeformat");
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        //$template->setVariable("STYLESHEET_LOCATION", $this->getContentCSS());
        $template->setVariable("STYLESHEET_LOCATION", ilUtil::getNewContentStyleSheetLocation() . "," . ilUtil::getStyleSheetLocation("output", "delos.css"));
        $template->setVariable("LANG", $this->_getEditorLanguage());
        $template->parseCurrentBlock();
        $this->tpl->setCurrentBlock("HeadContent");
        $this->tpl->setVariable("CONTENT_BLOCK", $template->get());
        $this->tpl->parseCurrentBlock();
    }

    /**
     * @param array $buttontags
     * @return string
     */
    protected function getButtonsForUserTextEditor(array $buttontags)
    {
        $btns = $this->_buildButtonsFromHTMLTags($buttontags);

        $btns = explode(',', $btns);
        
        $btns[] = 'undo';
        $btns[] = 'redo';
        
        return implode(',', $btns);
    }
    
    /**
    * Set Enable Style Selecttion.
    *
    * @public boolean	$a_styleselect	Enable Style Selecttion
    */
    protected function setStyleSelect($a_styleselect)
    {
        $this->styleselect = $a_styleselect;
    }

    /**
    * Get Enable Style Selecttion.
    *
    * @return	boolean	Enable Style Selecttion
    */
    public function getStyleSelect()
    {
        return $this->styleselect;
    }

    /**
     * @param array $a_html_tags
     * @return string
     */
    public function _buildAdvancedBlockformatsFromHTMLTags(array $a_html_tags)
    {
        $blockformats = array();
        
        if (in_array("p", $a_html_tags)) {
            array_push($blockformats, "p");
        }
        if (in_array("div", $a_html_tags)) {
            array_push($blockformats, "div");
        }
        if (in_array("pre", $a_html_tags)) {
            array_push($blockformats, "pre");
        }
        if (in_array("code", $a_html_tags)) {
            array_push($blockformats, "code");
        }
        if (in_array("h1", $a_html_tags)) {
            array_push($blockformats, "h1");
        }
        if (in_array("h2", $a_html_tags)) {
            array_push($blockformats, "h2");
        }
        if (in_array("h3", $a_html_tags)) {
            array_push($blockformats, "h3");
        }
        if (in_array("h4", $a_html_tags)) {
            array_push($blockformats, "h4");
        }
        if (in_array("h5", $a_html_tags)) {
            array_push($blockformats, "h5");
        }
        if (in_array("h6", $a_html_tags)) {
            array_push($blockformats, "h6");
        }
        if (count($blockformats)) {
            return join(",", $blockformats);
        } else {
            return "";
        }
    }

    /**
     * @param int $a_buttons_section
     * @param array $a_html_tags
     * @return string
     */
    public function _buildAdvancedButtonsFromHTMLTags($a_buttons_section, array $a_html_tags)
    {
        $theme_advanced_buttons = array();
        
        if ($a_buttons_section == 1) {
            if (in_array("strong", $a_html_tags)) {
                array_push($theme_advanced_buttons, "bold");
            }
            if (in_array("em", $a_html_tags)) {
                array_push($theme_advanced_buttons, "italic");
            }
            if (in_array("u", $a_html_tags)) {
                array_push($theme_advanced_buttons, "underline");
            }
            if (in_array("strike", $a_html_tags)) {
                array_push($theme_advanced_buttons, "strikethrough");
            }
            if (count($theme_advanced_buttons)) {
                array_push($theme_advanced_buttons, "separator");
            }
            if (in_array("p", $a_html_tags)) {
                array_push($theme_advanced_buttons, "justifyleft");
                array_push($theme_advanced_buttons, "justifycenter");
                array_push($theme_advanced_buttons, "justifyright");
                array_push($theme_advanced_buttons, "justifyfull");
                array_push($theme_advanced_buttons, "separator");
            }
            if (strlen(ilTinyMCE::_buildAdvancedBlockformatsFromHTMLTags($a_html_tags))) {
                array_push($theme_advanced_buttons, "formatselect");
            }
            if (in_array("hr", $a_html_tags)) {
                array_push($theme_advanced_buttons, "hr");
            }
            array_push($theme_advanced_buttons, "removeformat");
            array_push($theme_advanced_buttons, "separator");
            if (in_array("sub", $a_html_tags)) {
                array_push($theme_advanced_buttons, "sub");
            }
            if (in_array("sup", $a_html_tags)) {
                array_push($theme_advanced_buttons, "sup");
            }
            if (in_array("font", $a_html_tags)) {
                array_push($theme_advanced_buttons, "fontselect");
                array_push($theme_advanced_buttons, "fontsizeselect");
            }
            array_push($theme_advanced_buttons, "charmap");
            if ((in_array("ol", $a_html_tags)) && (in_array("li", $a_html_tags))) {
                array_push($theme_advanced_buttons, "bullist");
            }
            if ((in_array("ul", $a_html_tags)) && (in_array("li", $a_html_tags))) {
                array_push($theme_advanced_buttons, "numlist");
            }
            array_push($theme_advanced_buttons, "separator");
            if (in_array("cite", $a_html_tags)) {
                array_push($theme_advanced_buttons, "cite");
            }
            if (in_array("abbr", $a_html_tags)) {
                array_push($theme_advanced_buttons, "abbr");
            }
            if (in_array("acronym", $a_html_tags)) {
                array_push($theme_advanced_buttons, "acronym");
            }
            if (in_array("del", $a_html_tags)) {
                array_push($theme_advanced_buttons, "del");
            }
            if (in_array("ins", $a_html_tags)) {
                array_push($theme_advanced_buttons, "ins");
            }
            if (in_array("blockquote", $a_html_tags)) {
                array_push($theme_advanced_buttons, "indent");
                array_push($theme_advanced_buttons, "outdent");
            }
            if (in_array("img", $a_html_tags)) {
                //array_push($theme_advanced_buttons, "advimage");
                array_push($theme_advanced_buttons, "image");
                array_push($theme_advanced_buttons, "ibrowser");
                array_push($theme_advanced_buttons, "ilimgupload");
            }
            if (in_array("a", $a_html_tags)) {
                array_push($theme_advanced_buttons, "link");
                array_push($theme_advanced_buttons, "unlink");
                array_push($theme_advanced_buttons, "anchor");
            }
            array_push($theme_advanced_buttons, "separator");
            array_push($theme_advanced_buttons, "undo");
            array_push($theme_advanced_buttons, "redo");
            
            if (is_array($this->buttons) && count($this->buttons)) {
                array_push($theme_advanced_buttons, "separator");
                foreach ($this->buttons as $button) {
                    array_push($theme_advanced_buttons, $button);
                }
            }
            
            array_push($theme_advanced_buttons, "code");
            array_push($theme_advanced_buttons, "fullscreen");
            
            // Changed in elba2 branch, adopted change for 4.2.x due to manits bug #8147
            array_push($theme_advanced_buttons, "pasteword");
        } elseif ($a_buttons_section == 2) {
            array_push($theme_advanced_buttons, "cut");
            array_push($theme_advanced_buttons, "copy");
            array_push($theme_advanced_buttons, "paste");
            array_push($theme_advanced_buttons, "pastetext");
        // Changed in elba2 branch, adopted change for 4.2.x due to manits bug #8147
            //array_push($theme_advanced_buttons, "pasteword");
        } elseif ($a_buttons_section == 3) {
        }
        
        $remove_buttons = $this->getDisabledButtons();
        if (is_array($remove_buttons)) {
            foreach ($remove_buttons as $buttontext) {
                if (($res = array_search($buttontext, $theme_advanced_buttons)) !== false) {
                    unset($theme_advanced_buttons[$res]);
                }
            }
        }
        
        return join(",", $theme_advanced_buttons);
    }

    /**
     * @param array $a_html_tags
     * @return string
     */
    protected function _buildButtonsFromHTMLTags(array $a_html_tags)
    {
        $theme_advanced_buttons = array();
        if (in_array("strong", $a_html_tags)) {
            array_push($theme_advanced_buttons, "bold");
        }
        if (in_array("em", $a_html_tags)) {
            array_push($theme_advanced_buttons, "italic");
        }
        if (in_array("u", $a_html_tags)) {
            array_push($theme_advanced_buttons, "underline");
        }
        if (in_array("strike", $a_html_tags)) {
            array_push($theme_advanced_buttons, "strikethrough");
        }
        if (in_array("p", $a_html_tags)) {
            array_push($theme_advanced_buttons, "justifyleft");
            array_push($theme_advanced_buttons, "justifycenter");
            array_push($theme_advanced_buttons, "justifyright");
            array_push($theme_advanced_buttons, "justifyfull");
        }
        if (strlen(ilTinyMCE::_buildAdvancedBlockformatsFromHTMLTags($a_html_tags))) {
            array_push($theme_advanced_buttons, "formatselect");
        }
        if (in_array("hr", $a_html_tags)) {
            array_push($theme_advanced_buttons, "hr");
        }
        if (in_array("sub", $a_html_tags)) {
            array_push($theme_advanced_buttons, "sub");
        }
        if (in_array("sup", $a_html_tags)) {
            array_push($theme_advanced_buttons, "sup");
        }
        if (in_array("font", $a_html_tags)) {
            array_push($theme_advanced_buttons, "fontselect");
            array_push($theme_advanced_buttons, "fontsizeselect");
        }
        if ((in_array("ol", $a_html_tags)) && (in_array("li", $a_html_tags))) {
            array_push($theme_advanced_buttons, "bullist");
        }
        if ((in_array("ul", $a_html_tags)) && (in_array("li", $a_html_tags))) {
            array_push($theme_advanced_buttons, "numlist");
        }
        if (in_array("cite", $a_html_tags)) {
            array_push($theme_advanced_buttons, "cite");
        }
        if (in_array("abbr", $a_html_tags)) {
            array_push($theme_advanced_buttons, "abbr");
        }
        if (in_array("acronym", $a_html_tags)) {
            array_push($theme_advanced_buttons, "acronym");
        }
        if (in_array("del", $a_html_tags)) {
            array_push($theme_advanced_buttons, "del");
        }
        if (in_array("ins", $a_html_tags)) {
            array_push($theme_advanced_buttons, "ins");
        }
        if (in_array("blockquote", $a_html_tags)) {
            array_push($theme_advanced_buttons, "indent");
            array_push($theme_advanced_buttons, "outdent");
        }
        if (in_array("img", $a_html_tags)) {
            //array_push($theme_advanced_buttons, "advimage");
            array_push($theme_advanced_buttons, "image");
            array_push($theme_advanced_buttons, "ibrowser");
            array_push($theme_advanced_buttons, "ilimgupload");
        }
        if (in_array("a", $a_html_tags)) {
            array_push($theme_advanced_buttons, "link");
            array_push($theme_advanced_buttons, "unlink");
            array_push($theme_advanced_buttons, "anchor");
        }
        
        $remove_buttons = $this->getDisabledButtons();
        if (is_array($remove_buttons)) {
            foreach ($remove_buttons as $buttontext) {
                if (($res = array_search($buttontext, $theme_advanced_buttons)) !== false) {
                    unset($theme_advanced_buttons[$res]);
                }
            }
        }
        
        return join(",", $theme_advanced_buttons);
    }

    /**
     * @param array $a_html_tags
     * @return string
     */
    public function _buildAdvancedTableButtonsFromHTMLTags(array $a_html_tags)
    {
        $theme_advanced_buttons = array();
        if (in_array("table", $a_html_tags) && in_array("tr", $a_html_tags) && in_array("td", $a_html_tags)) {
            array_push($theme_advanced_buttons, "tablecontrols");
        }
        
        $remove_buttons = $this->getDisabledButtons();
        if (is_array($remove_buttons)) {
            foreach ($remove_buttons as $buttontext) {
                if (($res = array_search($buttontext, $theme_advanced_buttons)) !== false) {
                    unset($theme_advanced_buttons[$res]);
                }
            }
        }
        
        return join(",", $theme_advanced_buttons);
    }

    protected function _getEditorLanguage()
    {
        $lang = $this->user->getLanguage();
        if (file_exists("./Services/RTE/tiny_mce" . $this->vd . "/langs/$lang.js")) {
            return "$lang";
        } else {
            return "en";
        }
    }

    /**
     * @param array $a_html_tags
     * @return string
     */
    public function _getValidElementsFromHTMLTags(array $a_html_tags)
    {
        $valid_elements = array();
        foreach ($a_html_tags as $tag) {
            switch ($tag) {
                case "a":
                    array_push($valid_elements, "a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name"
                        . "|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rel|rev"
                        . "|shape<circle?default?poly?rect|style|tabindex|title|target|type]");
                    break;
                case "abbr":
                    array_push($valid_elements, "abbr[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "acronym":
                    array_push($valid_elements, "acronym[class|dir<ltr?rtl|id|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "address":
                    array_push($valid_elements, "address[class|align|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title]");
                    break;
                case "applet":
                    array_push($valid_elements, "applet[align<bottom?left?middle?right?top|alt|archive|class|code|codebase"
                        . "|height|hspace|id|name|object|style|title|vspace|width]");
                    break;
                case "area":
                    array_push($valid_elements, "area[accesskey|alt|class|coords|dir<ltr?rtl|href|id|lang|nohref<nohref"
                    . "|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
                    . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup"
                    . "|shape<circle?default?poly?rect|style|tabindex|title|target]");
                    break;
                case "base":
                    array_push($valid_elements, "base[href|target]");
                    break;
                case "basefont":
                    array_push($valid_elements, "basefont[color|face|id|size]");
                    break;
                case "bdo":
                    array_push($valid_elements, "bdo[class|dir<ltr?rtl|id|lang|style|title]");
                    break;
                case "big":
                    array_push($valid_elements, "big[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                    . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                    . "|title]");
                    break;
                case "blockquote":
                    array_push($valid_elements, "blockquote[dir|style|cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick"
                    . "|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
                    . "|onmouseover|onmouseup|style|title]");
                    break;
                case "body":
                    array_push($valid_elements, "body[alink|background|bgcolor|class|dir<ltr?rtl|id|lang|link|onclick"
                    . "|ondblclick|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove"
                    . "|onmouseout|onmouseover|onmouseup|onunload|style|title|text|vlink]");
                    break;
                case "br":
                    array_push($valid_elements, "br[class|clear<all?left?none?right|id|style|title]");
                    break;
                case "button":
                    array_push($valid_elements, "button[accesskey|class|dir<ltr?rtl|disabled<disabled|id|lang|name|onblur"
                    . "|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown"
                    . "|onmousemove|onmouseout|onmouseover|onmouseup|style|tabindex|title|type"
                    . "|value]");
                    break;
                case "caption":
                    array_push($valid_elements, "caption[align<bottom?left?right?top|class|dir<ltr?rtl|id|lang|onclick"
                    . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                    . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "center":
                    array_push($valid_elements, "center[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "cite":
                    array_push($valid_elements, "cite[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "code":
                    array_push($valid_elements, "code[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "col":
                    array_push($valid_elements, "col[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
                        . "|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
                        . "|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title"
                        . "|valign<baseline?bottom?middle?top|width]");
                    break;
                case "colgroup":
                    array_push($valid_elements, "colgroup[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl"
                        . "|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
                        . "|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title"
                        . "|valign<baseline?bottom?middle?top|width]");
                    break;
                case "dd":
                    array_push($valid_elements, "dd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "del":
                    array_push($valid_elements, "del[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title]");
                    break;
                case "dfn":
                    array_push($valid_elements, "dfn[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "dir":
                    array_push($valid_elements, "dir[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title]");
                    break;
                case "div":
                    array_push($valid_elements, "div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "dl":
                    array_push($valid_elements, "dl[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title]");
                    break;
                case "dt":
                    array_push($valid_elements, "dt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "em":
                    array_push($valid_elements, "em/i[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "fieldset":
                    array_push($valid_elements, "fieldset[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "font":
                    array_push($valid_elements, "font[class|color|dir<ltr?rtl|face|id|lang|size|style|title]");
                    break;
                case "form":
                    array_push($valid_elements, "form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang"
                        . "|method<get?post|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onsubmit"
                        . "|style|title|target]");
                    break;
                case "frame":
                    array_push($valid_elements, "frame[class|frameborder|id|longdesc|marginheight|marginwidth|name"
                        . "|noresize<noresize|scrolling<auto?no?yes|src|style|title]");
                    break;
                case "frameset":
                    array_push($valid_elements, "frameset[class|cols|id|onload|onunload|rows|style|title]");
                    break;
                case "h1":
                    array_push($valid_elements, "h1[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "h2":
                    array_push($valid_elements, "h2[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "h3":
                    array_push($valid_elements, "h3[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "h4":
                    array_push($valid_elements, "h4[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "h5":
                    array_push($valid_elements, "h5[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "h6":
                    array_push($valid_elements, "h6[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "head":
                    array_push($valid_elements, "head[dir<ltr?rtl|lang|profile]");
                    break;
                case "hr":
                    array_push($valid_elements, "hr[align<center?left?right|class|dir<ltr?rtl|id|lang|noshade<noshade|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|size|style|title|width]");
                    break;
                case "html":
                    array_push($valid_elements, "html[dir<ltr?rtl|lang|version]");
                    break;
                case "iframe":
                    array_push($valid_elements, "iframe[align<bottom?left?middle?right?top|class|frameborder|height|id"
                        . "|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style"
                        . "|title|width]");
                    break;
                case "img":
                    array_push($valid_elements, "img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height"
                        . "|hspace|id|ismap<ismap|lang|longdesc|name|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|src|style|title|usemap|vspace|width]");
                    break;
                case "input":
                    array_push($valid_elements, "input[accept|accesskey|align<bottom?left?middle?right?top|alt"
                        . "|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang"
                        . "|maxlength|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect"
                        . "|readonly<readonly|size|src|style|tabindex|title"
                        . "|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text"
                        . "|usemap|value]");
                    break;
                case "ins":
                    array_push($valid_elements, "ins[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title]");
                    break;
                case "isindex":
                    array_push($valid_elements, "isindex[class|dir<ltr?rtl|id|lang|prompt|style|title]");
                    break;
                case "kbd":
                    array_push($valid_elements, "kbd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "label":
                    array_push($valid_elements, "label[accesskey|class|dir<ltr?rtl|for|id|lang|onblur|onclick|ondblclick"
                        . "|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
                        . "|onmouseover|onmouseup|style|title]");
                    break;
                case "legend":
                    array_push($valid_elements, "legend[align<bottom?left?right?top|accesskey|class|dir<ltr?rtl|id|lang"
                        . "|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "li":
                    array_push($valid_elements, "li[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type"
                        . "|value]");
                    break;
                case "link":
                    array_push($valid_elements, "link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|rel|rev|style|title|target|type]");
                    break;
                case "map":
                    array_push($valid_elements, "map[class|dir<ltr?rtl|id|lang|name|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "menu":
                    array_push($valid_elements, "menu[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title]");
                    break;
                case "meta":
                    array_push($valid_elements, "meta[content|dir<ltr?rtl|http-equiv|lang|name|scheme]");
                    break;
                case "noframes":
                    array_push($valid_elements, "noframes[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "noscript":
                    array_push($valid_elements, "noscript[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "object":
                    array_push($valid_elements, "object[align<bottom?left?middle?right?top|archive|border|class|classid"
                        . "|codebase|codetype|data|declare|dir<ltr?rtl|height|hspace|id|lang|name"
                        . "|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|standby|style|tabindex|title|type|usemap"
                        . "|vspace|width]");
                    break;
                case "ol":
                    array_push($valid_elements, "ol[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|start|style|title|type]");
                    break;
                case "optgroup":
                    array_push($valid_elements, "optgroup[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "option":
                    array_push($valid_elements, "option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick|ondblclick"
                        . "|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
                        . "|onmouseover|onmouseup|selected<selected|style|title|value]");
                    break;
                case "p":
                    array_push($valid_elements, "p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "param":
                    array_push($valid_elements, "param[id|name|type|value|valuetype<DATA?OBJECT?REF]");
                    break;
                case "pre":
                case "listing":
                case "plaintext":
                case "xmp":
                    array_push($valid_elements, "pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|onclick|ondblclick"
                        . "|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
                        . "|onmouseover|onmouseup|style|title|width]");
                    break;
                case "q":
                    array_push($valid_elements, "q[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "s":
                    array_push($valid_elements, "s[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "samp":
                    array_push($valid_elements, "samp[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "script":
                    array_push($valid_elements, "script[charset|defer|language|src|type]");
                    break;
                case "select":
                    array_push($valid_elements, "select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name"
                        . "|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|size|style"
                        . "|tabindex|title]");
                    break;
                case "small":
                    array_push($valid_elements, "small[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "span":
                    array_push($valid_elements, "span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title]");
                    break;
                case "strike":
                    array_push($valid_elements, "strike[class|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title]");
                    break;
                case "strong":
                    array_push($valid_elements, "strong/b[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "style":
                    array_push($valid_elements, "style[dir<ltr?rtl|lang|media|title|type]");
                    break;
                case "sub":
                    array_push($valid_elements, "sub[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "sup":
                    array_push($valid_elements, "sup[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
                case "table":
                    array_push($valid_elements, "table[align<center?left?right|bgcolor|border|cellpadding|cellspacing|class"
                        . "|dir<ltr?rtl|frame|height|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rules"
                        . "|style|summary|title|width]");
                    break;
                case "tbody":
                    array_push($valid_elements, "tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id"
                        . "|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
                        . "|onmousemove|onmouseout|onmouseover|onmouseup|style|title"
                        . "|valign<baseline?bottom?middle?top]");
                    break;
                case "td":
                    array_push($valid_elements, "td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class"
                        . "|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup"
                        . "|style|title|valign<baseline?bottom?middle?top|width]");
                    break;
                case "textarea":
                    array_push($valid_elements, "textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name"
                        . "|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect"
                        . "|readonly<readonly|rows|style|tabindex|title]");
                    break;
                case "tfoot":
                    array_push($valid_elements, "tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
                        . "|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
                        . "|onmousemove|onmouseout|onmouseover|onmouseup|style|title"
                        . "|valign<baseline?bottom?middle?top]");
                    break;
                case "th":
                    array_push($valid_elements, "th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class"
                        . "|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick"
                        . "|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
                        . "|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup"
                        . "|style|title|valign<baseline?bottom?middle?top|width]");
                    break;
                case "thead":
                    array_push($valid_elements, "thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
                        . "|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
                        . "|onmousemove|onmouseout|onmouseover|onmouseup|style|title"
                        . "|valign<baseline?bottom?middle?top]");
                    break;
                case "title":
                    array_push($valid_elements, "title[dir<ltr?rtl|lang]");
                    break;
                case "tr":
                    array_push($valid_elements, "tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class"
                        . "|rowspan|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title|valign<baseline?bottom?middle?top]");
                    break;
                case "tt":
                    array_push($valid_elements, "tt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]");
                    break;
                case "u":
                    array_push($valid_elements, "u[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
                        . "|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]");
                    
                    // Bugfix #5945: Necessary because TinyMCE does not use the "u"
                    // html element but <span style="text-decoration: underline">E</span>
                    array_push($valid_elements, "span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title]");
                    break;
                case "ul":
                    array_push($valid_elements, "ul[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
                        . "|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
                        . "|onmouseup|style|title|type]");
                    break;
                case "var":
                    array_push($valid_elements, "var[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
                        . "|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
                        . "|title]");
                    break;
            }
        }
        return join(",", $valid_elements);
    }

    /**
     * Removes redundant seperators and removes ,, and , at the first or last position of the string
     * @param   string $a_string A string
     * @return  string
     */
    public static function removeRedundantSeparators($a_string)
    {
        while (strpos($a_string, 'separator,separator') !== false) {
            $a_string = str_replace('separator,separator', 'separator', $a_string);
        }
        
        while (strpos($a_string, ',,') !== false) {
            $a_string = str_replace(',,', ',', $a_string);
        }

        if ($a_string[0] == ',') {
            $a_string = (string) substr($a_string, 1);
        }

        if (strlen($a_string) && $a_string[strlen($a_string) - 1] == ',') {
            $a_string = substr($a_string, 0, strlen($a_string) - 1);
        }

        return $a_string;
    }

    /**
     * @param boolean $remove_img_context_menu_item
     */
    public function setRemoveImgContextMenuItem($remove_img_context_menu_item)
    {
        $this->remove_img_context_menu_item = $remove_img_context_menu_item;
    }

    /**
     * @return boolean
     */
    public function getRemoveImgContextMenuItem()
    {
        return $this->remove_img_context_menu_item;
    }
}
