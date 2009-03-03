<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Color picker form for selecting color hexcodes using yui library
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesForm
*/

class ilColorPickerInputGUI extends ilTextInputGUI
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
		$this->setType("color");
		$this->setDefaultColor("04427e");
	}
	
	/**
	 * check input
	 *
	 * @access public
	 * @return
	 */
	public function checkInput()
	{
		return true;
	}
	
	/**
	 * set value
	 *
	 * @access public
	 * @param string $a_value color hexcode 
	 * @return
	 */
	public function setValue($a_value)
	{
		$this->hex = ilColorPickerInputGUI::determineHexcode($a_value);
		parent::setValue($this->getHexcode());
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
	 * get hexcode
	 *
	 * @access public
	 * @return
	 */
	public function getHexcode()
	{
		if(strpos($this->hex,'#') === 0)
		{
			return substr($this->hex,1);
		}
		return $this->hex ? $this->hex : $this->getDefaultColor();
	}
	
	/**
	* Determine hex code for a given value
	*/
	static function determineHexcode($a_value)
	{
		$a_value = trim(strtolower($a_value));

		// remove leading #
		if(strpos($a_value,'#') === 0)
		{
			$a_value = substr($a_value,1);
		}
		
		// handle named colors
		switch ($a_value)
		{
			// html4 colors
			case "black": $a_value = "000000"; break;
			case "maroon": $a_value = "800000"; break;
			case "green": $a_value = "008000"; break;
			case "olive": $a_value = "808000"; break;
			case "navy": $a_value = "000080"; break;
			case "purple": $a_value = "800080"; break;
			case "teal": $a_value = "008080"; break;
			case "silver": $a_value = "C0C0C0"; break;
			case "gray": $a_value = "808080"; break;
			case "red": $a_value = "ff0000"; break;
			case "lime": $a_value = "00ff00"; break;
			case "yellow": $a_value = "ffff00"; break;
			case "blue": $a_value = "0000ff"; break;
			case "fuchsia": $a_value = "ff00ff"; break;
			case "aqua": $a_value = "00ffff"; break;
			case "white": $a_value = "ffffff"; break;
			
			// other colors used by ILIAS, supported by modern browsers
			case "brown": $a_value = "a52a2a"; break;
		}
		
		// handle rgb values
		if (substr($a_value, 0, 3) == "rgb")
		{
			$pos1 = strpos($a_value, "(");
			$pos2 = strpos($a_value, ")");
			$rgb = explode(",", substr($a_value, $pos1 + 1, $pos2 - $pos1 - 1));
			$r = str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
			$g = str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
			$b = str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
			$a_value = $r.$g.$b;
		}
		
		$a_value = trim(strtolower($a_value));
		
		// expand three digit hex numbers
		if (preg_match("/^[0-9a-f]3/", $a_value))
		{
			$a_value = "".$a_value;
			$a_value = "0".$a_value[0]."0".$a_value[1]."0".$a_value[2];
		}
		
		if (!preg_match("/^[a-f0-9]{6}/", $a_value))
		{
			$a_value = "";
		}

		return strtoupper($a_value);
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert($a_tpl)
	{
		global $tpl;
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		
		ilYuiUtil::initColorPicker();
		
		
		$a_tpl->setCurrentBlock("prop_color");

		$js_tpl = new ilTemplate('tpl.color_picker.js',true,true,'Services/Form');
		$js_tpl->setVariable('THUMB_PATH',ilUtil::getImagePath('color_picker_thumb.png','Services/Form'));
		$js_tpl->setVariable('HUE_THUMB_PATH',ilUtil::getImagePath('color_picker_hue_thumb.png','Services/Form'));
		$js_tpl->setVariable('COLOR_ID',$this->getFieldId());
		$ic = ilColorPickerInputGUI::determineHexcode($this->getHexcode());
		if ($ic == "")
		{
			$ic = "FFFFFF";
		}
		$js_tpl->setVariable('INIT_COLOR_SHORT',$ic);
		$js_tpl->setVariable('INIT_COLOR','#'.$this->getHexcode());
		$js_tpl->setVariable('POST_VAR', $this->getPostVar());
		
		
		if($this->getDisabled())
		{
			$a_tpl->setVariable('COLOR_DISABLED','disabled="disabled"');
		}
		else
		{
			$a_tpl->setVariable('PROP_COLOR_JS',$js_tpl->get());		
		}
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("PROP_COLOR_ID", $this->getFieldId());
		$a_tpl->setVariable("PROPERTY_VALUE_COLOR",ilUtil::prepareFormOutput($this->getHexcode()));
		$a_tpl->parseCurrentBlock();
	}
	
}
?>
