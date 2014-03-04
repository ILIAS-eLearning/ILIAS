<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Input GUI for the configuration of select input elements. E.g course custum field, 
 * udf field, ...
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup	ServicesForm
 */
class ilSelectBuilderInputGUI extends ilTextWizardInputGUI
{
	
	protected $open_answer_indexes = array();
	
	
	// constructor
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
	}
	
	
	/**
	 * Get open answer indexes
	 */
	public function getOpenAnswerIndexes()
	{
		return $this->open_answer_indexes;
	}
	
	/**
	 * Set open answer indexes
	 */
	public function setOpenAnswerIndexes($a_indexes)
	{
		$this->open_answer_indexes = $a_indexes;
	}
	
	/**
	 * Mark an index as open answer
	 */
	public function addOpenAnswerIndex($a_idx)
	{
		$this->open_answer_indexes[] = $a_idx;
	}
	
	/**
	 * Check if an index is an open answer index
	 * @param type $a_idx
	 * @return type
	 */
	public function isOpenAnswerIndex($a_idx)
	{
		return in_array($a_idx,(array) $this->open_answer_indexes);
	}

		/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	public function checkInput()
	{
		global $lng;
		
		$foundvalues = $_POST[$this->getPostVar()];
		
		
		
		$this->setOpenAnswerIndexes(array());
		if (is_array($foundvalues))
		{
			foreach ($foundvalues as $idx => $value)
			{
				$_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
				if ($this->getRequired() && trim($value) == "")
				{
					$this->setAlert($lng->txt("msg_input_is_required"));

					return false;
				}
				else if (strlen($this->getValidationRegexp()))
				{
					if (!preg_match($this->getValidationRegexp(), $value))
					{
						$this->setAlert($lng->txt("msg_wrong_format"));
						return FALSE;
					}
				}
			}
		}
		else
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
			return FALSE;
		}
		
		foreach((array) $_POST[$this->getPostVar().'_open'] as $oindex => $ovalue)
		{
			$this->addOpenAnswerIndex($oindex);
		}
		
		
		return $this->checkSubItemsInput();
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	public function insert($a_tpl)
	{
		$tpl = new ilTemplate("tpl.prop_selectbuilder.html", true, true, "Services/Form");
		$i = 0;
		foreach ($this->values as $value)
		{
			if(!is_string($value))
			{
				continue;
			}
			
			if (strlen((string) $value))
			{
				$tpl->setCurrentBlock("prop_text_propval");
				$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput((string) $value));
				$tpl->parseCurrentBlock();
			}
			if ($this->getAllowMove())
			{
				$tpl->setCurrentBlock("move");
				$tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
				$tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
				$tpl->setVariable("ID", $this->getFieldId() . "[$i]");
				$tpl->setVariable("UP_BUTTON", ilUtil::getImagePath('a_up.png'));
				$tpl->setVariable("DOWN_BUTTON", ilUtil::getImagePath('a_down.png'));
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("row");
			$class = ($i % 2 == 0) ? "even" : "odd";
			if ($i == 0) $class .= " first";
			if ($i == count($this->values)-1) $class .= " last";
			$tpl->setVariable("ROW_CLASS", $class);
			$tpl->setVariable("POST_VAR", $this->getPostVar() . "[$i]");
			#$tpl->setVariable('POST_VAR_OPEN',$this->getPostVar().'[open]'.'['.$i.']');
			$tpl->setVariable('POST_VAR_OPEN',$this->getPostVar().'_open'.'['.$i.']');
			$tpl->setVariable('POST_VAR_OPEN_ID', $this->getPostVar().'_open['.$i.']');
			
			if($this->isOpenAnswerIndex($i))
			{
				$tpl->setVariable('PROP_OPEN_CHECKED','checked="checked"');
			}
			if($this->getDisabled())
			{
				$tpl->setVariable('PROP_OPEN_DISABLED','disabled="disabled"');
			}
			
			$tpl->setVariable("ID", $this->getFieldId() . "[$i]");
			$tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
			$tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
			$tpl->setVariable("SIZE", $this->getSize());
			$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
			if ($this->getDisabled())
			{
				$tpl->setVariable("DISABLED",
					" disabled=\"disabled\"");
			}
			$tpl->setVariable("ADD_BUTTON", ilUtil::getImagePath('edit_add.png'));
			$tpl->setVariable("REMOVE_BUTTON", ilUtil::getImagePath('edit_remove.png'));
			$tpl->parseCurrentBlock();
			$i++;
		}
		$tpl->setVariable("ELEMENT_ID", $this->getFieldId());

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
		
		global $tpl;
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initDomEvent();
		$tpl->addJavascript("./Services/Form/templates/default/textwizard.js");
	}
	
	
}
