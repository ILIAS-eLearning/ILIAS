<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->sec_node =& $this->dom->create_element("Section");
		$this->sec_node =& $this->node->append_child($this->sec_node);
		$this->sec_node->set_attribute("Characteristic", "Block");
	}

	/**
	* Set Characteristic of section
	*
	* @param	string	$a_char		Characteristic
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
			$char =  $this->sec_node->get_attribute("Characteristic");
			if (substr($char, 0, 4) == "ilc_")
			{
				$char = substr($char, 4);
			}
			return $char;
		}
	}
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("ed_insert_section");
	}

}

?>
