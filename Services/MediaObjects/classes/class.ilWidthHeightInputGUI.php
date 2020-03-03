<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a width/height item in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesMediaObjects
*/
class ilWidthHeightInputGUI extends ilFormPropertyGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    protected $value;
    
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
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("width_height");
        $this->dirs = array("width", "height");
    }

    /**
    * Set Width.
    *
    * @param	integer	$a_width	Width
    */
    public function setWidth($a_width)
    {
        $this->width = $a_width;
    }

    /**
    * Get Width.
    *
    * @return	integer	Width
    */
    public function getWidth()
    {
        return $this->width;
    }

    /**
    * Set Height.
    *
    * @param	integer	$a_height	Height
    */
    public function setHeight($a_height)
    {
        $this->height = $a_height;
    }

    /**
    * Get Height.
    *
    * @return	integer	Height
    */
    public function getHeight()
    {
        return $this->height;
    }

    /**
    * Set Constrain Proportions.
    *
    * @param	boolean	$a_constrainproportions	Constrain Proportions
    */
    public function setConstrainProportions($a_constrainproportions)
    {
        $this->constrainproportions = $a_constrainproportions;
    }

    /**
    * Get Constrain Proportions.
    *
    * @return	boolean	Constrain Proportions
    */
    public function getConstrainProportions()
    {
        return $this->constrainproportions;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        foreach ($this->dirs as $dir) {
            $pre_value = $_POST[$this->getPostVar()][$dir] =
                ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]);
                
            /*
            if ($this->getRequired() && trim($num_value) == "")
            {
                $this->setAlert($lng->txt("msg_input_is_required"));

                return false;
            }*/
                        
            $value = $pre_value;
            
            if (trim($value) != "") {
                switch ($dir) {
                    case "width": $this->setWidth($value); break;
                    case "height": $this->setHeight($value); break;
                }
            }
        }
        
        return true;
    }

    /**
    * Insert property html
    */
    public function insert($a_tpl)
    {
        $lng = $this->lng;
        
        $tpl = new ilTemplate("tpl.prop_width_height.html", true, true, "Services/MediaObjects");

        foreach ($this->dirs as $dir) {
            switch ($dir) {
                case "width": $value = strtolower(trim($this->getWidth())); break;
                case "height": $value = strtolower(trim($this->getHeight())); break;
            }
            $tpl->setVariable("VAL_" . strtoupper($dir), $value);
        }
        if ($this->getConstrainProportions()) {
            $tpl->setVariable("CHECKED", 'checked="checked"');
        }

        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("TXT_CONSTR_PROP", $lng->txt("cont_constrain_proportions"));
        $wh_ratio = 0;
        if ((int) $this->getHeight() > 0) {
            $wh_ratio = (int) $this->getWidth() / (int) $this->getHeight();
        }
        $tpl->setVariable("WH_RATIO", str_replace(",", ".", round($wh_ratio, 6)));
        
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
        
        $GLOBALS["tpl"]->addJavascript("./Services/MediaObjects/js/ServiceMediaObjectPropWidthHeight.js");
    }

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $ilUser = $this->user;
        //var_dump($a_values[$this->getPostVar()]);
        $this->setWidth($a_values[$this->getPostVar()]["width"]);
        $this->setHeight($a_values[$this->getPostVar()]["height"]);
        $this->setConstrainProportions($a_values[$this->getPostVar()]["constr_prop"]);
    }
}
