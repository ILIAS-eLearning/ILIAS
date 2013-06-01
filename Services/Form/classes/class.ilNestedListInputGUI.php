<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';

/**
* This class represents a (nested) list of checkboxes (could be extended for radio items, too)
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilNestedListInputGUI extends ilFormPropertyGUI
{
	protected $value = "1";
	protected $checked;
	protected $list_nodes = array();
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("nested_list");

		include_once("./Services/UIComponent/NestedList/classes/class.ilNestedList.php");
		$this->list = new ilNestedList();
		$this->list->setListClass("il_Explorer");
	}

	/**
	 * Add list node
	 *
	 * @param
	 */
	function addListNode($a_id, $a_text, $a_parent = 0, $a_checked = false, $a_disabled = false,
		$a_img_src = "", $a_img_alt = "", $a_post_var = "")
	{
		$this->list_nodes[$a_id] = array("text" => $a_text,
			"parent" => $a_parent, "checked" => $a_checked, "disabled" => $a_disabled,
			"img_src" => $a_img_src, "img_alt" => $a_img_alt, "post_var" => $a_post_var);
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
	* @param	object	$a_item		Item
	*/
	function setValueByArray($a_values)
	{
//		$this->setChecked($a_values[$this->getPostVar()]);
//		foreach($this->getSubItems() as $item)
//		{
//			$item->setValueByArray($a_values);
//		}
	}
	
	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		return true;
	}
	
	/**
	* Render item
	*/
	function render()
	{
		foreach ($this->list_nodes as $id => $n)
		{
			if ($n["post_var"] == "")
			{
				$post_var = $this->getPostVar()."[]";
				$value = $id;
			}
			else
			{
				$post_var = $n["post_var"];
				$value = $id;
			}
			$item_html = ilUtil::formCheckbox($n["checked"], $post_var, $value,
				$n["disabled"]);
			if ($n["img_src"] != "")
			{
				$item_html.= ilUtil::img($n["img_src"], $n["img_alt"])." ";
			}
			$item_html.= $n["text"];

			$this->list->addListNode($item_html, $id, $n["parent"]);
		}

		return $this->list->getHTML();
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

}
