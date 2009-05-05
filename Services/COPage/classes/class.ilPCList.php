<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCList
*
* List content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCList extends ilPageContent
{
	var $list_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("list");
	}

	/**
	* Set pc node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->list_node =& $a_node->first_child();		// this is the Table node
	}

	/**
	* Create new list
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->list_node =& $this->dom->create_element("List");
		$this->list_node =& $this->node->append_child($this->list_node);
	}

	/**
	* Add a number of items to list
	*/
	function addItems($a_nr)
	{
		for ($i=1; $i<=$a_nr; $i++)
		{
			$new_item =& $this->dom->create_element("ListItem");
			$new_item =& $this->list_node->append_child($new_item);
		}
	}

	/**
	* Set order type
	*/
	function setOrderType($a_type = "Unordered")
	{
		switch ($a_type)
		{
			case "Unordered":
				$this->list_node->set_attribute("Type", "Unordered");
				if ($this->list_node->has_attribute("NumberingType"))
				{
					$this->list_node->remove_attribute("NumberingType");
				}
				break;

			case "Number":
			case "Roman":
			case "roman":
			case "Alphabetic":
			case "alphabetic":
			case "Decimal":
				$this->list_node->set_attribute("Type", "Ordered");
				$this->list_node->set_attribute("NumberingType", $a_type);
				break;
		}
	}

	/**
	* Get order type
	*/
	function getOrderType()
	{
		if ($this->list_node->get_attribute("Type") == "Unordered")
		{
			return "Unordered";
		}
		
		$nt = $this->list_node->get_attribute("NumberingType");
		switch ($nt)
		{
			case "Number":
			case "Roman":
			case "roman":
			case "Alphabetic":
			case "alphabetic":
			case "Decimal":
				return $nt;
				break;
				
			default:
				return "Number";
		}
	}

	/**
	* Set start value
	*
	* @param	int		start value
	*/
	function setStartValue($a_val)
	{
		if ($a_val != "")
		{
			$this->list_node->set_attribute("StartValue", $a_val);
		}
		else
		{
			if ($this->list_node->has_attribute("StartValue"))
			{
				$this->list_node->remove_attribute("StartValue");
			}
		}
	}
	
	/**
	* Get start value
	*
	* @return	int		start value
	*/
	function getStartValue()
	{
		return $this->list_node->get_attribute("StartValue");
	}
}
?>
