<?php
// cat-tms-patch start

/* Copyright (c) 2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");

/**
* This class represents a groupable selection list property in a property form.
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de> 
*/
class ilGroupableSelectInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $cust_attr = array();
	protected $groups = array();
	protected $value;
	protected $length;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("select");
		$this->length = 0;
	}

	/**
	* Set Groups.
	*
	* Key => Title of group
	* Value => Options for group
	*
	* @param array<string, array<mixed, string> 	$groups
	*
	* @return void
	*/
	function setGroups($groups)
	{
		$this->groups = $groups;
	}

	/**
	* Get Groups.
	*
	* @return array<string, array<mixed, string> 	$groups
	*/
	function getGroups()
	{
		return $this->groups;
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
	 * Get max text length.
	 *
	 * @return int
	 */
	public function getTextLength()
	{
		return $this->length;
	}

	/**
	 * Set max text length.
	 *
	 * @param 	int 	$length
	 * @return 	void
	 */
	public function setTextLength($length)
	{
		assert('is_int($length)');
		$this->length = $length;
	}

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
		foreach($this->getSubItems() as $item)
		{
			$item->setValueByArray($a_values);
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

		$valid = true;
		$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);

		if($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$valid = false;
		}

		if(!array_key_exists($_POST[$this->getPostVar()], (array) $this->getOptions()))
		{
			$this->setAlert($lng->txt('msg_invalid_post_input'));
			return false;
		}

		if (!$valid)
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
			return false;
		}

		return $this->checkSubItemsInput();
	}
	
	public function addCustomAttribute($a_attr)
	{
		$this->cust_attr[] = $a_attr;
	}
	
	public function getCustomAttributes()
	{
		return (array) $this->cust_attr;
	}

	/**
	* Render item
	*/
	function render($a_mode = "")
	{
		$tpl = new ilTemplate("tpl.prop_group_select.html", true, true, "Services/Form");
		$postvar = $this->getPostVar();

		$tpl->setVariable("ID", $this->getFieldId());

		if ($this->getDisabled())
		{
			$hidden = $this->getHiddenTag($postvar, $this->getValue());
			if($hidden)
			{
				$tpl->setVariable("DISABLED", " disabled=\"disabled\"");
				$tpl->setVariable("HIDDEN_INPUT", $hidden);
			}
		}
		else
		{
			$tpl->setVariable("POST_VAR", $postvar);
		}

		foreach($this->getCustomAttributes() as $attr)
		{
			$tpl->setCurrentBlock('cust_attr');
			$tpl->setVariable('CUSTOM_ATTR',$attr);
			$tpl->parseCurrentBlock();
		}

		foreach($this->getGroups() as $group_title => $options)
		{
			foreach($options as $value => $option_title) {
				$adjusted_option_title = htmlentities($this->getAdjustTitle($option_title));
				$option_title = htmlentities($option_title);

				$tpl->setCurrentBlock("prop_select_option");
				$tpl->setVariable("VAL_SELECT_OPTION", $value);
				$tpl->setVariable("TXT_SELECT_OPTION_TITLE", $option_title);
				$tpl->setVariable("TXT_SELECT_OPTION", $option_title);
				if($this->getTextLength() > 0) {
					$tpl->setVariable("TXT_SELECT_OPTION", $adjusted_option_title);
				}
				if((string) $value == (string) $this->getValue())
				{
					$tpl->setVariable("CHK_SEL_OPTION", 'selected="selected"');
				}
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("prop_groupstart");
			$tpl->setVariable("HEADER", htmlentities($this->getAdjustTitle($group_title)));
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * Get a title adjust by length property.
	 *
	 * @param 	string 	$title
	 * @return 	string
	 */
	private function getAdjustTitle($title)
	{
		assert('is_string($title)');
		if($this->getTextLength() > 0 && strlen($title) >= $this->getTextLength()) {
			return substr($title, 0, $this->getTextLength())."...";
		}
		return $title;
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert($a_tpl)
	{
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $this->render());
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Set initial sub form visibility, optionally add dynamic value-based condition
	 * 
	 * @see ilObjBookingPoolGUI
	 * @param bool $a_value
	 * @param string $a_condition
	 */
	function setHideSubForm($a_value, $a_condition = null)
	{
		$this->hide_sub = (bool)$a_value;
		
		if($a_condition)
		{
			$this->addCustomAttribute('onchange="if(this.value '.$a_condition.')'.
				' { il.Form.showSubForm(\'subform_'.$this->getFieldId().'\', \'il_prop_cont_'.$this->getFieldId().'\'); }'.
				' else { il.Form.hideSubForm(\'subform_'.$this->getFieldId().'\'); };"');
		}
	}

	function hideSubForm()
	{
		return (bool)$this->hide_sub;
	}

}

// cat-tms-patch end