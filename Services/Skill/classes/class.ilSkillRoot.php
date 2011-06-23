<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

/**
 * Skill root node
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillRoot extends ilSkillTreeNode
{
	var $id;

	/**
	 * Constructor
	 * @access	public
	 */
	function __construct($a_id = 0)
	{
		parent::ilSkillTreeNode($a_id);
		$this->setType("skrt");
	}

	/**
	 * Read data from database
	 */
	function read()
	{
		parent::read();
	}

	/**
	 * Create skill
	 *
	 */
	function create()
	{
		parent::create();
	}

	/**
	 * Delete skill
	 */
	function delete()
	{
		parent::delete();
	}

}
?>
