<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Nested List
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup
 */
class ilNestedList
{
	var $item_class = "il_Explorer";
	var $list_class = "il_Explorer";

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __constructor()
	{
		$this->childs[0] = array();
	}

	/**
	 * Set li class
	 *
	 * @param	string	li class
	 */
	function setItemClass($a_val)
	{
		$this->item_class = $a_val;
	}

	/**
	 * Get li class
	 *
	 * @return	string	li class
	 */
	function getItemClass()
	{
		return $this->item_class;
	}

	/**
	 * Set list class
	 *
	 * @param	string	list class
	 */
	function setListClass($a_val)
	{
		$this->list_class = $a_val;
	}

	/**
	 * Get list class
	 *
	 * @return	string	list class
	 */
	function getListClass()
	{
		return $this->list_class;
	}

	/**
	 * Add list node
	 *
	 * @param
	 * @return
	 */
	function addListNode($a_content, $a_id, $a_parent = 0)
	{
		$this->nodes[$a_id] = $a_content;
		$this->childs[$a_parent][] = $a_id;
	}

	/**
	 * Get HTML
	 *
	 * @param
	 * @return
	 */
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.nested_list.html", true, true, "Services/UIComponent/NestedList");

		if (is_array($this->childs[0]) && count($this->childs[0]) > 0)
		{
			$this->listStart($tpl);
			foreach ($this->childs[0] as $child)
			{
				$this->renderNode($child, $tpl);
			}
			$this->listEnd($tpl);
		}

		return $tpl->get();
	}

	/**
	 * Render node
	 *
	 * @param
	 * @return
	 */
	function renderNode($a_id, $tpl)
	{
		$this->listItemStart($tpl);
		$tpl->setCurrentBlock("content");
		$tpl->setVariable("CONTENT", $this->nodes[$a_id]);
//echo "<br>".$this->nodes[$a_id];
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("tag");

		if (is_array($this->childs[$a_id]) && count($this->childs[$a_id]) > 0)
		{
			$this->listStart($tpl);
			foreach ($this->childs[$a_id] as $child)
			{
				$this->renderNode($child, $tpl);
			}
			$this->listEnd($tpl);
		}

		$this->listItemEnd($tpl);
	}

	/**
	 * List item start
	 *
	 * @param
	 * @return
	 */
	function listItemStart($tpl)
	{
		if ($this->getItemClass() != "")
		{
			$tpl->setCurrentBlock("list_item_start");
			$tpl->setVariable("LI_CLASS", ' class="'.$this->getItemClass().'" ');
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->touchBlock("list_item_start");
		}
		$tpl->touchBlock("tag");
	}

	/**
	 * List item end
	 *
	 * @param
	 * @return
	 */
	function listItemEnd($tpl)
	{
		$tpl->touchBlock("list_item_end");
		$tpl->touchBlock("tag");
	}

	/**
	 * List start
	 *
	 * @param
	 * @return
	 */
	function listStart($tpl)
	{
//echo "<br>listStart";
		if ($this->getListClass() != "")
		{
			$tpl->setCurrentBlock("list_start");
			$tpl->setVariable("UL_CLASS", ' class="'.$this->getListClass().'" ');
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->touchBlock("list_start");
		}
		$tpl->touchBlock("tag");
	}

	/**
	 * List end
	 *
	 * @param
	 * @return
	 */
	function listEnd($tpl)
	{
//echo "<br>listEnd";
		$tpl->touchBlock("list_end");
		$tpl->touchBlock("tag");
	}

}
?>
