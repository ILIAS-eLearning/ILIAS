<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Class ilPCTabs
*
* Tabbed contents (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTabs extends ilPageContent
{
	var $tabs_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("tabs");
	}

	/**
	* Set content node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->tabs_node =& $a_node->first_child();		// this is the Tabs node
	}

	/**
	* Create new Tabs node
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->tabs_node =& $this->dom->create_element("Tabs");
		$this->tabs_node =& $this->node->append_child($this->tabs_node);
	}

	/**
	* Add Tab items
	*/
	function addItems($a_nr)
	{
		for ($i=1; $i<=$a_nr; $i++)
		{
			$new_item =& $this->dom->create_element("Tab");
			$new_item =& $this->tabs_node->append_child($new_item);
		}
	}

	/**
	* Set type of tabs
	*
	* @param	string		$a_type		("HorizontalTabs" | "Accordion")
	*/
	function setTabType($a_type = "HorizontalTabs")
	{
		switch ($a_type)
		{
			case "HorizontalTabs":
			case "Accordion":
				$this->tabs_node->set_attribute("Type", $a_type);
				break;
		}
	}

	/**
	* Get type of tabs
	*/
	function getTabType()
	{
		return $this->tabs_node->get_attribute("Type");
	}
	
	/**
	* Add Tab items
	*/
	function setCaptions($a_captions)
	{
		// iterate all tab nodes
		$j = 0;
		$tab_nodes = $this->tabs_node->child_nodes();
		for($i = 0; $i < count($tab_nodes); $i++)
		{
			if ($tab_nodes[$i]->node_name() == "Tab")
			{
				// if caption given, set it, otherwise delete caption subitem
				if ($a_captions[$j] != "")
				{
					ilDOMUtil::setFirstOptionalElement($this->dom, $tab_nodes[$i], "TabCaption",
						array("PageContent"), $a_captions[$j], array());
				}
				else
				{
					ilDOMUtil::deleteAllChildsByName($tab_nodes[$i], array("TabCaption"));
				}
				$j++;
			}
		}
	}

	/**
	* Get captions
	*/
	function getCaptions()
	{
		$captions = array();
		$tab_nodes = $this->tabs_node->child_nodes();
		for($i = 0; $i < count($tab_nodes); $i++)
		{
			if ($tab_nodes[$i]->node_name() == "Tab")
			{
				$tab_node_childs = $tab_nodes[$i]->child_nodes();
				$current_caption = "";
				for($j = 0; $j < count($tab_node_childs); $j++)
				{
					if ($tab_node_childs[$j]->node_name() == "TabCaption")
					{
						$current_caption = $tab_node_childs[$j]->get_content();
					}
				}
				$captions[] = $current_caption;
			}
		}
		
		return $captions;
	}

}
?>
