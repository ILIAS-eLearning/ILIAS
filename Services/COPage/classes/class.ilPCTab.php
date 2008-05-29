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
* Class ilPCTab
*
* Tab content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTab extends ilPageContent
{
	var $dom;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("tabstab");
	}

	/**
	* insert new tab item after current one
	*/
	function newItemAfter()
	{
		$tab = $this->getNode();
		$new_tab =& $this->dom->create_element("Tab");
		if ($next_tab =& $tab->next_sibling())
		{
			$new_tab =& $next_tab->insert_before($new_tab, $next_tab);
		}
		else
		{
			$parent_tabs = $tab->parent_node();
			$new_tab =& $parent_tabs->append_child($new_tab);
		}
	}


	/**
	* insert new tab item before current one
	*/
	function newItemBefore()
	{
		$tab = $this->getNode();
		$new_tab = $this->dom->create_element("Tab");
		$new_tab = $tab->insert_before($new_tab, $tab);
	}


	/**
	* delete tab
	*/
	function deleteItem()
	{
		$tab =& $this->getNode();
		$tab->unlink($tab);
	}

	/**
	* move tab item down
	*/
	function moveItemDown()
	{
		$tab = $this->getNode();
		$next = $tab->next_sibling();
		$next_copy = $next->clone_node(true);
		$next_copy = $tab->insert_before($next_copy, $tab);
		$next->unlink($next);
	}

	/**
	* move tab item up
	*/
	function moveItemUp()
	{
		$tab = $this->getNode();
		$prev = $tab->previous_sibling();
		$tab_copy = $tab->clone_node(true);
		$tab_copy =& $prev->insert_before($tab_copy, $prev);
		$tab->unlink($tab);
	}

}
?>
