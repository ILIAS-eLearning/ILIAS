<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCAMDPageList
*
* Advanced MD page list content object (see ILIAS DTD)
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPCListItem.php 22210 2009-10-26 09:46:06Z akill $
*
* @ingroup ModulesWiki
*/
class ilPCAMDPageList extends ilPageContent
{
	var $dom;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("amdpl");
	}
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("ed_insert_amd_page_list", "pc_amdpl");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->amdpl_node =& $a_node->first_child();		// this is the courses node
	}

	/**
	* Create list node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->amdpl_node = $this->dom->create_element("AMDPageList");
		$this->amdpl_node = $this->node->append_child($this->amdpl_node);
	}

	/**
	 * Set list settings
	 */
	function setData(array $a_field_data)
	{			
		// remove all children first
		$children = $this->amdpl_node->child_nodes();
		if($children)
		{
			foreach($children as $child)
			{
				$this->amdpl_node->remove_child($child);
			}
		}

		foreach($a_field_data as $field_id => $value)
		{
			$field_node = $this->dom->create_element("AdvMDField");
			$field_node = $this->amdpl_node->append_child($field_node);
			$field_node->set_attribute("Id", $field_id);			
			$field_node->set_content($value);			
		}		
	}
	
	/**
	 * Get consultation hours group ids
	 *
	 * @return string
	 */
	function getFieldValues()
	{
		$res = array();
		if (is_object($this->amdpl_node))
		{
			$children = $this->amdpl_node->child_nodes();
			if($children)
			{
				foreach($children as $child)
				{
					$res[$child->get_attribute("Id")] = $child->get_content();
				}
			}
		}
		return $res;
	}
}

?>