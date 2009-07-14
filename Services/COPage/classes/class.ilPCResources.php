<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCResources
*
* Resources content object (see ILIAS DTD). Inserts Repository Resources
* of a Container Object,
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCResources extends ilPageContent
{
	var $dom;
	var $res_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("repobj");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->res_node =& $a_node->first_child();		// this is the Resources node
	}

	/**
	* Create resources node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->res_node =& $this->dom->create_element("Resources");
		$this->res_node =& $this->node->append_child($this->res_node);
	}

	/**
	* Set Type of Resource List (currently only one)
	*
	* @param	string	$a_type		Resource Type Group
	*/
	function setResourceListType($a_type)
	{
		if (!empty($a_type))
		{
			$children = $this->res_node->child_nodes();
			for ($i=0; $i<count($children); $i++)
			{
				$this->res_node->remove_child($children[$i]);
			}
			$list_node =& $this->dom->create_element("ResourceList");
			$list_node =& $this->res_node->append_child($list_node);
			$list_node->set_attribute("Type", $a_type);
		}
	}

	/**
	* Get Resource Lis Type.
	*
	* @return	string		resource type group
	*/
	function getResourceListType()
	{
		if (is_object($this->res_node))
		{
			$children = $this->res_node->child_nodes();
			if (is_object($children[0]))
			{
				return $children[0]->get_attribute("Type");
			}
		}
	}
}

?>
