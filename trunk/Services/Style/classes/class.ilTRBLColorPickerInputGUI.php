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

/**
* Color picker form for selecting color hexcodes using yui library (all/top/right/bottom/left)
*
* @author Alex Killing <killing@leifos.com>
* @version $Id$
*
* @ingroup ServicesForm
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
		parent::__construct($a_title, $a_postvar);
		$this->setType("trbl_color");
		$this->dirs = array("all", "top", "bottom", "left", "right");
	}
	
	/**
	* Set All Value.
	*
	* @param	string	$a_allvalue	All Value
	*/
	function setAllValue($a_allvalue)
	{
		$a_allvalue = trim($a_allvalue);
		if ($this->getAcceptNamedColors() && substr($a_allvalue, 0, 1) == "!")
		{
			$this->allvalue = $a_allvalue;
		}
		else
		{
			$this->allvalue = ilColorPickerInputGUI::determineHexcode($a_allvalue);
		}
	}

	/**
	* Get All Value.
	*
	* @return	string	All Value
	*/
	function getAllValue()
	{
		return $this->allvalue;
	}

	/**
	* Set Top Value.
	*
	* @param	string	$a_topvalue	Top Value
	*/
	function setTopValue($a_topvalue)
	{
		$a_topvalue = trim($a_topvalue);
		if ($this->getAcceptNamedColors() && substr($a_topvalue, 0, 1) == "!")
		{
			$this->topvalue = $a_topvalue;
		}
		else
		{
			$this->topvalue = ilColorPickerInputGUI::determineHexcode($a_topvalue);
		}
	}

	/**
	* Get Top Value.
	*
	* @return	string	Top Value
	*/
	function getTopValue()
	{
		return $this->topvalue;
	}

	/**
	* Set Bottom Value.
	*
	* @param	string	$a_bottomvalue	Bottom Value
	*/
	function setBottomValue($a_bottomvalue)
	{
		$a_bottomvalue = trim($a_bottomvalue);
		if ($this->getAcceptNamedColors() && substr($a_bottomvalue, 0, 1) == "!")
		{
			$this->bottomvalue = $a_bottomvalue;
		}
		else
		{
			$this->bottomvalue = ilColorPickerInputGUI::determineHexcode($a_bottomvalue);
		}
	}

	/**
	* Get Bottom Value.
	*
	* @return	string	Bottom Value
	*/
	function getBottomValue()
	{
		return $this->bottomvalue;
	}

	/**
	* Set Left Value.
	*
	* @param	string	$a_leftvalue	Left Value
	*/
	function setLeftValue($a_leftvalue)
	{
		$a_leftvalue = trim($a_leftvalue);
		if ($this->getAcceptNamedColors() && substr($a_leftvalue, 0, 1) == "!")
		{
			$this->leftvalue = $a_leftvalue;
		}
		else
		{
			$this->leftvalue = ilColorPickerInputGUI::determineHexcode($a_leftvalue);
		}
	}

	/**
	* Get Left Value.
	*
	* @return	string	Left Value
	*/
	function getLeftValue()
	{
		return $this->leftvalue;
	}

	/**
	* Set Right Value.
	*
	* @param	string	$a_rightvalue	Right Value
	*/
	function setRightValue($a_rightvalue)
	{
		$a_rightvalue = trim($a_rightvalue);
		if ($this->getAcceptNamedColors() && substr($a_rightvalue, 0, 1) == "!")
		{
			$this->rightvalue = $a_rightvalue;
		}
		else
		{
			$this->rightvalue = ilColorPickerInputGUI::determineHexcode($a_rightvalue);
		}
	}

	/**
	* Get Right Value.
	*
	* @return	string	Right Value
	*/
	function getRightValue()
	{
		return $this->rightvalue;
	}

	/**
	* Set Default Color.
	*
	* @param	mixed	$a_defaultcolor	Default Color
	*/
	function setDefaultColor($a_defaultcolor)
	{
		$this->defaultcolor = $a_defaultcolor;
	}

	/**
	* Get Default Color.
	*
	* @return	mixed	Default Color
	*/
	function getDefaultColor()
	{
		return $this->defaultcolor;
	}

	/**
	* Set Accept Named Colors (Leading '!').
	*
	* @param	boolean	$a_acceptnamedcolors	Accept Named Colors (Leading '!')
	*/
	function setAcceptNamedColors($a_acceptnamedcolors)
	{
		$this->acceptnamedcolors = $a_acceptnamedcolors;
	}

	/**
	* Get Accept Named Colors (Leading '!').
	*
	* @return	boolean	Accept Named Colors (Leading '!')
	*/
	function getAcceptNamedColors()
	{
		return $this->acceptnamedcolors;
	}

	/**
	 * check input
	 *
	 * @access public
	 * @return
	 */
	public function checkInput()
	{
		foreach ($this->dirs as $dir)
		{
			$value = $_POST[$this->getPostVar()][$dir]["value"] = 
				ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]["value"]);

			if (trim($value) != "")
			{
				switch ($dir)
				{
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
	*
	* @return	int	Size
	*/
	function insert($a_tpl)
	{
		global $lng;
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initColorPicker();
		
		$layout_tpl = new ilTemplate("tpl.prop_trbl_layout.html", true, true, "Services/Style");
		
		$funcs = array(
			"all" => "getAllValue", "top" => "getTopValue",
			"bottom" => "getBottomValue", "left" => "getLeftValue",
			"right" => "getRightValue");
		
		foreach ($this->dirs as $dir)
		{
			/*switch($dir)
			{
				case "all": $value = strtoupper(trim($this->getAllValue())); break;
				case "top": $value = strtoupper(trim($this->getTopValue())); break;
				case "bottom": $value = strtoupper(trim($this->getBottomValue())); break;
				case "left": $value = strtoupper(trim($this->getLeftValue())); break;
				case "right": $value = strtoupper(trim($this->getRightValue())); break;
			}*/
			$value = trim($this->$funcs[$dir]());
			if (!$this->getAcceptNamedColors() || substr($value, 0, 1) != "!")
			{
				$value = strtoupper($value);
			}

			$ctpl = new ilTemplate("tpl.prop_trbl_color.html", true, true, "Services/Style");

			$js_tpl = new ilTemplate('tpl.trbl_color_picker.js',true,true,'Services/Style');
			$js_tpl->setVariable('THUMB_PATH',ilUtil::getImagePath('color_picker_thumb.png','Services/Form'));
			$js_tpl->setVariable('HUE_THUMB_PATH',ilUtil::getImagePath('color_picker_hue_thumb.png','Services/Form'));
			$js_tpl->setVariable('COLOR_ID',$this->getFieldId()."_".$dir);
			$ic = ilColorPickerInputGUI::determineHexcode($value);
			if ($ic == "")
			{
				$ic = "FFFFFF";
			}
			$js_tpl->setVariable('INIT_COLOR_SHORT',$ic);
			$js_tpl->setVariable('INIT_COLOR','#'.$value);
			$js_tpl->setVariable('POST_VAR', $this->getPostVar());
			$js_tpl->setVariable('DIR', $dir);
		
		
			if($this->getDisabled())
			{
				$ctpl->setVariable('COLOR_DISABLED','disabled="disabled"');
			}
			else
			{
				$ctpl->setVariable('PROP_COLOR_JS',$js_tpl->get());		
			}
			$ctpl->setVariable("POST_VAR", $this->getPostVar());
			$ctpl->setVariable("PROP_COLOR_ID", $this->getFieldId()."_".$dir);
			
			$ctpl->setVariable("PROPERTY_VALUE_COLOR", $value);
			$ctpl->setVariable("DIR", $dir);
			$ctpl->setVariable("TXT_DIR", $lng->txt("sty_$dir"));
			
			$layout_tpl->setVariable(strtoupper($dir), $ctpl->get());
		}

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $layout_tpl->get());
		$a_tpl->parseCurrentBlock();
		
	}
	
}
?>