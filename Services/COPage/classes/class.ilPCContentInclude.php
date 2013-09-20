<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCContentInclude
*
* Content include object (see ILIAS DTD). Inserts content snippets from other
* source (e.g. media pool)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCContentInclude extends ilPageContent
{
	var $dom;
	var $incl_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("incl");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->incl_node =& $a_node->first_child();		// this is the snippet node
	}

	/**
	* Create content include node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->incl_node =& $this->dom->create_element("ContentInclude");
		$this->incl_node =& $this->node->append_child($this->incl_node);
	}

	/**
	 * Set content id
	 */
	function setContentId($a_id)
	{
		$this->setContentIncludeAttribute("ContentId", $a_id);
	}
	
	/**
	 * Get content id
	 */
	function getContentId()
	{
		return $this->getContentIncludeAttribute("ContentId");
	}

	/**
	 * Set content type
	 */
	function setContentType($a_type)
	{
		$this->setContentIncludeAttribute("ContentType", $a_type);
	}
	
	/**
	 * Get content type
	 */
	function getContentType()
	{
		return $this->getContentIncludeAttribute("ContentType");
	}

	/**
	 * Set installation id
	 */
	function setInstId($a_id)
	{
		$this->setContentIncludeAttribute("InstId", $a_id);
	}

	/**
	 * Get installation id
	 */
	function getInstId()
	{
		return $this->getContentIncludeAttribute("InstId");
	}
	
	/**
	* Set attribute of content include tag
	*
	* @param	string		attribute name
	* @param	string		attribute value
	*/
	protected function setContentIncludeAttribute($a_attr, $a_value)
	{
		if (!empty($a_value))
		{
			$this->incl_node->set_attribute($a_attr, $a_value);
		}
		else
		{
			if ($this->incl_node->has_attribute($a_attr))
			{
				$this->incl_node->remove_attribute($a_attr);
			}
		}
	}

	/**
	* Get content include tag attribute
	*
	* @return	string		attribute name
	*/
	function getContentIncludeAttribute($a_attr)
	{
		if (is_object($this->incl_node))
		{
			return  $this->incl_node->get_attribute($a_attr);
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
		// pc content include
		self::saveContentIncludeUsage($a_page, $a_domdoc);
	}
	
	/**
	 * Before page is being deleted
	 *
	 * @param object $a_page page object
	 */
	static function beforePageDelete($a_page)
	{
		include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
		ilPageContentUsage::deleteAllUsages("incl", $a_page->getParentType().":pg", $a_page->getId(), false, $a_page->getLanguage());
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
		self::saveContentIncludeUsage($a_page, $a_old_domdoc, $a_old_nr);
	}

	/**
	 * save content include usages
	 */
	static function saveContentIncludeUsage($a_page, $a_domdoc, $a_old_nr = 0)
	{
		include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
		$ci_ids = self::collectContentIncludes($a_page, $a_domdoc);
		ilPageContentUsage::deleteAllUsages("incl", $a_page->getParentType().":pg", $a_page->getId(), $a_old_nr, $a_page->getLanguage());
		foreach($ci_ids as $ci_id)
		{
			if ((int) $ci_id["inst_id"] <= 0)
			{
				ilPageContentUsage::saveUsage("incl", $ci_id["id"], $a_page->getParentType().":pg", $a_page->getId(), $a_old_nr,
					$a_page->getLanguage());
			}
		}
	}

	/**
	 * get all content includes that are used within the page
	 */
	static function collectContentIncludes($a_page, $a_domdoc)
	{
		$xpath = new DOMXPath($a_domdoc);
		$nodes = $xpath->query('//ContentInclude');	

		$ci_ids = array();
		foreach ($nodes as $node)
		{
			$type = $node->getAttribute("ContentType");
			$id = $node->getAttribute("ContentId");
			$inst_id = $node->getAttribute("InstId");
			$ci_ids[$type.":".$id.":".$inst_id] = array(
				"type" => $type, "id" => $id, "inst_id" => $inst_id);
		}

		return $ci_ids;
	}

}

?>