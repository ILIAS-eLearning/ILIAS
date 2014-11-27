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

include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';

/**
* This class represents a checkbox property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilCheckboxInputGUI extends ilSubEnabledFormPropertyGUI implements ilToolbarItem
{
	protected $value = "1";
	protected $checked;
	protected $optiontitle = "";
	protected $additional_attributes = '';
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("checkbox");
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getValue()
	{
		return $this->value;
	}

	/**
	* Set Checked.
	*
	* @param	boolean	$a_checked	Checked
	*/
	function setChecked($a_checked)
	{
		$this->checked = $a_checked;
	}

	/**
	* Get Checked.
	*
	* @return	boolean	Checked
	*/
	function getChecked()
	{
		return $this->checked;
	}

	/**
	* Set Option Title (optional).
	*
	* @param	string	$a_optiontitle	Option Title (optional)
	*/
	function setOptionTitle($a_optiontitle)
	{
		$this->optiontitle = $a_optiontitle;
	}

	/**
	* Get Option Title (optional).
	*
	* @return	string	Option Title (optional)
	*/
	function getOptionTitle()
	{
		return $this->optiontitle;
	}

	/**
	* Set value by array
	*
	* @param	object	$a_item		Item
	*/
	function setValueByArray($a_values)
	{
		$this->setChecked($a_values[$this->getPostVar()]);
		foreach($this->getSubItems() as $item)
		{
			$item->setValueByArray($a_values);
		}
	}
	
	/**
	* Set addiotional attributes
	*
	* @param	string	$a_attrs	addition attribute string
	*/
	function setAdditionalAttributes($a_attrs)
	{
		$this->additional_attributes = $a_attrs;
	}
	
	/**
	* get addtional attributes
	*
	*/
	function getAdditionalAttributes()
	{
		return $this->additional_attributes;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		
		// getRequired() is NOT processed here!

		$ok = $this->checkSubItemsInput();

		// only not ok, if checkbox not checked
		if (!$ok && $_POST[$this->getPostVar()] == "")
		{
			$ok = true;
		}

		return $ok;
	}
	
	/**
	* Sub form hidden on init?
	*
	*/
	public function hideSubForm()
	{
		return !$this->getChecked();
	}

	/**
	* Render item
	*/
	function render($a_mode = '')
	{
		$tpl = new ilTemplate("tpl.prop_checkbox.html", true, true, "Services/Form");
		
		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("ID", $this->getFieldId());
		$tpl->setVariable("PROPERTY_VALUE", $this->getValue());
		$tpl->setVariable("OPTION_TITLE", $this->getOptionTitle());
		if(strlen($this->getAdditionalAttributes()))
		{
			$tpl->setVariable('PROP_CHECK_ATTRS',$this->getAdditionalAttributes());
		}
		if ($this->getChecked())
		{
			$tpl->setVariable("PROPERTY_CHECKED",
				'checked="checked"');
		}
		if ($this->getDisabled())
		{
			$tpl->setVariable("DISABLED",
				'disabled="disabled"');
		}
		
		if ($a_mode == "toolbar")
		{
			// block-inline hack, see: http://blog.mozilla.com/webdev/2009/02/20/cross-browser-inline-block/
			// -moz-inline-stack for FF2
			// zoom 1; *display:inline for IE6 & 7
			$tpl->setVariable("STYLE_PAR", 'display: -moz-inline-stack; display:inline-block; zoom: 1; *display:inline;');
		}
		
		return $tpl->get();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	* Get HTML for table filter
	*/
	function getTableFilterHTML()
	{
		$html = $this->render();
		return $html;
	}

	/**
	* serialize data
	*/
	function serializeData()
	{
		return serialize($this->getChecked());
	}
	
	/**
	* unserialize data
	*/
	function unserializeData($a_data)
	{
		$data = unserialize($a_data);

		if ($data)
		{
			$this->setValue($data);
			$this->setChecked(true);
		}
	}
	
	/**
	 * Get HTML for toolbar
	 */
	function getToolbarHTML()
	{
		$html = $this->render('toolbar');
		return $html;
	}
}
