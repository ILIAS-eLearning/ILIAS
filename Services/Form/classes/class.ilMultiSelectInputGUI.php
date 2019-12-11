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

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
* This class represents a multi selection list property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilMultiSelectInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
    protected $options;
    protected $value;
    protected $select_all; // [bool]
    protected $selected_first; // [bool]

    /**
     * Width for this field
     *
     * @access private
     * @var integer width
     */
    private $width = 160;

    /**
     * Height for this field
     *
     * @access private
     * @var integer height
     */
    private $height = 100;

    /**
     * @var string
     */
    protected $widthUnit = 'px';

    /**
     * @var string
     */
    protected $heightUnit = 'px';

    /**
     * @var array
     */
    protected $custom_attributes = array();
    
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
        $this->setType("multi_select");
        $this->setValue(array());
    }

    /**
     * Sets the width of this field
     *
     * @access public
     * @param integer $a_width
     */
    public function setWidth($a_width)
    {
        $this->width = (int) $a_width;
    }

    /**
     * Returns the width currently set for this field
     *
     * @access public
     * @return integer width
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Sets the height of this field
     *
     * @access public
     * @param integer $a_height
     */
    public function setHeight($a_height)
    {
        $this->height = (int) $a_height;
    }

    /**
     * Returns the height currently set for this field
     *
     * @access public
     * @return integer height
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
    * Set Options.
    *
    * @param	array	$a_options	Options. Array ("value" => "option_text")
    */
    public function setOptions($a_options)
    {
        $this->options = $a_options;
    }

    /**
    * Get Options.
    *
    * @return	array	Options. Array ("value" => "option_text")
    */
    public function getOptions()
    {
        return $this->options;
    }

    /**
    * Set Value.
    *
    * @param	array 		array with all activated selections
    */
    public function setValue($a_array)
    {
        $this->value = $a_array;
    }

    /**
    * Get Value.
    *
    * @return	array 		array with all activated selections
    */
    public function getValue()
    {
        return is_array($this->value) ? $this->value : array();
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }
    
    
    public function enableSelectAll($a_value)
    {
        $this->select_all = (bool) $a_value;
    }
    
    public function enableSelectedFirst($a_value)
    {
        $this->selected_first = (bool) $a_value;
    }

    
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        if (is_array($_POST[$this->getPostVar()])) {
            foreach ($_POST[$this->getPostVar()] as $k => $v) {
                $_POST[$this->getPostVar()][$k] =
                    ilUtil::stripSlashes($v);
            }
        } else {
            $_POST[$this->getPostVar()] = array();
        }
        if ($this->getRequired() && count($_POST[$this->getPostVar()]) == 0) {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }
        return true;
    }

    /**
    * Render item
    */
    public function render()
    {
        $lng = $this->lng;
        
        $tpl = new ilTemplate("tpl.prop_multi_select.html", true, true, "Services/Form");
        $values = $this->getValue();

        $options = $this->getOptions();
        if ($options) {
            if ($this->select_all) {
                // enable select all toggle
                $tpl->setCurrentBlock("item");
                $tpl->setVariable("VAL", "");
                $tpl->setVariable("ID_VAL", ilUtil::prepareFormOutput("all__toggle"));
                $tpl->setVariable("IID", $this->getFieldId());
                $tpl->setVariable("TXT_OPTION", "<em>" . $lng->txt("select_all") . "</em>");
                $tpl->setVariable("POST_VAR", $this->getPostVar());
                $tpl->parseCurrentBlock();
                
                $tpl->setVariable("TOGGLE_FIELD_ID", $this->getFieldId());
                $tpl->setVariable("TOGGLE_ALL_ID", $this->getFieldId() . "_all__toggle");
                $tpl->setVariable("TOGGLE_ALL_CBOX_ID", $this->getFieldId() . "_");
            }
            
            if ($this->selected_first) {
                // move selected values to top
                $tmp_checked = $tmp_unchecked = array();
                foreach ($options as $option_value => $option_text) {
                    if (in_array($option_value, $values)) {
                        $tmp_checked[$option_value] = $option_text;
                    } else {
                        $tmp_unchecked[$option_value] = $option_text;
                    }
                }
                $options = $tmp_checked + $tmp_unchecked;
                unset($tmp_checked);
                unset($tmp_unchecked);
            }
            
            foreach ($options as $option_value => $option_text) {
                $tpl->setCurrentBlock("item");
                if ($this->getDisabled()) {
                    $tpl->setVariable(
                        "DISABLED",
                        " disabled=\"disabled\""
                    );
                }
                if (in_array($option_value, $values)) {
                    $tpl->setVariable(
                        "CHECKED",
                        " checked=\"checked\""
                    );
                }

                $tpl->setVariable("VAL", ilUtil::prepareFormOutput($option_value));
                $tpl->setVariable("ID_VAL", ilUtil::prepareFormOutput($option_value));
                $tpl->setVariable("IID", $this->getFieldId());
                $tpl->setVariable("TXT_OPTION", $option_text);
                $tpl->setVariable("POST_VAR", $this->getPostVar());
                $tpl->parseCurrentBlock();
            }
        }
        
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("CUSTOM_ATTRIBUTES", implode(' ', $this->getCustomAttributes()));

        if ($this->getWidth()) {
            $tpl->setVariable("WIDTH", $this->getWidth() . ($this->getWidthUnit()?$this->getWidthUnit():''));
        }
        if ($this->getHeight()) {
            $tpl->setVariable("HEIGHT", $this->getHeight() . ($this->getHeightUnit()?$this->getHeightUnit():''));
        }
        
        return $tpl->get();
    }
    
    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    /**
    * Get HTML for table filter
    */
    public function getTableFilterHTML()
    {
        $html = $this->render();
        return $html;
    }


    /**
     * @return array
     */
    public function getCustomAttributes()
    {
        return $this->custom_attributes;
    }


    /**
     * @param array $custom_attributes
     */
    public function setCustomAttributes($custom_attributes)
    {
        $this->custom_attributes = $custom_attributes;
    }


    /**
     * @param array $custom_attribute
     */
    public function addCustomAttribute($custom_attribute)
    {
        $this->custom_attributes[] = $custom_attribute;
    }

    /**
     * @return string
     */
    public function getWidthUnit()
    {
        return $this->widthUnit;
    }

    /**
     * @param string $widthUnit
     */
    public function setWidthUnit($widthUnit)
    {
        $this->widthUnit = $widthUnit;
    }

    /**
     * @return string
     */
    public function getHeightUnit()
    {
        return $this->heightUnit;
    }

    /**
     * @param string $heightUnit
     */
    public function setHeightUnit($heightUnit)
    {
        $this->heightUnit = $heightUnit;
    }
}
