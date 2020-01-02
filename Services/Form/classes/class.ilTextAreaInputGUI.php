<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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

include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
include_once "./Services/RTE/classes/class.ilRTE.php";

/**
* This class represents a text area property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTextAreaInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected $value;
    protected $cols;
    protected $rows;
    protected $usert;
    protected $rtetags;
    protected $plugins;
    protected $removeplugins;
    protected $buttons;
    protected $rtesupport;
    protected $use_tags_for_rte_only = true;
    protected $max_num_chars;
    protected $min_num_chars;

    /**
     * @var int
     */
    protected $initial_rte_width = 795;
    
    /**
    * Array of tinymce buttons which should be disabled
    *
    * @var		Array
    * @type		Array
    * @access	protected
    *
    */
    protected $disabled_buttons = array();
    
    /**
    * Use purifier or not
    *
    * @var		boolean
    * @type		boolean
    * @access	protected
    *
    */
    protected $usePurifier = false;
    
    /**
    * Instance of ilHtmlPurifierInterface
    *
    * @var		ilHtmlPurifierInterface
    * @type		ilHtmlPurifierInterface
    * @access	protected
    *
    */
    protected $Purifier = null;
    
    /**
    * TinyMCE root block element which surrounds the generated html
    *
    * @var		string
    * @type		string
    * @access	protected
    */
    protected $root_block_element = null;
    
    protected $rte_tag_set = array(
        "mini" => array("strong", "em", "u", "ol", "li", "ul", "blockquote", "a", "p", "span", "br"), // #13286/#17981
        "standard" => array("strong", "em", "u", "ol", "li", "ul", "p", "div",
            "i", "b", "code", "sup", "sub", "pre", "strike", "gap"),
        "extended" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","li","ol","p",
            "pre","span","strike","strong","sub","sup","u","ul",
            "i", "b", "gap"),
        "extended_img" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","img","li","ol","p",
            "pre","span","strike","strong","sub","sup","u","ul",
            "i", "b", "gap"),
        "extended_table" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","li","ol","p",
            "pre","span","strike","strong","sub","sup","table","td",
            "tr","u","ul", "i", "b", "gap"),
        "extended_table_img" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","img","li","ol","p",
            "pre","span","strike","strong","sub","sup","table","td",
            "tr","u","ul", "i", "b", "gap"),
        "full" => array(
            "a","blockquote","br","cite","code","div","em","h1","h2","h3",
            "h4","h5","h6","hr","img","li","ol","p",
            "pre","span","strike","strong","sub","sup","table","td",
            "tr","u","ul","ruby","rbc","rtc","rb","rt","rp", "i", "b", "gap"));
        
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("textarea");
        $this->setRteTagSet("standard");
        $this->plugins = array();
        $this->removeplugins = array();
        $this->buttons = array();
        $this->rteSupport = array();
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
    * Get Value.
    *
    * @return	string	Value
    */
    public function getValue()
    {
        return $this->value;
    }

    /**
    * Set Cols.
    *
    * @deprecated
    * @param	int	$a_cols	Cols
    */
    public function setCols($a_cols)
    {
        // obsolete because of bootstrap
        $this->cols = $a_cols;
    }

    /**
    * Get Cols.
    *
    * @return	int	Cols
    */
    public function getCols()
    {
        return $this->cols;
    }

    /**
    * Set Rows.
    *
    * @param	int	$a_rows	Rows
    */
    public function setRows($a_rows)
    {
        $this->rows = $a_rows;
    }

    /**
    * Get Rows.
    *
    * @return	int	Rows
    */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Set Maximum number of characters allowed.
     *
     * @param int $a_number	Characters
     */
    public function setMaxNumOfChars($a_number)
    {
        $this->max_num_chars = $a_number;
    }

    /**
     * Get Maximum number of characters allowed.
     */
    public function getMaxNumOfChars()
    {
        return $this->max_num_chars;
    }

    /**
     * Set Minimum number of characters allowed.
     *
     * @param int $a_number	Characters
     */
    public function setMinNumOfChars($a_number)
    {
        $this->min_num_chars = $a_number;
    }

    /**
     * Get Minimum number of characters allowed.
     */
    public function getMinNumOfChars()
    {
        return $this->min_num_chars;
    }

    /**
     * Set Use Rich Text Editing.
     *
     * @param	int	$a_usert	Use Rich Text Editing
     * @param	string $version
     */
    public function setUseRte($a_usert, $version = '')
    {
        $this->usert = $a_usert;

        if (strlen($version)) {
            $this->rteSupport['version'] = $version;
        }
    }

    /**
    * Get Use Rich Text Editing.
    *
    * @return	int	Use Rich Text Editing
    */
    public function getUseRte()
    {
        return $this->usert;
    }
    
    /**
    * Add RTE plugin.
    *
    * @param string $a_plugin Plugin name
    */
    public function addPlugin($a_plugin)
    {
        $this->plugins[$a_plugin] = $a_plugin;
    }
    
    /**
    * Remove RTE plugin.
    *
    * @param string $a_plugin Plugin name
    */
    public function removePlugin($a_plugin)
    {
        $this->removeplugins[$a_plugin] = $a_plugin;
    }

    /**
    * Add RTE button.
    *
    * @param string $a_button Button name
    */
    public function addButton($a_button)
    {
        $this->buttons[$a_button] = $a_button;
    }
    
    /**
    * Remove RTE button.
    *
    * @param string $a_button Button name
    */
    public function removeButton($a_button)
    {
        unset($this->buttons[$a_button]);
    }

    /**
    * Set RTE support for a special module
    *
    * @param int $obj_id Object ID
    * @param string $obj_type Object Type
    * @param string $module ILIAS module
    */
    public function setRTESupport($obj_id, $obj_type, $module, $cfg_template = null, $hide_switch = false, $version = null)
    {
        $this->rteSupport = array("obj_id" => $obj_id, "obj_type" => $obj_type, "module" => $module, 'cfg_template' => $cfg_template, 'hide_switch' => $hide_switch, 'version' => $version);
    }
    
    /**
    * Remove RTE support for a special module
    */
    public function removeRTESupport()
    {
        $this->rteSupport = array();
    }

    /**
    * Set Valid RTE Tags.
    *
    * @param	array	$a_rtetags	Valid RTE Tags
    */
    public function setRteTags($a_rtetags)
    {
        $this->rtetags = $a_rtetags;
    }

    /**
    * Get Valid RTE Tags.
    *
    * @return	array	Valid RTE Tags
    */
    public function getRteTags()
    {
        return $this->rtetags;
    }
    
    /**
    * Set Set of Valid RTE Tags
    *
    * @return	array	Set name "standard", "extended", "extended_img",
    *					"extended_table", "extended_table_img", "full"
    */
    public function setRteTagSet($a_set_name)
    {
        $this->setRteTags($this->rte_tag_set[$a_set_name]);
    }

    /**
    * Get Set of Valid RTE Tags
    *
    * @return	array	Set name "standard", "extended", "extended_img",
    *					"extended_table", "extended_table_img", "full"
    */
    public function getRteTagSet($a_set_name)
    {
        return $this->rte_tag_set[$a_set_name];
    }

    
    /**
    * RTE Tag string
    */
    public function getRteTagString()
    {
        $result = "";
        foreach ($this->getRteTags() as $tag) {
            $result .= "<$tag>";
        }
        return $result;
    }

    /**
     * Set use tags for RTE only (default is true)
     *
     * @param boolean $a_val use tags for RTE only
     */
    public function setUseTagsForRteOnly($a_val)
    {
        $this->use_tags_for_rte_only = $a_val;
    }
    
    /**
     * Get use tags for RTE only (default is true)
     *
     * @return boolean use tags for RTE only
     */
    public function getUseTagsForRteOnly()
    {
        return $this->use_tags_for_rte_only;
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
        
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");
        
        if ($this->usePurifier() && $this->getPurifier()) {
            $_POST[$this->getPostVar()] = ilUtil::stripOnlySlashes($_POST[$this->getPostVar()]);
            $_POST[$this->getPostVar()] = $this->getPurifier()->purify($_POST[$this->getPostVar()]);
        } else {
            $allowed = $this->getRteTagString();
            if ($this->plugins["latex"] == "latex" && !is_int(strpos($allowed, "<span>"))) {
                $allowed.= "<span>";
            }
            $_POST[$this->getPostVar()] = ($this->getUseRte() || !$this->getUseTagsForRteOnly())
                ? ilUtil::stripSlashes($_POST[$this->getPostVar()], true, $allowed)
                : $this->stripSlashesAddSpaceFallback($_POST[$this->getPostVar()]);
        }

        $_POST[$this->getPostVar()] = self::removeProhibitedCharacters($_POST[$this->getPostVar()]);

        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }

        if ($this->isCharLimited()) {
            //avoid whitespace surprises. #20630, #20674
            $ascii_whitespaces = chr(194) . chr(160);
            $ascii_breaklines = chr(13) . chr(10);

            $to_replace = array($ascii_whitespaces, $ascii_breaklines, "&lt;", "&gt;", "&amp;");
            $replace_to = array(' ', '', "_", "_", "_");

            #20630 mbstring extension is mandatory for 5.4
            $chars_entered = mb_strlen(strip_tags(str_replace($to_replace, $replace_to, $_POST[$this->getPostVar()])));

            if ($this->getMaxNumOfChars() && ($chars_entered > $this->getMaxNumOfChars())) {
                $this->setAlert($lng->txt("msg_input_char_limit_max"));

                return false;
            } elseif ($this->getMinNumOfChars() && ($chars_entered < $this->getMinNumOfChars())) {
                $this->setAlert($lng->txt("msg_input_char_limit_min"));

                return false;
            }
        }

        return $this->checkSubItemsInput();
    }

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $lng = $this->lng;

        $ttpl = new ilTemplate("tpl.prop_textarea.html", true, true, "Services/Form");
        
        // disabled rte
        if ($this->getUseRte() && $this->getDisabled()) {
            $ttpl->setCurrentBlock("disabled_rte");
            $ttpl->setVariable("DR_VAL", $this->getValue());
            $ttpl->parseCurrentBlock();
        } else {
            if ($this->getUseRte()) {
                $rtestring = ilRTE::_getRTEClassname();
                include_once "./Services/RTE/classes/class.$rtestring.php";
                $rte = new $rtestring($this->rteSupport['version']);

                $rte->setInitialWidth($this->getInitialRteWidth());
                
                // @todo: Check this.
                $rte->addPlugin("emotions");
                foreach ($this->plugins as $plugin) {
                    if (strlen($plugin)) {
                        $rte->addPlugin($plugin);
                    }
                }
                foreach ($this->removeplugins as $plugin) {
                    if (strlen($plugin)) {
                        $rte->removePlugin($plugin);
                    }
                }
    
                foreach ($this->buttons as $button) {
                    if (strlen($button)) {
                        $rte->addButton($button);
                    }
                }
                
                $rte->disableButtons($this->getDisabledButtons());
                
                if ($this->getRTERootBlockElement() !== null) {
                    $rte->setRTERootBlockElement($this->getRTERootBlockElement());
                }
                
                if (count($this->rteSupport) >= 3) {
                    $rte->addRTESupport($this->rteSupport["obj_id"], $this->rteSupport["obj_type"], $this->rteSupport["module"], false, $this->rteSupport['cfg_template'], $this->rteSupport['hide_switch']);
                } else {
                    // disable all plugins for mini-tagset
                    if (!array_diff($this->getRteTags(), $this->getRteTagSet("mini"))) {
                        $rte->removeAllPlugins();
                        
                        // #13603 - "paste from word" is essential
                        $rte->addPlugin("paste");
                        
                        // #11980 - p-tag is mandatory but we do not want the icons it comes with
                        $rte->disableButtons(array("anchor", "justifyleft", "justifycenter",
                            "justifyright", "justifyfull", "formatselect", "removeformat",
                            "cut", "copy", "paste", "pastetext")); // JF, 2013-12-09
                    }
                    
                    $rte->addCustomRTESupport(0, "", $this->getRteTags());
                }
                
                $ttpl->touchBlock("prop_ta_w");
                $ttpl->setCurrentBlock("prop_textarea");
                $ttpl->setVariable("ROWS", $this->getRows());
            } else {
                $ttpl->touchBlock("no_rteditor");
    
                if ($this->getCols() > 5) {
                    $ttpl->setCurrentBlock("prop_ta_c");
                    $ttpl->setVariable("COLS", $this->getCols());
                    $ttpl->parseCurrentBlock();
                } else {
                    $ttpl->touchBlock("prop_ta_w");
                }
                
                $ttpl->setCurrentBlock("prop_textarea");
                $ttpl->setVariable("ROWS", $this->getRows());
            }
            if (!$this->getDisabled()) {
                $ttpl->setVariable(
                    "POST_VAR",
                    $this->getPostVar()
                );
            }
            $ttpl->setVariable("ID", $this->getFieldId());
            if ($this->getDisabled()) {
                $ttpl->setVariable('DISABLED', 'disabled="disabled" ');
            }
            $ttpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
        
            if ($this->getRequired()) {
                $ttpl->setVariable("REQUIRED", "required=\"required\"");
            }

            if ($this->isCharLimited()) {
                $ttpl->setVariable("MAXCHARS", $this->getMaxNumOfChars());
                $ttpl->setVariable("MINCHARS", $this->getMinNumOfChars());

                $lng->toJS("form_chars_remaining");
            }

            $ttpl->parseCurrentBlock();
        }

        if ($this->isCharLimited()) {
            $ttpl->setVariable("FEEDBACK_MAX_LIMIT", $this->getMaxNumOfChars());
            $ttpl->setVariable("FEEDBACK_ID", $this->getFieldId());
            $ttpl->setVariable("CHARS_REMAINING", $lng->txt("form_chars_remaining"));
        }

        if ($this->getDisabled()) {
            $ttpl->setVariable(
                "HIDDEN_INPUT",
                $this->getHiddenTag($this->getPostVar(), $this->getValue())
            );
        }
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $ttpl->get());
        $a_tpl->parseCurrentBlock();
    }
    
    /**
    * Setter/Getter for the html purifier usage
    *
    * @param	boolean	$a_flag	Use purifier or not
    * @return	mixed	Returns instance of ilTextAreaInputGUI or boolean
    * @access	public
    */
    public function usePurifier($a_flag = null)
    {
        if (null === $a_flag) {
            return $this->usePurifier;
        }
        
        $this->usePurifier = $a_flag;
        return $this;
    }
    
    /**
    * Setter for the html purifier
    *
    * @param	ilHtmlPurifierInterface	Instance of ilHtmlPurifierInterface
    * @return	ilTextAreaInputGUI		Instance of ilTextAreaInputGUI
    * @access	public
    */
    public function setPurifier(ilHtmlPurifierInterface $Purifier)
    {
        $this->Purifier = $Purifier;
        return $this;
    }
    
    /**
    * Getter for the html purifier
    *
    * @return	ilHtmlPurifierInterface	Instance of ilHtmlPurifierInterface
    * @access	public
    */
    public function getPurifier()
    {
        return $this->Purifier;
    }
    
    /**
    * Setter for the TinyMCE root block element
    *
    * @param	string				$a_root_block_element	root block element
    * @return	ilTextAreaInputGUI	Instance of ilTextAreaInputGUI
    * @access	public
    */
    public function setRTERootBlockElement($a_root_block_element)
    {
        $this->root_block_element = $a_root_block_element;
        return $this;
    }
    
    /**
    * Getter for the TinyMCE root block element
    *
    * @return	string	Root block element of TinyMCE
    * @access	public
    */
    public function getRTERootBlockElement()
    {
        return $this->root_block_element;
    }
    
    /**
    * Sets buttons which should be disabled in TinyMCE
    *
    * @param	mixed	$a_button	Either a button string or an array of button strings
    * @return	ilTextAreaInputGUI	Instance of ilTextAreaInputGUI
    * @access	public
    *
    */
    public function disableButtons($a_button)
    {
        if (is_array($a_button)) {
            $this->disabled_buttons = array_unique(array_merge($this->disabled_buttons, $a_button));
        } else {
            $this->disabled_buttons = array_unique(array_merge($this->disabled_buttons, array($a_button)));
        }
        
        return $this;
    }
    
    /**
    * Returns the disabled TinyMCE buttons
    *
    * @param	boolean	$as_array	Should the disabled buttons be returned as a string or as an array
    * @return	Array	Array of disabled buttons
    * @access	public
    *
    */
    public function getDisabledButtons($as_array = true)
    {
        if (!$as_array) {
            return implode(',', $this->disabled_buttons);
        } else {
            return $this->disabled_buttons;
        }
    }

    /**
     * @return int
     */
    public function getInitialRteWidth()
    {
        return $this->initial_rte_width;
    }

    /**
     * @param int $initial_rte_width
     */
    public function setInitialRteWidth($initial_rte_width)
    {
        $this->initial_rte_width = $initial_rte_width;
    }

    public function isCharLimited()
    {
        if ($this->getMaxNumOfChars() || $this->getMinNumOfChars()) {
            return true;
        }

        return false;
    }
}
