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

include_once("./Services/Form/classes/class.ilCheckboxOption.php");

/**
* This class represents a property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilCheckboxGroupInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $options = array();
	protected $value;
	protected $use_option_post_vars = false;
	protected $checked_by_options = false;
	protected $use_options_ids = false;


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
	 * Set use option post vars (instead of groupinput postvar)
	 *
	 * @param bool $a_val use option post vars	
	 */
	function setUseOptionsPostVars($a_val)
	{
		$this->use_option_post_vars = $a_val;
	}
	
	/**
	 * Get use option post vars (instead of groupinput postvar)
	 *
	 * @return bool use option post vars
	 */
	function getUseOptionsPostVars()
	{
		return $this->use_option_post_vars;
	}
	
	/**
	 * Set use options ids
	 *
	 * @param bool $a_val use options ids	
	 */
	function setUseOptionsIds($a_val)
	{
		$this->use_options_ids = $a_val;
	}
	
	/**
	 * Get use options ids
	 *
	 * @return bool use options ids
	 */
	function getUseOptionsIds()
	{
		return $this->use_options_ids;
	}
	
	/**
	 * Set checked by options
	 *
	 * @param bool $a_val check by options	
	 */
	function setCheckedByOptions($a_val)
	{
		$this->checked_by_options = $a_val;
	}
	
	/**
	 * Get checked by options
	 *
	 * @return bool check by options
	 */
	function getCheckedByOptions()
	{
		return $this->checked_by_options;
	}
	
	/**
	* Add Option.
	*
	* @param	object		$a_option	CheckboxOption object
	*/
	function addOption($a_option)
	{
		$this->options[] = $a_option;
	}

        /**
	* Set Options.
	*
	* @param	array	$a_options	Options. Array ("value" => "option_text")
	*/
	function setOptions($a_options)
	{
            foreach($a_options as $key => $label) {
                if (is_string($label)) {
                    $chb = new ilCheckboxInputGUI($label, $key);
                    $this->options[] = $chb;
                }
                else if ($label instanceof ilCheckboxInputGUI) {
                    $this->options[] = $label;
                }
            }
	}

	/**
	* Get Options.
	*
	* @return	array	Array of CheckboxOption objects
	*/
	function getOptions()
	{
		return $this->options;
	}

	/**
	* Set Value.
	*
	* @param	array	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	array	Value
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

		if ($this->getRequired() && count($_POST[$this->getPostVar()]) == 0)
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
				if (!$item_ok && in_array($option->getValue(), $_POST[$this->getPostVar()]))
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
		$tpl = new ilTemplate("tpl.prop_checkbox_group.html", true, true, "Services/Form");

		foreach($this->getOptions() as $option)
		{
			// information text for option
			if ($option->getInfo() != "")
			{
				$tpl->setCurrentBlock("checkbox_option_desc");
				$tpl->setVariable("CHECKBOX_OPTION_DESC", $option->getInfo());
				$tpl->parseCurrentBlock();
			}


			if (count($option->getSubItems()) > 0)
			{
				$tpl->setCurrentBlock("checkbox_option_subform");
				$pf = new ilPropertyFormGUI();
				$pf->setMode("subform");
				$pf->setItems($option->getSubItems());
				$tpl->setVariable("SUB_FORM", $pf->getContent());
				$tpl->setVariable("SOP_ID", $this->getFieldId()."_".$option->getValue());
				if ($pf->getMultipart())
				{
					$this->getParentForm()->setMultipart(true);
				}
				$tpl->parseCurrentBlock();
				if ($pf->getMultipart())
				{
					$this->getParentForm()->setMultipart(true);
				}
			}

			$tpl->setCurrentBlock("prop_checkbox_option");
			if (!$this->getUseOptionsPostVars())
			{
				$tpl->setVariable("POST_VAR", $this->getPostVar() . '[]');
			}
			else
			{
				$tpl->setVariable("POST_VAR", $option->getPostVar());
			}
			$tpl->setVariable("VAL_CHECKBOX_OPTION", $option->getValue());
			
			if (!$this->getUseOptionsIds())
			{
				$tpl->setVariable("OP_ID", $this->getFieldId()."_".$option->getValue());
				$tpl->setVariable("FID", $this->getFieldId());
			}
			else
			{
				$tpl->setVariable("OP_ID", $option->getFieldId());
				$tpl->setVariable("FID", $option->getFieldId());
			}
			
			if($this->getDisabled() or $option->getDisabled())
			{
				$tpl->setVariable('DISABLED','disabled="disabled" ');
			}
			if (!$this->getCheckedByOptions() && is_array($this->getValue()))
			{
				if (in_array($option->getValue(), $this->getValue()))
				{
					$tpl->setVariable("CHK_CHECKBOX_OPTION",
						'checked="checked"');
				}
			}
			else if ($this->getCheckedByOptions())
			{
				if ($option->getChecked())
				{
					$tpl->setVariable("CHK_CHECKBOX_OPTION",
						'checked="checked"');
				}
			}
			$tpl->setVariable("TXT_CHECKBOX_OPTION", $option->getTitle());


			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("ID", $this->getFieldId());

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
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
