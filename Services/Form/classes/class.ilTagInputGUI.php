<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* This class represents a tag list property in a property form.
*
* @author Guido Vollbach <gvollbach@databay.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTagInputGUI extends ilSubEnabledFormPropertyGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $options 					= array();
    protected $max_tags					= 0;
    protected $max_chars				= 0;
    protected $allow_duplicates			= false;
    protected $js_self_init				= true;
    
    protected $type_ahead 				= false;
    protected $type_ahead_ignore_case	= true;
    protected $type_ahead_list			= array();
    protected $type_ahead_min_length	= 2;
    protected $type_ahead_limit 		= 30;
    protected $type_ahead_highlight 	= true;

    /**
     * @param int $max_tags
     */
    public function setMaxTags($max_tags)
    {
        $this->max_tags = $max_tags;
    }
    
    /**
     * @param int $max_chars
     */
    public function setMaxChars($max_chars)
    {
        $this->max_chars = $max_chars;
    }

    /**
     * @param boolean $allow_duplicates
     */
    public function setAllowDuplicates($allow_duplicates)
    {
        $this->allow_duplicates = $allow_duplicates;
    }

    /**
     * @param boolean $js_self_init
     */
    public function setJsSelfInit($js_self_init)
    {
        $this->js_self_init = $js_self_init;
    }
    
    /**
     * @param boolean $type_ahead
     */
    public function setTypeAhead($type_ahead)
    {
        $this->type_ahead = $type_ahead;
    }
    
    /**
     * @param boolean $type_ahead_ignore_case
     */
    public function setTypeAheadIgnoreCase($type_ahead_ignore_case)
    {
        $this->type_ahead_ignore_case = $type_ahead_ignore_case;
    }
    
    /**
     * @param int $min_length
     */
    public function setTypeAheadMinLength($min_length)
    {
        $this->type_ahead_min_length = $min_length;
    }
    
    /**
     * @param int $limit
     */
    public function setTypeAheadLimit($limit)
    {
        $this->type_ahead_limit = $limit;
    }
    
    /**
     * @param boolean $highlight
     */
    public function setTypeAheadHighlight($highlight)
    {
        $this->type_ahead_highlight = $highlight;
    }

    /**
     * @param array $type_ahead_list
     */
    public function setTypeAheadList($type_ahead_list)
    {
        $this->type_ahead_list = $type_ahead_list;
    }

    /**
     * Set Options.
     *
     * @param	array	$a_options	Options.
     */
    public function setOptions($a_options)
    {
        $this->options = $a_options;
    }

    /**
     * Get Options.
     *
     * @return	array	Options. Array
     */
    public function getOptions()
    {
        return $this->options ? $this->options : array();
    }
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("tag_input");
        $tpl = $DIC["tpl"];
        $tpl->addJavaScript('./Services/Form/js/bootstrap-tagsinput_2015_25_03.js');
        $tpl->addJavaScript('./Services/Form/js/typeahead_0.11.1.js');
        $tpl->addCss('./Services/Form/css/bootstrap-tagsinput_2015_25_03.css');
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setOptions($a_values[$this->getPostVar()]);
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

        $valid = true;
        if (array_key_exists($this->getPostVar(), $_POST)) {
            foreach ($_POST[$this->getPostVar()] as $idx => $value) {
                $_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
            }
            $_POST[$this->getPostVar()] = array_unique($_POST[$this->getPostVar()]);

            if ($this->getRequired() && !trim(implode("", $_POST[$this->getPostVar()]))) {
                $valid = false;
            }
        } elseif ($this->getRequired()) {
            $valid = false;
        }
        if (!$valid) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return $this->checkSubItemsInput();
    }

    /**
     * @param string    $a_mode
     * @return string
     */
    public function render($a_mode = "")
    {
        if ($this->type_ahead) {
            $tpl = new ilTemplate("tpl.prop_tag_typeahead.html", true, true, "Services/Form");
            $tpl->setVariable("MIN_LENGTH", $this->type_ahead_min_length);
            $tpl->setVariable("LIMIT", $this->type_ahead_limit);
            $tpl->setVariable("HIGHLIGHT", $this->type_ahead_highlight);
            if ($this->type_ahead_ignore_case) {
                $tpl->setVariable("CASE", 'i');
            }
            $tpl->setVariable("TERMS", json_encode($this->type_ahead_list));
        } else {
            $tpl = new ilTemplate("tpl.prop_tag.html", true, true, "Services/Form");
        }

        $tpl->setVariable("MAXTAGS", $this->max_tags);
        $tpl->setVariable("MAXCHARS", $this->max_chars);
        $tpl->setVariable("ALLOW_DUPLICATES", $this->allow_duplicates);
        
        foreach ($this->getOptions() as $option_value => $option_text) {
            $tpl->setCurrentBlock("prop_select_option");
            $tpl->setVariable("VAL_SELECT_OPTION", ilUtil::prepareFormOutput($option_text));
            $tpl->setVariable("TXT_SELECT_OPTION", $option_text);
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable("ID", $this->getFieldId());
                    
        $tpl->setVariable("POST_VAR", $this->getPostVar() . "[]");
        
        if ($this->js_self_init) {
            $tpl->setCurrentBlock("initialize_on_page_load");
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("ID", $this->getFieldId());
        return $tpl->get();
    }

    /**
     * @param $a_tpl
     */
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }
}
