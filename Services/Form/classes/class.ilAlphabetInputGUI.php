<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");
include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';

/**
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilAlphabetInputGUI extends ilFormPropertyGUI implements ilToolbarItem
{
	protected $letters;
	protected $parent_object;
	protected $parent_cmd;
	protected $highlight;
	protected $highlight_letter;

	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
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
	}

	/**
	 * Set letters available
	 *
	 * @param	array	letters
	 */
	function setLetters($a_val)
	{
		$this->letters = $a_val;
	}
	
	/**
	 * Get letters available
	 *
	 * @return	array	letters
	 */
	function getLetters()
	{
		return $this->letters;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		
		return true;
	}
	
	/**
	* Render item
	*/
	protected function render($a_mode = "")
	{
		die("only implemented for toolbar usage");
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
//	function insert(&$a_tpl)
//	{
//		$a_tpl->setCurrentBlock("prop_generic");
//		$a_tpl->setVariable("PROP_GENERIC", "zz");
//		$a_tpl->parseCurrentBlock();
//	}
	
	/**
	 * Set parent cmd
	 *
	 * @param	object	parent object
	 * @param	string	parent command
	 */
	function setParentCommand($a_obj, $a_cmd)
	{
		$this->parent_object = $a_obj;
		$this->parent_cmd = $a_cmd;
	}
	
	/**
	 * Set highlighted
	 *
	 * @param
	 * @return
	 */
	function setHighlighted($a_high_letter)
	{
		$this->highlight = true;
		$this->highlight_letter = $a_high_letter;
	}
	
	/**
	* Get HTML for toolbar
	*/
	function getToolbarHTML()
	{
		global $ilCtrl, $lng;
		
		$lng->loadLanguageModule("form");

		$tpl = new ilTemplate("tpl.prop_alphabet.html", true, true, "Services/Form");
		foreach ((array)$this->getLetters() as $l)
		{
			$tpl->setCurrentBlock("letter");
			$tpl->setVariable("TXT_LET", $l);
			$ilCtrl->setParameter($this->parent_object, "letter", rawurlencode($l));
			$tpl->setVariable("TXT_LET", $l);
			$tpl->setVariable("HREF_LET", $ilCtrl->getLinkTarget($this->parent_object, $this->parent_cmd));
			if ($this->highlight && $this->highlight_letter !== null && $this->highlight_letter == $l)
			{
				$tpl->setVariable("CLASS", ' class="ilHighlighted" ');
			}
			$tpl->parseCurrentBlock();
		}
		$ilCtrl->setParameter($this->parent_object, "letter", "");
		$tpl->setVariable("TXT_ALL", $lng->txt("form_alphabet_all"));
		$tpl->setVariable("HREF_ALL", $ilCtrl->getLinkTarget($this->parent_object, $this->parent_cmd));
		if ($this->highlight && $this->highlight_letter === null)
		{
			$tpl->setVariable("CLASSA", ' class="ilHighlighted" ');
		}
		return $tpl->get();
	}
	
}
?>