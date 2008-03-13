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
* Class ilPCSection
*
* Section content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCSection extends ilPageContent
{
	var $dom;
	var $sec_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("sec");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->sec_node =& $a_node->first_child();		// this is the Section node
	}

	/**
	* Create section node in xml.
	*/
	function create(&$a_pg_obj, $a_hier_id)
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->sec_node =& $this->dom->create_element("Section");
		$this->sec_node =& $this->node->append_child($this->sec_node);
		$this->sec_node->set_attribute("Characteristic", "Block");
	}

	/**
	* Set Characteristic of section
	*/
	function setCharacteristic($a_char)
	{
		if (!empty($a_char))
		{
			$this->sec_node->set_attribute("Characteristic", $a_char);
		}
		else
		{
			if ($this->sec_node->has_attribute("Characteristic"))
			{
				$this->sec_node->remove_attribute("Characteristic");
			}
		}
	}

	/**
	* Get characteristic of section.
	*
	* @return	string		characteristic
	*/
	function getCharacteristic()
	{
		if (is_object($this->sec_node))
		{
			return $this->sec_node->get_attribute("Characteristic");
		}
	}

}

?>
