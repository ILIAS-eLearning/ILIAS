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
	protected $plugins;
	protected $removeplugins;
	protected $buttons;
	protected $rtesupport;
	protected $usePurifier = false;
	protected $Purifier = null;
	
	/**
	* Setter for the TinyMCE root block element
	*
	* @var		string
	* @type		string
	* @access	protected
	*/
	protected $root_block_element = null;
	
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
		$this->plugins = array();
		$this->removeplugins = array();
		$this->buttons = array();
		$this->rteSupport = array();
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
	* Add RTE plugin.
	*
	* @param string $a_plugin Plugin name
	*/
	function addPlugin($a_plugin)
	{
		$this->plugins[$a_plugin] = $a_plugin;
	}
	
	/**
	* Remove RTE plugin.
	*
	* @param string $a_plugin Plugin name
	*/
	function removePlugin($a_plugin)
	{
		$this->removeplugins[$a_plugin] = $a_plugin;
	}

	/**
	* Add RTE button.
	*
	* @param string $a_button Button name
	*/
	function addButton($a_button)
	{
		$this->buttons[$a_button] = $a_button;
	}
	
	/**
	* Remove RTE button.
	*
	* @param string $a_button Button name
	*/
	function removeButton($a_button)
	{
		unset($this->buttons[$a_button]);
	}

	/**
	* Set RTE support for a special module
	*
	* @param int $obj_id Object ID
	* @param string $obj_type Object Type
	* @param string $module ILIAS module
	*/
	function setRTESupport($obj_id, $obj_type, $module, $cfg_template = null)
	{
		$this->rteSupport = array("obj_id" => $obj_id, "obj_type" => $obj_type, "module" => $module, 'cfg_template' => $cfg_template);
	}
	
	/**
	* Remove RTE support for a special module
	*/
	function removeRTESupport()
	{
		$this->rteSupport = array();
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
		include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");
		
		if($this->usePurifier() && $this->getPurifier())
		{
			$_POST[$this->getPostVar()] = ilUtil::stripOnlySlashes($_POST[$this->getPostVar()]);
   			$_POST[$this->getPostVar()] = $this->getPurifier()->purify($_POST[$this->getPostVar()]);
		}
		else
		{
			$_POST[$this->getPostVar()] = ($this->getUseRte())
				? ilUtil::stripSlashes($_POST[$this->getPostVar()], true, $this->getRteTagString())
				: ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		}

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
			foreach ($this->plugins as $plugin)
			{
				if (strlen($plugin))
				{
					$rte->addPlugin($plugin);
				}
			}
			foreach ($this->removeplugins as $plugin)
			{
				if (strlen($plugin))
				{
					$rte->removePlugin($plugin);
				}
			}

			foreach ($this->buttons as $button)
			{
				if (strlen($button))
				{
					$rte->addButton($button);
				}
			}
			
			if($this->getRTERootBlockElement() !== null)
			{
				$rte->setRTERootBlockElement($this->getRTERootBlockElement());
			}
			
			if (count($this->rteSupport) >= 3)
			{
				$rte->addRTESupport($this->rteSupport["obj_id"], $this->rteSupport["obj_type"], $this->rteSupport["module"], false, $this->rteSupport['cfg_template']);
			}
			else
			{
				$rte->addCustomRTESupport(0, "", $this->getRteTags());
			}			
			
			$a_tpl->touchBlock("prop_ta_w");
			$a_tpl->setCurrentBlock("prop_textarea");
			$a_tpl->setVariable("ROWS", $this->getRows());
		}
		else
		{
			$a_tpl->touchBlock("no_rteditor");

			if ($this->getCols() > 5)
			{
				$a_tpl->setCurrentBlock("prop_ta_c");
				$a_tpl->setVariable("COLS", $this->getCols());
				$a_tpl->parseCurrentBlock();
			}
			else
			{
				$a_tpl->touchBlock("prop_ta_w");
			}
			
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
	
	public function usePurifier($a_flag = null)
	{
		if(null === $a_flag)
		{
			return $this->usePurifier;
		}
		
		$this->usePurifier = $a_flag;
		return $this;
	}
	
	/**
	* Setter for the TinyMCE root block element
	*
	* @param	ilHtmlPurifierInterface	Instance of ilHtmlPurifierInterface 
	* @return	ilRTE	instance
	* @access	public
	*/
	public function setPurifier(ilHtmlPurifierInterface $Purifier)
	{
		$this->Purifier = $Purifier;
		return $this;
	}
	
	public function getPurifier()
	{
		return $this->Purifier;
	}
	
	/**
	* Setter for the TinyMCE root block element
	*
	* @param	string	$a_root_block_element		root block element
	* @return	ilTextAreaInputGUI					This reference
	* @access	public
	*/
	public function setRTERootBlockElement($a_root_block_element)
	{
		$this->root_block_element = $a_root_block_element;
		return $this;
	}
	
	/**
	* Getter for the TinyMCE root block element
	*
	* @return	string	$a_text	root block element
	* @access	public
	*/
	public function getRTERootBlockElement()
	{
		return $this->root_block_element;
	}
}
