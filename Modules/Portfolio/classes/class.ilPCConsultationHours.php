<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCConsultationHours
*
* Consultation hours content object (see ILIAS DTD)
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPCListItem.php 22210 2009-10-26 09:46:06Z akill $
*
* @ingroup ServicesCOPage
*/
class ilPCConsultationHours extends ilPageContent
{
	var $dom;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("cach");
	}
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("ed_insert_consultation_hours", "pc_cach");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->cach_node =& $a_node->first_child();		// this is the consultation hours node
	}

	/**
	* Create consultation hours node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->cach_node = $this->dom->create_element("ConsultationHours");
		$this->cach_node = $this->node->append_child($this->cach_node);
	}

	/**
	 * Set consultation hours settings
	 *
	 * @param int $a_mode
	 * @param int $a_grp_id
	 */
	function setData($a_mode, array $a_grp_ids)
	{
		global $ilUser;
		
		$this->cach_node->set_attribute("Mode", $a_mode);
		$this->cach_node->set_attribute("User", $ilUser->getId());
		
		// remove all children first
		$children = $this->cach_node->child_nodes();
		if($children)
		{
			foreach($children as $child)
			{
				$this->cach_node->remove_child($child);
			}
		}

		if($a_mode == "manual")
		{
			foreach($a_grp_ids as $grp_id)
			{
				$field_node = $this->dom->create_element("ConsultationHoursGroup");
				$field_node = $this->cach_node->append_child($field_node);
				$field_node->set_attribute("Id", $grp_id);
			}
		}
	}

	/**
	 * Get consultation hours group ids
	 *
	 * @return string
	 */
	function getGroupIds()
	{
		$res = array();
		if (is_object($this->cach_node))
		{
			$children = $this->cach_node->child_nodes();
			if($children)
			{
				foreach($children as $child)
				{
					$res[] = $child->get_attribute("Id");
				}
			}
		}
		return $res;
	}
}
?>