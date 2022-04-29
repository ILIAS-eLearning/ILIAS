<?php declare(strict_types=1);

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
 * Tiny MCE editor class
 *
 * This class provides access methods for Tiny MCE
 *
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version    $Id$
 * @module   class.ilTinyMCE.php
 */
class ilTinyMCE extends ilRTE
{
    protected static bool $renderedToGlobalTemplate = false;

    protected string $mode = 'textareas';
    protected string $version = ''; // set default version here
    protected bool $styleselect = false;
    protected bool $remove_img_context_menu_item = false;
    /**
     * @var string[]
     */
    protected array $contextMenuItems;

    public function __construct()
    {
        parent::__construct();

        $this->plugins = [
            'link',
            'emoticons',
            'hr',
            'table',
            'save',
            'insertdatetime',
            'preview',
            'searchreplace',
            'print',
            'paste',
            'directionality',
            'fullscreen',
            'nonbreaking',
            'noneditable',
            'anchor',
            'lists',
            'code',
            'charmap'
        ];
        $this->contextMenuItems = ['cut', 'copy', 'paste', 'link', 'unlink', 'ilimgupload', 'imagetools', 'table'];

        $this->setStyleSelect(false);
        $this->addInternalTinyMCEImageManager();
    }


    /**
     * @return string[]
     */
    public function getPlugins() : array
    {
        return $this->plugins;
    }

    protected function addInternalTinyMCEImageManager() : void
    {
        if (!$this->client_init->readVariable('tinymce', 'use_advanced_img_mng')) {
            parent::addPlugin('ilimgupload');
            $this->addButton('ilimgupload');
            parent::removePlugin('ibrowser');
            parent::removePlugin('image');

            $this->disableButtons([
                'ibrowser',
                'image'
            ]);

            $this->setRemoveImgContextMenuItem(true);
        } else {
            parent::addPlugin('ibrowser');
            parent::removePlugin('ilimgupload');
            $this->disableButtons('ilimgupload');

            $this->setRemoveImgContextMenuItem(false);
        }
    }

    /**
     * @param string[] $tags
     */
    protected function handleImagePluginsBeforeRendering(array $tags) : void
    {
        if (!in_array('img', $tags)) {
            $this->setRemoveImgContextMenuItem(true);
            parent::removePlugin('ilimgupload');
            parent::removePlugin('ibrowser');
            parent::removePlugin('image');
            $this->disableButtons([
                'ibrowser',
                'image',
                'ilimgupload'
            ]);
        }
    }

    protected function handleIliasImageManagerAdded() : void
    {
        $this->addInternalTinyMCEImageManager();
    }

    protected function handleIliasImageManagerRemoved() : void
    {
        if (!$this->client_init->readVariable('tinymce', 'use_advanced_img_mng')) {
            parent::removePlugin('ilimgupload');
            $this->disableButtons('ilimgupload');
        } else {
            parent::removePlugin('ibrowser');
            $this->disableButtons('ibrowser');
        }
    }

    public function addPlugin(string $a_plugin_name) : void
    {
        if (self::ILIAS_IMG_MANAGER_PLUGIN === $a_plugin_name) {
            $this->handleIliasImageManagerAdded();
        } else {
            parent::addPlugin($a_plugin_name);
        }
    }

    public function removePlugin(string $a_plugin_name) : void
    {
        if (self::ILIAS_IMG_MANAGER_PLUGIN === $a_plugin_name) {
            $this->handleIliasImageManagerRemoved();
        } else {
            parent::removePlugin($a_plugin_name);
        }
    }

