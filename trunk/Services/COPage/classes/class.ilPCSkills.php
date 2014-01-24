<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCSkills
*
* Skills content object (see ILIAS DTD)
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPCListItem.php 22210 2009-10-26 09:46:06Z akill $
*
* @ingroup ServicesCOPage
*/
class ilPCSkills extends ilPageContent
{
	var $dom;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("skills");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->skill_node =& $a_node->first_child();		// this is the skill node
	}

	/**
	* Create skill node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->skill_node = $this->dom->create_element("Skills");
		$this->skill_node = $this->node->append_child($this->skill_node);
	}

	/**
	 * Set skill settings
	 *
	 * @param int $a_skill_id
	 */
	function setData($a_skill_id)
	{
		global $ilUser;
		
		$this->skill_node->set_attribute("Id", $a_skill_id);
		$this->skill_node->set_attribute("User", $ilUser->getId());
	}

	/**
	 * Get skill mode
	 *
	 * @return string
	 */
	function getSkillId()
	{
		if (is_object($this->skill_node))
		{
			return $this->skill_node->get_attribute("Id");
		}
	}
	
	/**
	 * After page has been updated (or created)
	 *
	 * @param object $a_page page object
	 * @param DOMDocument $a_domdoc dom document
	 * @param string $a_xml xml
	 * @param bool $a_creation true on creation, otherwise false
	 */
	static function afterPageUpdate($a_page, DOMDocument $a_domdoc, $a_xml, $a_creation)
	{
		// pc skill
		self::saveSkillUsage($a_page, $a_domdoc);
	}
	
	/**
	 * Before page is being deleted
	 *
	 * @param object $a_page page object
	 */
	static function beforePageDelete($a_page)
	{
		ilPageContentUsage::deleteAllUsages("skmg", $a_page->getParentType().":pg", $a_page->getId(), false,
			$a_page->getLanguage());
	}

	/**
	 * After page history entry has been created
	 *
	 * @param object $a_page page object
	 * @param DOMDocument $a_old_domdoc old dom document
	 * @param string $a_old_xml old xml
	 * @param integer $a_old_nr history number
	 */
	static function afterPageHistoryEntry($a_page, DOMDocument $a_old_domdoc, $a_old_xml, $a_old_nr)
	{
		self::saveSkillUsage($a_page, $a_old_domdoc, $a_old_nr);
	}
	
	/**
	 * save content include usages
	 */
	static function saveSkillUsage($a_page, $a_domdoc, $a_old_nr = 0)
	{
		include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
		$skl_ids = self::collectSkills($a_page, $a_domdoc);
		ilPageContentUsage::deleteAllUsages("skmg", $a_page->getParentType().":pg", $a_page->getId(), $a_old_nr,
			$a_page->getLanguage());
		foreach($skl_ids as $skl_id)
		{
			if ((int) $skl_id["inst_id"] <= 0)
			{
				ilPageContentUsage::saveUsage("skmg", $skl_id["id"], $a_page->getParentType().":pg", $a_page->getId(), $a_old_nr,
					$a_page->getLanguage());
			}
		}
	}

	/**
	 * get all content includes that are used within the page
	 */
	static function collectSkills($a_page, $a_domdoc)
	{
		$xpath = new DOMXPath($a_domdoc);
		$nodes = $xpath->query('//Skills');	

		$skl_ids = array();
		foreach($nodes as $node)
		{
			$user = $node->getAttribute("User");
			$id = $node->getAttribute("Id");
			$inst_id = $node->getAttribute("InstId");
			$skl_ids[$user.":".$id.":".$inst_id] = array(
				"user" => $user, "id" => $id, "inst_id" => $inst_id);
		}

		return $skl_ids;
	}


}
?>