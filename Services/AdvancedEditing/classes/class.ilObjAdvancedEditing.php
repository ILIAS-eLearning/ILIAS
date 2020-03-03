<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjAdvancedEditing
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilObject
*/
class ilObjAdvancedEditing extends ilObject
{
    public $setting;
    
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->lng = $DIC->language();
        include_once "./Services/Administration/classes/class.ilSetting.php";
        $this->setting = new ilSetting("advanced_editing");
        $this->type = "adve";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update()
    {
        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff

        return true;
    }


    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        //put here your module specific stuff

        return true;
    }

    /**
    * Returns an array of all allowed HTML tags for text editing
    *
    * Returns an array of all allowed HTML tags for text editing
    *
    * @param string $a_module Name of the module or object which uses the tags
    * @return array HTML tags
    */
    public static function _getUsedHTMLTags($a_module = "")
    {
        $usedtags = array();
        include_once "./Services/Administration/classes/class.ilSetting.php";
        $setting = new ilSetting("advanced_editing");
        $tags = $setting->get("advanced_editing_used_html_tags_" . $a_module);
        if (strlen($tags)) {
            $usedtags = unserialize($tags);
        } else {
            if ($a_module == 'frm_post' || $a_module == 'exc_ass') {
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
        }
        
        // frm_posts need blockquote and div urgently
        if ($a_module === 'frm_post') {
            if (!in_array('div', $usedtags)) {
                $usedtags[] = 'div';
            }
            
            if (!in_array('blockquote', $usedtags)) {
                $usedtags[] = 'blockquote';
            }
        }
        
        return $usedtags;
    }

    /**
    * Returns a string of all allowed HTML tags for text editing
    *
    * Returns a string of all allowed HTML tags for text editing
    *
    * @param string $a_module Name of the module or object which uses the tags
    * @return string Used HTML tags
    */
    public static function _getUsedHTMLTagsAsString($a_module = "")
    {
        $result = "";
        $tags = ilObjAdvancedEditing::_getUsedHTMLTags($a_module);
        foreach ($tags as $tag) {
            $result .= "<$tag>";
        }
        return $result;
    }
    
    /**
    * Returns the identifier for the Rich Text Editor
    *
    * Returns the identifier for the Rich Text Editor
    *
    * @return string Identifier for the Rich Text Editor
    */
    public static function _getRichTextEditor()
    {
        include_once "./Services/Administration/classes/class.ilSetting.php";
        $setting = new ilSetting("advanced_editing");
        $js = $setting->get("advanced_editing_javascript_editor");
        return $js;
    }
    
    /**
    * Sets wheather a Rich Text Editor should be used or not
    *
    * Sets wheather a Rich Text Editor should be used or not
    *
    * @param boolean $a_js_editor A boolean indicating if the JS editor should be used or not
    */
    public function setRichTextEditor($a_js_editor)
    {
        include_once "./Services/Administration/classes/class.ilSetting.php";
        $setting = new ilSetting("advanced_editing");
        $setting->set("advanced_editing_javascript_editor", $a_js_editor);
    }
    
    /**
    * Writes an array with allowed HTML tags to the ILIAS settings
    *
    * Writes an array with allowed HTML tags to the ILIAS settings
    *
    * @param array $a_html_tags An array containing the allowed HTML tags
    * @param string $a_module The name of the module or object which uses the tags
    * @throws ilAdvancedEditingRequiredTagsException
    *
    */
    public function setUsedHTMLTags($a_html_tags, $a_module)
    {
        $lng = $this->lng;
        
        if (strlen($a_module)) {
            $auto_added_tags = array();
            
            // frm_posts need blockquote and div urgently
            if ($a_module == 'frm_post') {
                if (!in_array('div', $a_html_tags)) {
                    $auto_added_tags[] = 'div';
                }
                
                if (!in_array('blockquote', $a_html_tags)) {
                    $auto_added_tags[] = 'blockquote';
                }
            }
            
            include_once "./Services/Administration/classes/class.ilSetting.php";
            $setting = new ilSetting("advanced_editing");
            $setting->set("advanced_editing_used_html_tags_" . $a_module, serialize(array_merge((array) $a_html_tags, $auto_added_tags)));
            
            if (count($auto_added_tags)) {
                require_once 'Services/AdvancedEditing/exceptions/class.ilAdvancedEditingRequiredTagsException.php';
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
    *
    * Returns an array of all possible HTML tags for text editing
    *
    * @return array HTML tags
    */
    public function &getHTMLTags()
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
    *
    * Returns an array of all possible HTML tags for text editing
    *
    * @return array HTML tags
    */
    public static function _getAllHTMLTags()
    {
        $tags = array(
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
        return $tags;
    }

    /**
    * Sets the state of the rich text editor visibility for the current user
    *
    * Sets the state of the rich text editor visibility for the current user
    * @static
    * @param integer $a_state 0 if the RTE should be disabled, 1 otherwise
    */
    public static function _setRichTextEditorUserState($a_state)
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilUser->writePref("show_rte", $a_state);
    }

    /**
    * Gets the state of the rich text editor visibility for the current user
    *
    * Gets the state of the rich text editor visibility for the current user
    *
    * @static
    * @return integer 0 if the RTE should be disabled, 1 otherwise
    */
    public static function _getRichTextEditorUserState()
    {
        global $DIC;

        $ilUser = $DIC->user();
        if (strlen($ilUser->getPref("show_rte")) > 0) {
            return $ilUser->getPref("show_rte");
        }
        return 1;
    }
} // END class.ilObjAdvancedEditing
