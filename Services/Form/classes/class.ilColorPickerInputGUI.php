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
* Color picker form for selecting color hexcodes using yui library
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesForm
*/

class ilColorPickerInputGUI extends ilTextInputGUI
{
	protected $hex = '04427e';


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
		$this->hex = $a_value;
		parent::setValue($this->getHexcode());
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
		return $this->hex;
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
		$js_tpl->setVariable('INIT_COLOR','#'.$this->getHexcode());
		$js_tpl->setVariable('INIT_COLOR_SHORT',$this->getHexcode());
		
		
		$a_tpl->setVariable('PROP_COLOR_JS',$js_tpl->get());		
		
		
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("PROP_COLOR_ID", $this->getFieldId());
		$a_tpl->setVariable("PROPERTY_VALUE_COLOR",ilUtil::prepareFormOutput($this->getHexcode()));
		$a_tpl->parseCurrentBlock();
	}
	
}
?>