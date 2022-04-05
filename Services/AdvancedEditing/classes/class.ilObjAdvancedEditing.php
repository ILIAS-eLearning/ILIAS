<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilObjAdvancedEditing
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilObjAdvancedEditing extends ilObject
{
    public ilSetting $setting;
    
    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setting = new ilSetting("advanced_editing");
        $this->type = "adve";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * Returns an array of all allowed HTML tags for text editing
     * @param string $a_module Name of the module or object which uses the tags
     * @return array HTML tags
     */
    public static function _getUsedHTMLTags(string $a_module = "") : array
    {
        $setting = new ilSetting("advanced_editing");
        $tags = $setting->get("advanced_editing_used_html_tags_" . $a_module, '');
        if ($tags !== '') {
            $usedtags = unserialize($tags, ["allowed_classes" => false]);
        } elseif ($a_module === 'frm_post' || $a_module === 'exc_ass') {
            $usedtags = array(
            "a",
            "blockquote",
            "br",
            "code",
            "div",
            "em",
            "img",
            "li",
            "ol",
            "p",
            "strong",
            "u",
            "ul",
            "span"
            );
        } else {
            // default: everything but tables
            $usedtags = array(
            "a",
            "blockquote",
            "br",
            "cite",
            "code",
            "dd",
            "div",
            "dl",
            "dt",
            "em",
            "h1",
            "h2",
            "h3",
            "h4",
            "h5",
            "h6",
            "hr",
            "img",
            "li",
            "ol",
            "p",
            "pre",
            "span",
            "strike",
            "strong",
            "sub",
            "sup",
            "u",
            "ul"
            );
        }
        
        // frm_posts need blockquote and div urgently
        if ($a_module === 'frm_post') {
            if (!in_array('div', $usedtags, true)) {
                $usedtags[] = 'div';
            }
            
            if (!in_array('blockquote', $usedtags, true)) {
                $usedtags[] = 'blockquote';
            }
        }
        
        return $usedtags;
    }

    /**
     * Returns a string of all allowed HTML tags for text editing
     * @param string $a_module Name of the module or object which uses the tags
     * @return string Used HTML tags
     */
    public static function _getUsedHTMLTagsAsString(string $a_module = "") : string
    {
        $result = "";
        $tags = self::_getUsedHTMLTags($a_module);
        foreach ($tags as $tag) {
            $result .= "<$tag>";
        }
        return $result;
    }
    
    /**
     * Returns the identifier for the Rich Text Editor
     * @return string Identifier for the Rich Text Editor
     */
    public static function _getRichTextEditor() : string
    {
        return (new ilSetting("advanced_editing"))->get("advanced_editing_javascript_editor", "0");
    }
    
    public function setRichTextEditor(string $a_js_editor) : void
    {
        $setting = new ilSetting("advanced_editing");
        $setting->set("advanced_editing_javascript_editor", $a_js_editor);
    }
    
    /**
     * Writes an array with allowed HTML tags to the ILIAS settings
     * @param array $a_html_tags An array containing the allowed HTML tags
     * @param string $a_module The name of the module or object which uses the tags
     * @throws ilAdvancedEditingRequiredTagsException
     */
    public function setUsedHTMLTags(
        array $a_html_tags,
        string $a_module
    ) : void {
        $lng = $this->lng;
        
        if ($a_module !== '') {
            $auto_added_tags = array();
            
            // frm_posts need blockquote and div urgently
            if ($a_module === 'frm_post') {
                if (!in_array('div', $a_html_tags, true)) {
                    $auto_added_tags[] = 'div';
                }
                
                if (!in_array('blockquote', $a_html_tags, true)) {
                    $auto_added_tags[] = 'blockquote';
                }
            }
            
            $setting = new ilSetting("advanced_editing");
            $setting->set("advanced_editing_used_html_tags_" . $a_module, serialize(array_merge($a_html_tags, $auto_added_tags)));
            
            if (count($auto_added_tags)) {
                throw new ilAdvancedEditingRequiredTagsException(
                    sprintf(
                        $lng->txt('advanced_editing_required_tags'),
                        implode(', ', $auto_added_tags)
                    )
                );
            }
        }
    }
    
    /**
     * Returns an array of all possible HTML tags for text editing
     * @return array HTML tags
     */
    public function &getHTMLTags() : array
    {
        $tags = array(
            "a",
            "blockquote",
            "br",
            "cite",
            "code",
            "dd",
            "div",
            "dl",
            "dt",
            "em",
            "h1",
            "h2",
            "h3",
            "h4",
            "h5",
            "h6",
            "hr",
            "img",
            "li",
            "object",
            "ol",
            "p",
            "param",
            "pre",
            "span",
            "strike",
            "strong",
            "sub",
            "sup",
            "table",
            "td",
            "tr",
            "u",
            "ul",
            "ruby", // Ruby Annotation XHTML module
            "rbc",
            "rtc",
            "rb",
            "rt",
            "rp"
        );
        return $tags;
    }

    /**
     * Returns an array of all possible HTML tags for text editing
     * @return array HTML tags
     */
    public static function _getAllHTMLTags() : array
    {
        return array(
            "a",
            "abbr",
            "acronym",
            "address",
            "applet",
            "area",
            "b",
            "base",
            "basefont",
            "bdo",
            "big",
            "blockquote",
            "br",
            "button",
            "caption",
            "center",
            "cite",
            "code",
            "col",
            "colgroup",
            "dd",
            "del",
            "dfn",
            "dir",
            "div",
            "dl",
            "dt",
            "em",
            "fieldset",
            "font",
            "form",
            "h1",
            "h2",
            "h3",
            "h4",
            "h5",
            "h6",
            "hr",
            "i",
            "iframe",
            "img",
            "input",
            "ins",
            "isindex",
            "kbd",
            "label",
            "legend",
            "li",
            "link",
            "map",
            "menu",
            "object",
            "ol",
            "optgroup",
            "option",
            "p",
            "param",
            "pre",
            "q",
            "s",
            "samp",
            "select",
            "small",
            "span",
            "strike",
            "strong",
            "sub",
            "sup",
            "table",
            "tbody",
            "td",
            "textarea",
            "tfoot",
            "th",
            "thead",
            "tr",
            "tt",
            "u",
            "ul",
            "var",
            "ruby", // Ruby Annotation XHTML module
            "rbc",
            "rtc",
            "rb",
            "rt",
            "rp"
        );
    }

    /**
     * Sets the state of the rich text editor visibility for the current user
     */
    public static function _setRichTextEditorUserState(int $a_state) : void
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilUser->writePref("show_rte", (string) $a_state);
    }

    /**
     * Gets the state of the rich text editor visibility for the current user
     * @return int 0 if the RTE should be disabled, 1 otherwise
     */
    public static function _getRichTextEditorUserState() : int
    {
        global $DIC;

        $ilUser = $DIC->user();
        if ($ilUser->getPref("show_rte") != '') {
            return (int) $ilUser->getPref("show_rte");
        }
        return 1;
    }
}