    public function addRTESupport(
        int $obj_id,
        string $obj_type,
        string $a_module = '',
        bool $allowFormElements = false,
        ?string $cfg_template = null,
        bool $hide_switch = false
    ) : void {
        global $DIC;

        $lng = $DIC['lng'];

        if ($this->browser->isMobile()) {
            ilObjAdvancedEditing::_setRichTextEditorUserState(0);
        } else {
            ilObjAdvancedEditing::_setRichTextEditorUserState(1);
        }

        if (
            ilObjAdvancedEditing::_getRichTextEditorUserState() !== 0 &&
            strcmp(ilObjAdvancedEditing::_getRichTextEditor(), "0") !== 0
        ) {
            $tpl = new ilTemplate(
                ($cfg_template ?? "tpl.tinymce.js"),
                true,
                true,
                "Services/RTE"
            );
            $this->handleImgContextMenuItem($tpl);
            $tags = ilObjAdvancedEditing::_getUsedHTMLTags($a_module);
            $this->handleImagePluginsBeforeRendering($tags);
            if ($allowFormElements) {
                $tpl->touchBlock("formelements");
            }
            if ($this->getInitialWidth() !== null && $tpl->blockExists('initial_width')) {
                $tpl->setCurrentBlock('initial_width');
                $tpl->setVariable('INITIAL_WIDTH', $this->getInitialWidth());
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('tinymce');

            $tpl->setVariable('OBJ_ID', $obj_id);
            $tpl->setVariable('OBJ_TYPE', $obj_type);
            $tpl->setVariable('CLIENT_ID', CLIENT_ID);
            $tpl->setVariable('SESSION_ID', $_COOKIE[session_name()]);
            $tpl->setVariable('BLOCKFORMATS', $this->_buildAdvancedBlockformatsFromHTMLTags($tags));
            $tpl->setVariable('VALID_ELEMENTS', $this->_getValidElementsFromHTMLTags($tags));
            $tpl->setVariable('TXT_MAX_SIZE', ilFileUtils::getFileSizeInfo());
            // allowed extentions for uploaded image files
            $tinyMCE_valid_imgs = ['gif', 'jpg', 'jpeg', 'png'];
            $tpl->setVariable(
                'TXT_ALLOWED_FILE_EXTENSIONS',
                $lng->txt('file_allowed_suffixes') . ' ' .
                implode(', ', array_map(static function (string $value) : string {
                    return '.' . $value;
                }, $tinyMCE_valid_imgs))
            );

            $buttons_1 = $this->_buildAdvancedButtonsFromHTMLTags(1, $tags);
            $buttons_2 = $this->_buildAdvancedButtonsFromHTMLTags(2, $tags)
                . ',' . $this->_buildAdvancedTableButtonsFromHTMLTags($tags)
                . ($this->getStyleSelect() ? ',styleselect' : '');
            $buttons_3 = $this->_buildAdvancedButtonsFromHTMLTags(3, $tags);
            $tpl->setVariable('BUTTONS_1', self::removeRedundantSeparators($buttons_1));
            $tpl->setVariable('BUTTONS_2', self::removeRedundantSeparators($buttons_2));
            $tpl->setVariable('BUTTONS_3', self::removeRedundantSeparators($buttons_3));

            $tpl->setVariable('CONTEXT_MENU_ITEMS', implode(' ', $this->contextMenuItems));

            $tpl->setVariable('ADDITIONAL_PLUGINS', implode(' ', $this->plugins));

            $tpl->setVariable(
                'STYLESHEET_LOCATION',
                ilUtil::getNewContentStyleSheetLocation() . ',' . ilUtil::getStyleSheetLocation('output', 'delos.css')
            );
            $tpl->setVariable('LANG', $this->_getEditorLanguage());

            if ($this->getRTERootBlockElement() !== null) {
                $tpl->setVariable('FORCED_ROOT_BLOCK', $this->getRTERootBlockElement());
            }

            $tpl->parseCurrentBlock();

            if (!self::$renderedToGlobalTemplate) {
                $this->tpl->addJavaScript('node_modules/tinymce/tinymce.js');
                $this->tpl->addOnLoadCode($tpl->get());
                self::$renderedToGlobalTemplate = true;
            }
        }
    }

    protected function handleImgContextMenuItem(ilTemplate $tpl) : void
    {
        if ($this->getRemoveImgContextMenuItem() && $tpl->blockExists('remove_img_context_menu_item')) {
            $tpl->touchBlock('remove_img_context_menu_item');
        }
    }

    //https://github.com/ILIAS-eLearning/ILIAS/pull/3088#issuecomment-805830050

    public function addContextmenuItem(string $item = '') : void
    {
        if ($item !== '') {
            $this->contextMenuItems[] = $item;
        }
    }

    public function removeAllContextMenuItems() : void
    {
        $this->contextMenuItems = [];
    }

    public function addCustomRTESupport(int $obj_id, string $obj_type, array $tags) : void
    {
        $this->handleImagePluginsBeforeRendering($tags);

        $tpl = new ilTemplate('tpl.tinymce.js', true, true, 'Services/RTE');
        $this->handleImgContextMenuItem($tpl);
        $tpl->setCurrentBlock('tinymce');

        $tpl->setVariable('OBJ_ID', $obj_id);
        $tpl->setVariable('OBJ_TYPE', $obj_type);
        $tpl->setVariable('CLIENT_ID', CLIENT_ID);
        $tpl->setVariable('SESSION_ID', $_COOKIE[session_name()]);
        $tpl->setVariable('BLOCKFORMATS', $this->_buildAdvancedBlockformatsFromHTMLTags($tags));
        $tpl->setVariable('VALID_ELEMENTS', $this->_getValidElementsFromHTMLTags($tags));
        $tpl->setVariable('TXT_MAX_SIZE', ilFileUtils::getFileSizeInfo());

        $this->disableButtons('charmap');
        $buttons_1 = $this->_buildAdvancedButtonsFromHTMLTags(1, $tags);
        $buttons_2 = $this->_buildAdvancedButtonsFromHTMLTags(2, $tags)
            . ',' . $this->_buildAdvancedTableButtonsFromHTMLTags($tags)
            . ($this->getStyleSelect() ? ',styleselect' : '');
        $buttons_3 = $this->_buildAdvancedButtonsFromHTMLTags(3, $tags);
        $tpl->setVariable('BUTTONS_1', self::removeRedundantSeparators($buttons_1));
        $tpl->setVariable('BUTTONS_2', self::removeRedundantSeparators($buttons_2));
        $tpl->setVariable('BUTTONS_3', self::removeRedundantSeparators($buttons_3));

        $tpl->setVariable('CONTEXT_MENU_ITEMS', implode(' ', $this->contextMenuItems));

        $tpl->setVariable('ADDITIONAL_PLUGINS', implode(' ', $this->plugins));

        $tpl->setVariable('STYLESHEET_LOCATION', ilUtil::getNewContentStyleSheetLocation());
        $tpl->setVariable('LANG', $this->_getEditorLanguage());

        if ($this->getRTERootBlockElement() !== null) {
            $tpl->setVariable('FORCED_ROOT_BLOCK', $this->getRTERootBlockElement());
        }

        $tpl->parseCurrentBlock();

        if (!self::$renderedToGlobalTemplate) {
            $this->tpl->addJavaScript('node_modules/tinymce/tinymce.js');
            $this->tpl->addOnLoadCode($tpl->get());
            self::$renderedToGlobalTemplate = true;
        }
    }

    public function addUserTextEditor(string $editor_selector) : void
    {
        $validtags = ["strong", "em", "p", "br", "div", "span"];
        $buttontags = ['strong', 'em'];

        $template = new ilTemplate('tpl.usereditor.js', true, true, 'Services/RTE');
        $this->handleImgContextMenuItem($template);
        $template->setCurrentBlock('tinymce');

        $template->setVariable('SELECTOR', $editor_selector);
        $template->setVariable('BLOCKFORMATS', '');
        $template->setVariable('VALID_ELEMENTS', $this->_getValidElementsFromHTMLTags($validtags));
        if ($this->getStyleSelect()) {
            $template->setVariable('STYLE_SELECT', ',styleselect');
        }
        $template->setVariable('BUTTONS', $this->getButtonsForUserTextEditor($buttontags) . ' backcolor removeformat');

        $template->setVariable(
            'STYLESHEET_LOCATION',
            ilUtil::getNewContentStyleSheetLocation() . ',' . ilUtil::getStyleSheetLocation('output', 'delos.css')
        );
        $template->setVariable('LANG', $this->_getEditorLanguage());
        $template->parseCurrentBlock();

        $this->tpl->addJavaScript('node_modules/tinymce/tinymce.js');
        $this->tpl->addOnLoadCode($template->get());
    }

    /**
     * @param string[] $buttontags
     * @return string
     */
    protected function getButtonsForUserTextEditor(array $buttontags) : string
    {
        $btns = $this->_buildButtonsFromHTMLTags($buttontags);

        $btns = explode(' ', $btns);

        $btns[] = 'undo';
        $btns[] = 'redo';

        return implode(' ', $btns);
    }

    protected function setStyleSelect(bool $a_styleselect) : void
    {
        $this->styleselect = $a_styleselect;
    }

    public function getStyleSelect() : bool
    {
        return $this->styleselect;
    }

    /**
     * @param string[] $a_html_tags
     * @return string
     */
    public function _buildAdvancedBlockformatsFromHTMLTags(array $a_html_tags) : string
    {
        $blockformats = [];

        if (in_array('p', $a_html_tags)) {
            $blockformats[] = 'p';
        }
        if (in_array('div', $a_html_tags)) {
            $blockformats[] = 'div';
        }
        if (in_array('pre', $a_html_tags)) {
            $blockformats[] = 'pre';
        }
        if (in_array('code', $a_html_tags)) {
            $blockformats[] = 'code';
        }
        if (in_array('h1', $a_html_tags)) {
            $blockformats[] = 'h1';
        }
        if (in_array('h2', $a_html_tags)) {
            $blockformats[] = 'h2';
        }
        if (in_array('h3', $a_html_tags)) {
            $blockformats[] = 'h3';
        }
        if (in_array('h4', $a_html_tags)) {
            $blockformats[] = 'h4';
        }
        if (in_array('h5', $a_html_tags)) {
            $blockformats[] = 'h5';
        }
        if (in_array('h6', $a_html_tags)) {
            $blockformats[] = 'h6';
        }
        if (count($blockformats)) {
            return implode(',', $blockformats);
        }

        return '';
    }

    /**
     * @param int $a_buttons_section
     * @param string[] $a_html_tags
     * @return string
     */
    public function _buildAdvancedButtonsFromHTMLTags(int $a_buttons_section, array $a_html_tags) : string
    {
        $theme_advanced_buttons = [];

        if ($a_buttons_section === 1) {
            if (in_array('strong', $a_html_tags)) {
                $theme_advanced_buttons[] = 'bold';
            }
            if (in_array('em', $a_html_tags)) {
                $theme_advanced_buttons[] = 'italic';
            }
            if (in_array('u', $a_html_tags)) {
                $theme_advanced_buttons[] = 'underline';
            }
            if (in_array('strike', $a_html_tags)) {
                $theme_advanced_buttons[] = 'strikethrough';
            }
            if (count($theme_advanced_buttons)) {
                $theme_advanced_buttons[] = '|';
            }
            if (in_array('p', $a_html_tags)) {
                $theme_advanced_buttons[] = 'alignleft';
                $theme_advanced_buttons[] = 'aligncenter';
                $theme_advanced_buttons[] = 'alignright';
                $theme_advanced_buttons[] = 'alignjustify';
                $theme_advanced_buttons[] = '|';
            }
            if ($this->_buildAdvancedBlockformatsFromHTMLTags($a_html_tags) !== '') {
                $theme_advanced_buttons[] = 'formatselect';
            }
            if (in_array('hr', $a_html_tags)) {
                $theme_advanced_buttons[] = 'hr';
            }
            $theme_advanced_buttons[] = 'removeformat';
            $theme_advanced_buttons[] = '|';
            if (in_array('sub', $a_html_tags)) {
                $theme_advanced_buttons[] = 'subscript';
            }
            if (in_array('sup', $a_html_tags)) {
                $theme_advanced_buttons[] = 'superscript';
            }
            if (in_array('font', $a_html_tags)) {
                $theme_advanced_buttons[] = 'fontselect';
                $theme_advanced_buttons[] = 'fontsizeselect';
            }
            $theme_advanced_buttons[] = 'charmap';
            if ((in_array('ol', $a_html_tags)) && (in_array('li', $a_html_tags))) {
                $theme_advanced_buttons[] = 'bullist';
            }
            if ((in_array('ul', $a_html_tags)) && (in_array('li', $a_html_tags))) {
                $theme_advanced_buttons[] = 'numlist';
            }
            $theme_advanced_buttons[] = '|';
            if (in_array('cite', $a_html_tags)) {
                $theme_advanced_buttons[] = 'blockquote';
            }
            if (in_array('abbr', $a_html_tags)) {
                $theme_advanced_buttons[] = 'abbr';
            }
            if (in_array('acronym', $a_html_tags)) {
                $theme_advanced_buttons[] = 'acronym';
            }
            if (in_array('del', $a_html_tags)) {
                $theme_advanced_buttons[] = 'del';
            }
            if (in_array('ins', $a_html_tags)) {
                $theme_advanced_buttons[] = 'ins';
            }
            if (in_array('blockquote', $a_html_tags)) {
                $theme_advanced_buttons[] = 'indent';
                $theme_advanced_buttons[] = 'outdent';
            }
            if (in_array('img', $a_html_tags)) {
                //array_push($theme_advanced_buttons, 'advimage');
                $theme_advanced_buttons[] = 'image';
                $theme_advanced_buttons[] = 'ibrowser';
                $theme_advanced_buttons[] = 'ilimgupload';
            }
            if (in_array('a', $a_html_tags)) {
                $theme_advanced_buttons[] = 'link';
                $theme_advanced_buttons[] = 'unlink';
                $theme_advanced_buttons[] = 'anchor';
            }
            $theme_advanced_buttons[] = '|';
            $theme_advanced_buttons[] = 'undo';
            $theme_advanced_buttons[] = 'redo';

            if (is_array($this->buttons) && count($this->buttons)) {
                $theme_advanced_buttons[] = '|';
                foreach ($this->buttons as $button) {
                    $theme_advanced_buttons[] = $button;
                }
            }

            $theme_advanced_buttons[] = 'code';
            $theme_advanced_buttons[] = 'fullscreen';

            // Changed in elba2 branch, adopted change for 4.2.x due to manits bug #8147
            $theme_advanced_buttons[] = 'pasteword';
        } elseif ($a_buttons_section === 2) {
            $theme_advanced_buttons[] = 'cut';
            $theme_advanced_buttons[] = 'copy';
            $theme_advanced_buttons[] = 'paste';
            $theme_advanced_buttons[] = 'pastetext';
            // Changed in elba2 branch, adopted change for 4.2.x due to manits bug #8147
            //array_push($theme_advanced_buttons, 'pasteword');
        }

        $remove_buttons = $this->getDisabledButtons();
        if (is_array($remove_buttons)) {
            foreach ($remove_buttons as $buttontext) {
                if (($res = array_search($buttontext, $theme_advanced_buttons, true)) !== false) {
                    unset($theme_advanced_buttons[$res]);
                }
            }
        }

        return implode(' ', $theme_advanced_buttons);
    }

    /**
     * @param string[] $a_html_tags
     * @return string
     */
    protected function _buildButtonsFromHTMLTags(array $a_html_tags) : string
    {
        $theme_advanced_buttons = [];
        if (in_array('strong', $a_html_tags)) {
            $theme_advanced_buttons[] = 'bold';
        }
        if (in_array('em', $a_html_tags)) {
            $theme_advanced_buttons[] = 'italic';
        }
        if (in_array('u', $a_html_tags)) {
            $theme_advanced_buttons[] = 'underline';
        }
        if (in_array('strike', $a_html_tags)) {
            $theme_advanced_buttons[] = 'strikethrough';
        }
        if (in_array('p', $a_html_tags)) {
            $theme_advanced_buttons[] = 'alignleft';
            $theme_advanced_buttons[] = 'aligncenter';
            $theme_advanced_buttons[] = 'alignright';
            $theme_advanced_buttons[] = 'alignjustify';
        }
        if ($this->_buildAdvancedBlockformatsFromHTMLTags($a_html_tags) !== '') {
            $theme_advanced_buttons[] = 'formatselect';
        }
        if (in_array('hr', $a_html_tags)) {
            $theme_advanced_buttons[] = 'hr';
        }
        if (in_array('sub', $a_html_tags)) {
            $theme_advanced_buttons[] = 'subscript';
        }
        if (in_array('sup', $a_html_tags)) {
            $theme_advanced_buttons[] = 'superscript';
        }
        if (in_array('font', $a_html_tags)) {
            $theme_advanced_buttons[] = 'fontselect';
            $theme_advanced_buttons[] = 'fontsizeselect';
        }
        if ((in_array('ol', $a_html_tags)) && (in_array('li', $a_html_tags))) {
            $theme_advanced_buttons[] = 'bullist';
        }
        if ((in_array('ul', $a_html_tags)) && (in_array('li', $a_html_tags))) {
            $theme_advanced_buttons[] = 'numlist';
        }
        if (in_array('cite', $a_html_tags)) {
            $theme_advanced_buttons[] = 'blockquote';
        }
        if (in_array('abbr', $a_html_tags)) {
            $theme_advanced_buttons[] = 'abbr';
        }
        if (in_array('acronym', $a_html_tags)) {
            $theme_advanced_buttons[] = 'acronym';
        }
        if (in_array('del', $a_html_tags)) {
            $theme_advanced_buttons[] = 'del';
        }
        if (in_array('ins', $a_html_tags)) {
            $theme_advanced_buttons[] = 'ins';
        }
        if (in_array('blockquote', $a_html_tags)) {
            $theme_advanced_buttons[] = 'indent';
            $theme_advanced_buttons[] = 'outdent';
        }
        if (in_array('img', $a_html_tags)) {
            //array_push($theme_advanced_buttons, 'advimage');
            $theme_advanced_buttons[] = 'image';
            $theme_advanced_buttons[] = 'ibrowser';
            $theme_advanced_buttons[] = 'ilimgupload';
        }
        if (in_array('a', $a_html_tags)) {
            $theme_advanced_buttons[] = 'link';
            $theme_advanced_buttons[] = 'unlink';
            $theme_advanced_buttons[] = 'anchor';
        }

        $remove_buttons = $this->getDisabledButtons();
        if (is_array($remove_buttons)) {
            foreach ($remove_buttons as $buttontext) {
                if (($res = array_search($buttontext, $theme_advanced_buttons, true)) !== false) {
                    unset($theme_advanced_buttons[$res]);
                }
            }
        }

        return implode(' ', $theme_advanced_buttons);
    }

    /**
     * @param string[] $a_html_tags
     * @return string
     */
    public function _buildAdvancedTableButtonsFromHTMLTags(array $a_html_tags) : string
    {
        $theme_advanced_buttons = [];
        if (
            in_array('table', $a_html_tags, true) &&
            in_array('tr', $a_html_tags, true) &&
            in_array('td', $a_html_tags, true)
        ) {
            $theme_advanced_buttons[] = 'table';
        }

        $remove_buttons = $this->getDisabledButtons();
        if (is_array($remove_buttons)) {
            foreach ($remove_buttons as $buttontext) {
                if (($res = array_search($buttontext, $theme_advanced_buttons, true)) !== false) {
                    unset($theme_advanced_buttons[$res]);
                }
            }
        }

        return implode(',', $theme_advanced_buttons);
    }

    protected function _getEditorLanguage() : string
    {
        $lang = $this->user->getLanguage();
        $langtiny = $lang;
        //Language files in tinymce and ILIAS have different nomenclatures: adjust the differences
        switch ($lang) {
            case 'hu':
                $langtiny = 'hu_HU';
                break;

            case 'zh':
                $langtiny = 'zh_CN';
                break;

            case 'he':
                $langtiny = 'he_IL';
                break;

            default:
                //do nothing
        }

        if (is_file("./node_modules/tinymce/langs/$langtiny.js")) {
            return $langtiny;
        }

        return 'en';
    }

    /**
     * @param string[] $a_html_tags
     * @return string
     */
    public function _getValidElementsFromHTMLTags(array $a_html_tags) : string
    {
        $valid_elements = [];

        foreach ($a_html_tags as $tag) {
            switch ($tag) {
                case 'a':
                    $valid_elements[] = 'a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name'
                        . '|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rel|rev'
                        . '|shape<circle?default?poly?rect|style|tabindex|title|target|type]';
                    break;
                case 'abbr':
                    $valid_elements[] = 'abbr[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'acronym':
                    $valid_elements[] = 'acronym[class|dir<ltr?rtl|id|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'address':
                    $valid_elements[] = 'address[class|align|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title]';
                    break;
                case 'applet':
                    $valid_elements[] = 'applet[align<bottom?left?middle?right?top|alt|archive|class|code|codebase'
                        . '|height|hspace|id|name|object|style|title|vspace|width]';
                    break;
                case 'area':
                    $valid_elements[] = 'area[accesskey|alt|class|coords|dir<ltr?rtl|href|id|lang|nohref<nohref'
                        . '|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup'
                        . '|shape<circle?default?poly?rect|style|tabindex|title|target]';
                    break;
                case 'base':
                    $valid_elements[] = 'base[href|target]';
                    break;
                case 'basefont':
                    $valid_elements[] = 'basefont[color|face|id|size]';
                    break;
                case 'bdo':
                    $valid_elements[] = 'bdo[class|dir<ltr?rtl|id|lang|style|title]';
                    break;
                case 'big':
                    $valid_elements[] = 'big[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'blockquote':
                    $valid_elements[] = 'blockquote[dir|style|cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick'
                        . '|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
                        . '|onmouseover|onmouseup|style|title]';
                    break;
                case 'body':
                    $valid_elements[] = 'body[alink|background|bgcolor|class|dir<ltr?rtl|id|lang|link|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|onunload|style|title|text|vlink]';
                    break;
                case 'br':
                    $valid_elements[] = 'br[class|clear<all?left?none?right|id|style|title]';
                    break;
                case 'button':
                    $valid_elements[] = 'button[accesskey|class|dir<ltr?rtl|disabled<disabled|id|lang|name|onblur'
                        . '|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown'
                        . '|onmousemove|onmouseout|onmouseover|onmouseup|style|tabindex|title|type'
                        . '|value]';
                    break;
                case 'caption':
                    $valid_elements[] = 'caption[align<bottom?left?right?top|class|dir<ltr?rtl|id|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'center':
                    $valid_elements[] = 'center[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'cite':
                    $valid_elements[] = 'cite[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'code':
                    $valid_elements[] = 'code[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'col':
                    $valid_elements[] = 'col[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id'
                        . '|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
                        . '|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title'
                        . '|valign<baseline?bottom?middle?top|width]';
                    break;
                case 'colgroup':
                    $valid_elements[] = 'colgroup[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl'
                        . '|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
                        . '|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title'
                        . '|valign<baseline?bottom?middle?top|width]';
                    break;
                case 'dd':
                    $valid_elements[] = 'dd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'del':
                    $valid_elements[] = 'del[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title]';
                    break;
                case 'dfn':
                    $valid_elements[] = 'dfn[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'dir':
                    $valid_elements[] = 'dir[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title]';
                    break;
                case 'div':
                    $valid_elements[] = 'div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'dl':
                    $valid_elements[] = 'dl[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title]';
                    break;
                case 'dt':
                    $valid_elements[] = 'dt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'em':
                    $valid_elements[] = 'em/i[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'fieldset':
                    $valid_elements[] = 'fieldset[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'font':
                    $valid_elements[] = 'font[class|color|dir<ltr?rtl|face|id|lang|size|style|title]';
                    break;
                case 'form':
                    $valid_elements[] = 'form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang'
                        . '|method<get?post|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onsubmit'
                        . '|style|title|target]';
                    break;
                case 'frame':
                    $valid_elements[] = 'frame[class|frameborder|id|longdesc|marginheight|marginwidth|name'
                        . '|noresize<noresize|scrolling<auto?no?yes|src|style|title]';
                    break;
                case 'frameset':
                    $valid_elements[] = 'frameset[class|cols|id|onload|onunload|rows|style|title]';
                    break;
                case 'h1':
                    $valid_elements[] = 'h1[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'h2':
                    $valid_elements[] = 'h2[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'h3':
                    $valid_elements[] = 'h3[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'h4':
                    $valid_elements[] = 'h4[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'h5':
                    $valid_elements[] = 'h5[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'h6':
                    $valid_elements[] = 'h6[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'head':
                    $valid_elements[] = 'head[dir<ltr?rtl|lang|profile]';
                    break;
                case 'hr':
                    $valid_elements[] = 'hr[align<center?left?right|class|dir<ltr?rtl|id|lang|noshade<noshade|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|size|style|title|width]';
                    break;
                case 'html':
                    $valid_elements[] = 'html[dir<ltr?rtl|lang|version]';
                    break;
                case 'iframe':
                    $valid_elements[] = 'iframe[align<bottom?left?middle?right?top|class|frameborder|height|id'
                        . '|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style'
                        . '|title|width]';
                    break;
                case 'img':
                    $valid_elements[] = 'img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height'
                        . '|hspace|id|ismap<ismap|lang|longdesc|name|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|src|style|title|usemap|vspace|width]';
                    break;
                case 'input':
                    $valid_elements[] = 'input[accept|accesskey|align<bottom?left?middle?right?top|alt'
                        . '|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang'
                        . '|maxlength|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect'
                        . '|readonly<readonly|size|src|style|tabindex|title'
                        . '|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text'
                        . '|usemap|value]';
                    break;
                case 'ins':
                    $valid_elements[] = 'ins[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title]';
                    break;
                case 'isindex':
                    $valid_elements[] = 'isindex[class|dir<ltr?rtl|id|lang|prompt|style|title]';
                    break;
                case 'kbd':
                    $valid_elements[] = 'kbd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'label':
                    $valid_elements[] = 'label[accesskey|class|dir<ltr?rtl|for|id|lang|onblur|onclick|ondblclick'
                        . '|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
                        . '|onmouseover|onmouseup|style|title]';
                    break;
                case 'legend':
                    $valid_elements[] = 'legend[align<bottom?left?right?top|accesskey|class|dir<ltr?rtl|id|lang'
                        . '|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'li':
                    $valid_elements[] = 'li[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type'
                        . '|value]';
                    break;
                case 'link':
                    $valid_elements[] = 'link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|rel|rev|style|title|target|type]';
                    break;
                case 'map':
                    $valid_elements[] = 'map[class|dir<ltr?rtl|id|lang|name|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'menu':
                    $valid_elements[] = 'menu[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title]';
                    break;
                case 'meta':
                    $valid_elements[] = 'meta[content|dir<ltr?rtl|http-equiv|lang|name|scheme]';
                    break;
                case 'noframes':
                    $valid_elements[] = 'noframes[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'noscript':
                    $valid_elements[] = 'noscript[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'object':
                    $valid_elements[] = 'object[align<bottom?left?middle?right?top|archive|border|class|classid'
                        . '|codebase|codetype|data|declare|dir<ltr?rtl|height|hspace|id|lang|name'
                        . '|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|standby|style|tabindex|title|type|usemap'
                        . '|vspace|width]';
                    break;
                case 'ol':
                    $valid_elements[] = 'ol[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|start|style|title|type]';
                    break;
                case 'optgroup':
                    $valid_elements[] = 'optgroup[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'option':
                    $valid_elements[] = 'option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick|ondblclick'
                        . '|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
                        . '|onmouseover|onmouseup|selected<selected|style|title|value]';
                    break;
                case 'p':
                    $valid_elements[] = 'p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'param':
                    $valid_elements[] = 'param[id|name|type|value|valuetype<DATA?OBJECT?REF]';
                    break;
                case 'pre':
                case 'listing':
                case 'plaintext':
                case 'xmp':
                    $valid_elements[] = 'pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|onclick|ondblclick'
                        . '|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
                        . '|onmouseover|onmouseup|style|title|width]';
                    break;
                case 'q':
                    $valid_elements[] = 'q[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 's':
                    $valid_elements[] = 's[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'samp':
                    $valid_elements[] = 'samp[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'script':
                    $valid_elements[] = 'script[charset|defer|language|src|type]';
                    break;
                case 'select':
                    $valid_elements[] = 'select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name'
                        . '|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|size|style'
                        . '|tabindex|title]';
                    break;
                case 'small':
                    $valid_elements[] = 'small[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'span':
                    $valid_elements[] = 'span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title]';
                    break;
                case 'strike':
                    $valid_elements[] = 'strike[class|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title]';
                    break;
                case 'strong':
                    $valid_elements[] = 'strong/b[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'style':
                    $valid_elements[] = 'style[dir<ltr?rtl|lang|media|title|type]';
                    break;
                case 'sub':
                    $valid_elements[] = 'sub[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'sup':
                    $valid_elements[] = 'sup[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
                case 'table':
                    $valid_elements[] = 'table[align<center?left?right|bgcolor|border|cellpadding|cellspacing|class'
                        . '|dir<ltr?rtl|frame|height|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rules'
                        . '|style|summary|title|width]';
                    break;
                case 'tbody':
                    $valid_elements[] = 'tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id'
                        . '|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
                        . '|onmousemove|onmouseout|onmouseover|onmouseup|style|title'
                        . '|valign<baseline?bottom?middle?top]';
                    break;
                case 'td':
                    $valid_elements[] = 'td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class'
                        . '|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup'
                        . '|style|title|valign<baseline?bottom?middle?top|width]';
                    break;
                case 'textarea':
                    $valid_elements[] = 'textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name'
                        . '|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect'
                        . '|readonly<readonly|rows|style|tabindex|title]';
                    break;
                case 'tfoot':
                    $valid_elements[] = 'tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id'
                        . '|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
                        . '|onmousemove|onmouseout|onmouseover|onmouseup|style|title'
                        . '|valign<baseline?bottom?middle?top]';
                    break;
                case 'th':
                    $valid_elements[] = 'th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class'
                        . '|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick'
                        . '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                        . '|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup'
                        . '|style|title|valign<baseline?bottom?middle?top|width]';
                    break;
                case 'thead':
                    $valid_elements[] = 'thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id'
                        . '|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
                        . '|onmousemove|onmouseout|onmouseover|onmouseup|style|title'
                        . '|valign<baseline?bottom?middle?top]';
                    break;
                case 'title':
                    $valid_elements[] = 'title[dir<ltr?rtl|lang]';
                    break;
                case 'tr':
                    $valid_elements[] = 'tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class'
                        . '|rowspan|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title|valign<baseline?bottom?middle?top]';
                    break;
                case 'tt':
                    $valid_elements[] = 'tt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]';
                    break;
                case 'u':
                    $valid_elements[] = 'u[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
                        . '|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]';

                    // Bugfix #5945: Necessary because TinyMCE does not use the 'u'
                    // html element but <span style='text-decoration: underline'>E</span>
                    $valid_elements[] = 'span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title]';
                    break;
                case 'ul':
                    $valid_elements[] = 'ul[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
                        . '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
                        . '|onmouseup|style|title|type]';
                    break;
                case 'var':
                    $valid_elements[] = 'var[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
                        . '|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
                        . '|title]';
                    break;
            }
        }

        return implode(',', $valid_elements);
    }

    /**
     * Removes redundant seperators and removes ,, and , at the first or last position of the string
     * @param string $a_string A string
     * @return  string
     */
    public static function removeRedundantSeparators(string $a_string) : string
    {
        while (strpos($a_string, '| |') !== false) {
            $a_string = str_replace('| |', '|', $a_string);
        }

        while (strpos($a_string, ',,') !== false) {
            $a_string = str_replace(',,', ',', $a_string);
        }
        while (strpos($a_string, 'separator') !== false) {
            $a_string = str_replace('separator', '|', $a_string);
        }
        while (strpos($a_string, ',') !== false) {
            $a_string = str_replace(',', ' ', $a_string);
        }

        if (isset($a_string[0]) && $a_string[0] === ',') {
            $a_string = (string) substr($a_string, 1);
        }

        if ($a_string !== '' && $a_string[strlen($a_string) - 1] === ',') {
            $a_string = substr($a_string, 0, -1);
        }
        //image uploader button keeps appearing twice: remove the duplicates
        if ($a_string !== '' && substr_count($a_string, 'ilimgupload') > 1) {
            $arr = explode('ilimgupload', $a_string, 2);
            $a_string = $arr[0];
            if (count($arr) > 1) {
                $a_string .= ' ilimgupload ' . str_replace('ilimgupload', '', $arr[1]);
            }
        }


        return $a_string;
    }

    public function setRemoveImgContextMenuItem(bool $remove_img_context_menu_item) : void
    {
        $this->remove_img_context_menu_item = $remove_img_context_menu_item;
    }

    public function getRemoveImgContextMenuItem() : bool
    {
        return $this->remove_img_context_menu_item;
    }
}
