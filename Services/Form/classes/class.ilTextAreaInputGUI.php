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

include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");

/**
* This class represents a text area property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTextAreaInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $value;
	protected $cols;
	protected $rows;
	protected $usert;
	protected $rtetags;
	
	protected $rte_tag_set = array(
		"standard" => array ("strong", "em", "u", "ol", "li", "ul", "p", "div",
			"i", "b", "code", "sup", "sub", "pre", "strike", "gap"),
		"extended" => array (
			"a","blockquote","br","cite","code","div","em","h1","h2","h3",
			"h4","h5","h6","hr","li","ol","p",
			"pre","span","strike","strong","sub","sup","u","ul",
			"i", "b", "gap"),
		"extended_img" => array (
			"a","blockquote","br","cite","code","div","em","h1","h2","h3",
			"h4","h5","h6","hr","img","li","ol","p",
			"pre","span","strike","strong","sub","sup","u","ul",
			"i", "b", "gap"),
		"extended_table" => array (
			"a","blockquote","br","cite","code","div","em","h1","h2","h3",
			"h4","h5","h6","hr","li","ol","p",
			"pre","span","strike","strong","sub","sup","table","td",
			"tr","u","ul", "i", "b", "gap"),
		"extended_table_img" => array (
			"a","blockquote","br","cite","code","div","em","h1","h2","h3",
			"h4","h5","h6","hr","img","li","ol","p",
			"pre","span","strike","strong","sub","sup","table","td",
			"tr","u","ul", "i", "b", "gap"),
		"full" => array (
			"a","blockquote","br","cite","code","div","em","h1","h2","h3",
			"h4","h5","h6","hr","img","li","ol","p",
			"pre","span","strike","strong","sub","sup","table","td",
			"tr","u","ul","ruby","rbc","rtc","rb","rt","rp", "i", "b", "gap"));
		
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("textarea");
		$this->setRteTagSet("standard");
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
	* Set Cols.
	*
	* @param	int	$a_cols	Cols
	*/
	function setCols($a_cols)
	{
		$this->cols = $a_cols;
	}

	/**
	* Get Cols.
	*
	* @return	int	Cols
	*/
	function getCols()
	{
		return $this->cols;
	}

	/**
	* Set Rows.
	*
	* @param	int	$a_rows	Rows
	*/
	function setRows($a_rows)
	{
		$this->rows = $a_rows;
	}

	/**
	* Get Rows.
	*
	* @return	int	Rows
	*/
	function getRows()
	{
		return $this->rows;
	}

	/**
	* Set Use Rich Text Editing.
	*
	* @param	int	$a_usert	Use Rich Text Editing
	*/
	function setUseRte($a_usert)
	{
		$this->usert = $a_usert;
	}

	/**
	* Get Use Rich Text Editing.
	*
	* @return	int	Use Rich Text Editing
	*/
	function getUseRte()
	{
		return $this->usert;
	}

	/**
	* Set Valid RTE Tags.
	*
	* @param	array	$a_rtetags	Valid RTE Tags
	*/
	function setRteTags($a_rtetags)
	{
		$this->rtetags = $a_rtetags;
	}

	/**
	* Get Valid RTE Tags.
	*
	* @return	array	Valid RTE Tags
	*/
	function getRteTags()
	{
		return $this->rtetags;
	}
	
	/**
	* Set Set of Valid RTE Tags
	*
	* @return	array	Set name "standard", "extended", "extended_img",
	*					"extended_table", "extended_table_img", "full"
	*/
	function setRteTagSet($a_set_name)
	{
		$this->setRteTags($this->rte_tag_set[$a_set_name]);
	}

	/**
	* Get Set of Valid RTE Tags
	*
	* @return	array	Set name "standard", "extended", "extended_img",
	*					"extended_table", "extended_table_img", "full"
	*/
	function getRteTagSet($a_set_name)
	{
		return $this->rte_tag_set[$a_set_name];
	}

	
	/**
	* RTE Tag string
	*/
	function getRteTagString()
	{
		$result = "";
		foreach ($this->getRteTags() as $tag)
		{
			$result .= "<$tag>";
		}
		return $result;
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
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		include_once("./classes/class.ilObjAdvancedEditing.php");
		
		$_POST[$this->getPostVar()] = ($this->getUseRte())
			? ilUtil::stripSlashes($_POST[$this->getPostVar()], true,
				$this->getRteTagString())
			: ilUtil::stripSlashes($_POST[$this->getPostVar()]);

		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		return $this->checkSubItemsInput();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		if ($this->getUseRte())
		{
			include_once "./Services/RTE/classes/class.ilRTE.php";
			$rtestring = ilRTE::_getRTEClassname();
			include_once "./Services/RTE/classes/class.$rtestring.php";
			$rte = new $rtestring();
			
			// @todo: Check this.
			$rte->addPlugin("emotions");
			$rte->addCustomRTESupport(0, "", $this->getRteTags());
			
			$a_tpl->touchBlock("prop_ta_w");
			$a_tpl->setCurrentBlock("prop_textarea");
			$a_tpl->setVariable("ROWS", $this->getRows());
		}
		else
		{
			$a_tpl->touchBlock("no_rteditor");
			$a_tpl->setCurrentBlock("prop_ta_c");
			$a_tpl->setVariable("COLS", $this->getCols());
			$a_tpl->parseCurrentBlock();
			
			$a_tpl->setCurrentBlock("prop_textarea");
			$a_tpl->setVariable("ROWS", $this->getRows());
		}
		$a_tpl->setVariable("POST_VAR",
			ilUtil::prepareFormOutput($this->getPostVar()));
		$a_tpl->setVariable("ID", $this->getFieldId());
		if($this->getDisabled())
		{
			$a_tpl->setVariable('DISABLED','disabled="disabled" ');
		}
		$a_tpl->setVariable("PROPERTY_VALUE", $this->getValue());
		$a_tpl->parseCurrentBlock();
	}

}
