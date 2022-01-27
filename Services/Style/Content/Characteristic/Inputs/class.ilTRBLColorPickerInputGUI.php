<?php
/**
 * Color picker form for selecting color hexcodes using yui library (all/top/right/bottom/left)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTRBLColorPickerInputGUI extends ilTextInputGUI
{
    protected $hex;


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
        $this->setType("trbl_color");
        $this->dirs = array("all", "top", "bottom", "left", "right");
    }
    
    /**
    * Set All Value.
    *
    * @param	string	$a_allvalue	All Value
    */
    public function setAllValue($a_allvalue)
    {
        $a_allvalue = trim($a_allvalue);
        if ($this->getAcceptNamedColors() && substr($a_allvalue, 0, 1) == "!") {
            $this->allvalue = $a_allvalue;
        } else {
            $this->allvalue = ilColorPickerInputGUI::determineHexcode($a_allvalue);
        }
    }

    /**
    * Get All Value.
    *
    * @return	string	All Value
    */
    public function getAllValue()
    {
        return $this->allvalue;
    }

    /**
    * Set Top Value.
    *
    * @param	string	$a_topvalue	Top Value
    */
    public function setTopValue($a_topvalue)
    {
        $a_topvalue = trim($a_topvalue);
        if ($this->getAcceptNamedColors() && substr($a_topvalue, 0, 1) == "!") {
            $this->topvalue = $a_topvalue;
        } else {
            $this->topvalue = ilColorPickerInputGUI::determineHexcode($a_topvalue);
        }
    }

    /**
    * Get Top Value.
    *
    * @return	string	Top Value
    */
    public function getTopValue()
    {
        return $this->topvalue;
    }

    /**
    * Set Bottom Value.
    *
    * @param	string	$a_bottomvalue	Bottom Value
    */
    public function setBottomValue($a_bottomvalue)
    {
        $a_bottomvalue = trim($a_bottomvalue);
        if ($this->getAcceptNamedColors() && substr($a_bottomvalue, 0, 1) == "!") {
            $this->bottomvalue = $a_bottomvalue;
        } else {
            $this->bottomvalue = ilColorPickerInputGUI::determineHexcode($a_bottomvalue);
        }
    }

    /**
    * Get Bottom Value.
    *
    * @return	string	Bottom Value
    */
    public function getBottomValue()
    {
        return $this->bottomvalue;
    }

    /**
    * Set Left Value.
    *
    * @param	string	$a_leftvalue	Left Value
    */
    public function setLeftValue($a_leftvalue)
    {
        $a_leftvalue = trim($a_leftvalue);
        if ($this->getAcceptNamedColors() && substr($a_leftvalue, 0, 1) == "!") {
            $this->leftvalue = $a_leftvalue;
        } else {
            $this->leftvalue = ilColorPickerInputGUI::determineHexcode($a_leftvalue);
        }
    }

    /**
    * Get Left Value.
    *
    * @return	string	Left Value
    */
    public function getLeftValue()
    {
        return $this->leftvalue;
    }

    /**
    * Set Right Value.
    *
    * @param	string	$a_rightvalue	Right Value
    */
    public function setRightValue($a_rightvalue)
    {
        $a_rightvalue = trim($a_rightvalue);
        if ($this->getAcceptNamedColors() && substr($a_rightvalue, 0, 1) == "!") {
            $this->rightvalue = $a_rightvalue;
        } else {
            $this->rightvalue = ilColorPickerInputGUI::determineHexcode($a_rightvalue);
        }
    }

    /**
    * Get Right Value.
    *
    * @return	string	Right Value
    */
    public function getRightValue()
    {
        return $this->rightvalue;
    }

    /**
    * Set Default Color.
    *
    * @param	mixed	$a_defaultcolor	Default Color
    */
    public function setDefaultColor($a_defaultcolor)
    {
        $this->defaultcolor = $a_defaultcolor;
    }

    /**
    * Get Default Color.
    *
    * @return	mixed	Default Color
    */
    public function getDefaultColor()
    {
        return $this->defaultcolor;
    }

    /**
    * Set Accept Named Colors (Leading '!').
    *
    * @param	boolean	$a_acceptnamedcolors	Accept Named Colors (Leading '!')
    */
    public function setAcceptNamedColors($a_acceptnamedcolors)
    {
        $this->acceptnamedcolors = $a_acceptnamedcolors;
    }

    /**
    * Get Accept Named Colors (Leading '!').
    *
    * @return	boolean	Accept Named Colors (Leading '!')
    */
    public function getAcceptNamedColors()
    {
        return $this->acceptnamedcolors;
    }

    /**
     * check input
     * @access public
     * @return bool
     */
    public function checkInput() : bool
    {
        foreach ($this->dirs as $dir) {
            $value = $_POST[$this->getPostVar()][$dir]["value"] =
                ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]["value"]);

            if (trim($value) != "") {
                switch ($dir) {
                    case "all": $this->setAllValue($value); break;
                    case "top": $this->setTopValue($value); break;
                    case "bottom": $this->setBottomValue($value); break;
                    case "left": $this->setLeftValue($value); break;
                    case "right": $this->setRightValue($value); break;
                }
            }
        }
        return true;
    }
    
    /**
    * Insert property html
    * @return	void	Size
    */
    public function insert(ilTemplate $a_tpl) : void
    {
        $lng = $this->lng;
        
        $layout_tpl = new ilTemplate("tpl.prop_trbl_layout.html", true, true, "Services/Style/Content");
        
        $funcs = array(
            "all" => "getAllValue", "top" => "getTopValue",
            "bottom" => "getBottomValue", "left" => "getLeftValue",
            "right" => "getRightValue");
        
        foreach ($this->dirs as $dir) {
            $f = $funcs[$dir];
            $value = trim($this->$f());
            if (!$this->getAcceptNamedColors() || substr($value, 0, 1) != "!") {
                $value = strtoupper($value);
            }

            $tpl = new ilTemplate('tpl.prop_color.html', true, true, 'Services/Form');
            $tpl->setVariable('COLOR_ID', $this->getFieldId() . "_" . $dir);
            $ic = ilColorPickerInputGUI::determineHexcode($value);
            if ($ic == "") {
                $ic = "FFFFFF";
            }
            $tpl->setVariable('INIT_COLOR_SHORT', $ic);
            $tpl->setVariable('POST_VAR', $this->getPostVar());

            if ($this->getDisabled()) {
                $a_tpl->setVariable('COLOR_DISABLED', 'disabled="disabled"');
            }

            $tpl->setVariable("POST_VAR", $this->getPostVar() . "[" . $dir . "][value]");
            $tpl->setVariable("PROP_COLOR_ID", $this->getFieldId() . "_" . $dir);

            if (substr(trim($this->getValue()), 0, 1) == "!" && $this->getAcceptNamedColors()) {
                $tpl->setVariable("PROPERTY_VALUE_COLOR", ilUtil::prepareFormOutput(trim($this->getValue())));
            } else {
                $tpl->setVariable("PROPERTY_VALUE_COLOR", ilUtil::prepareFormOutput($value));
                $tpl->setVariable('INIT_COLOR', '#' . $value);
            }

            $tpl->setVariable("TXT_PREFIX", $lng->txt("sty_$dir"));

            $layout_tpl->setVariable(strtoupper($dir), $tpl->get());
        }

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $layout_tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
