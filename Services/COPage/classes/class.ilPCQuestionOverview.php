<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
 * Question overview page content element
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCQuestionOverview extends ilPageContent
{
	var $dom;
	var $qover_node;

	/**
	 * Init page content component.
	 */
	function init()
	{
		$this->setType("qover");
	}

	/**
	 * Set node
	 */
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->qover_node = $a_node->first_child();		// this is the question overview node
	}

	/**
	 * Create question overview node in xml.
	 *
	 * @param	object	$a_pg_obj		Page Object
	 * @param	string	$a_hier_id		Hierarchical ID
	 */
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->qover_node = $this->dom->create_element("QuestionOverview");
		$this->qover_node = $this->node->append_child($this->qover_node);
		$this->qover_node->set_attribute("Type", "Short");
	}

	/**
	 * Set type of question overview
	 *
	 * @param	string	$a_type		Type
	 */
	function setOverviewType($a_type)
	{
		if (!empty($a_type))
		{
			$this->qover_node->set_attribute("Type", $a_type);
		}
		else
		{
			if ($this->qover_node->has_attribute("Type"))
			{
				$this->qover_node->remove_attribute("Type");
			}
		}
	}

	/**
	 * Get type of question overview
	 *
	 * @return	string		type
	 */
	function getOverviewType()
	{
		if (is_object($this->qover_node))
		{
			$type =  $this->qover_node->get_attribute("Type");
			return $type;
		}
	}
}

?>
