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

include_once("./Services/Form/classes/class.ilRadioOption.php");

/**
* This class represents a property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilRadioGroupInputGUI extends ilFormPropertyGUI
{
	protected $options = array();
	protected $value;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("radio");
	}

	/**
	* Add Option.
	*
	* @param	object		$a_option	RadioOption object
	*/
	function addOption($a_option)
	{
		$this->options[] = $a_option;
	}

	/**
	* Get Options.
	*
	* @return	array	Array of RadioOption objects
	*/
	function getOptions()
	{
		return $this->options;
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
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
		foreach($this->getOptions() as $option)
		{
			foreach($option->getSubItems() as $item)
			{
				$item->setValueByArray($a_values);
			}
		}
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
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		
		$ok = true;
		foreach($this->getOptions() as $option)
		{
			foreach($option->getSubItems() as $item)
			{
				$item_ok = $item->checkInput();
				if (!$item_ok && ($_POST[$this->getPostVar()] == $option->getValue()))
				{
					$ok = false;
				}
			}
		}
		return $ok;

	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		foreach($this->getOptions() as $option)
		{
			// information text for option
			if ($option->getInfo() != "")
			{
				$a_tpl->setCurrentBlock("radio_option_desc");
				$a_tpl->setVariable("RADIO_OPTION_DESC", $option->getInfo());
				$a_tpl->parseCurrentBlock();
			}
			
			
			if (count($option->getSubItems()) > 0)
			{
				if ($option->getValue() != $this->getValue())
				{
					$a_tpl->touchBlock("prop_radio_opt_hide");
					$a_tpl->setVariable("HOP_ID", $this->getFieldId()."_".$option->getValue());
					$a_tpl->parseCurrentBlock();
				}
				$a_tpl->setCurrentBlock("prop_radio_option_subform");
				$pf = new ilPropertyFormGUI();
				$pf->setMode("subform");
				$pf->setItems($option->getSubItems());
				$a_tpl->setVariable("SUB_FORM", $pf->getContent());
				$a_tpl->setVariable("SOP_ID", $this->getFieldId()."_".$option->getValue());
				if ($pf->getMultipart())
				{
					$this->getParentForm()->setMultipart(true);
				}
				$a_tpl->parseCurrentBlock();
			}

			$a_tpl->setCurrentBlock("prop_radio_option");
			$a_tpl->setVariable("POST_VAR", $this->getPostVar());
			$a_tpl->setVariable("VAL_RADIO_OPTION", $option->getValue());
			$a_tpl->setVariable("OP_ID", $this->getFieldId()."_".$option->getValue());
			$a_tpl->setVariable("ID", $this->getFieldId());
			if($this->getDisabled() or $option->getDisabled())
			{
				$a_tpl->setVariable('DISABLED','disabled="disabled" ');
			}
			if ($option->getValue() == $this->getValue())
			{
				$a_tpl->setVariable("CHK_RADIO_OPTION",
					'checked="checked"');
			}
			$a_tpl->setVariable("TXT_RADIO_OPTION", $option->getTitle());
			
			
			$a_tpl->parseCurrentBlock();
		}
		$a_tpl->setCurrentBlock("prop_radio");
		$a_tpl->setVariable("ID", $this->getFieldId());
		$a_tpl->parseCurrentBlock();

	}

	/**
	* Get item by post var
	*
	* @return	mixed	false or item object
	*/
	function getItemByPostVar($a_post_var)
	{
		if ($this->getPostVar() == $a_post_var)
		{
			return $this;
		}

		foreach($this->getOptions() as $option)
		{
			foreach($option->getSubItems() as $item)
			{
				if ($item->getType() != "section_header")
				{
					$ret = $item->getItemByPostVar($a_post_var);
					if (is_object($ret))
					{
						return $ret;
					}
				}
			}
		}
		
		return false;
	}

}
