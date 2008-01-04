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
* Class ilPCFileList
*
* File List content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCFileList extends ilPageContent
{
	var $list_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("flst");
	}

	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->list_node =& $a_node->first_child();		// this is the Table node
	}

	function create(&$a_pg_obj, $a_hier_id)
	{
//echo "::".is_object($this->dom).":";
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->list_node =& $this->dom->create_element("FileList");
		$this->list_node =& $this->node->append_child($this->list_node);
	}

	/*
	function addItems($a_nr)
	{
		for ($i=1; $i<=$a_nr; $i++)
		{
			$new_item =& $this->dom->create_element("ListItem");
			$new_item =& $this->list_node->append_child($new_item);
		}
	}*/

	function appendItem($a_id, $a_location, $a_format)
	{
		// File Item
		$new_item =& $this->dom->create_element("FileItem");
		$new_item =& $this->list_node->append_child($new_item);

		// Identifier
		$id_node =& $this->dom->create_element("Identifier");
		$id_node =& $new_item->append_child($id_node);
		$id_node->set_attribute("Catalog", "ILIAS");
		$id_node->set_attribute("Entry", "il__file_".$a_id);

		// Location
		$loc_node =& $this->dom->create_element("Location");
		$loc_node =& $new_item->append_child($loc_node);
		$loc_node->set_attribute("Type", "LocalFile");
		$loc_node->set_content($a_location);

		// Format
		$form_node =& $this->dom->create_element("Format");
		$form_node =& $new_item->append_child($form_node);
		$form_node->set_content($a_format);
	}

	function setListTitle($a_title, $a_language)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom,
			$this->list_node, "Title", array("FileItem"),
			$a_title, array("Language" => $a_language));
	}

	function getListTitle()
	{
		$chlds =& $this->list_node->child_nodes();
		for($i=0; $i<count($chlds); $i++)
		{
			if ($chlds[$i]->node_name() == "Title")
			{
				return $chlds[$i]->get_content();
			}
		}
		return "";
	}

	function getLanguage()
	{
		$chlds =& $this->list_node->child_nodes();
		for($i=0; $i<count($chlds); $i++)
		{
			if ($chlds[$i]->node_name() == "Title")
			{
				return $chlds[$i]->get_attribute("Language");
			}
		}
		return "";
	}
}
?>
