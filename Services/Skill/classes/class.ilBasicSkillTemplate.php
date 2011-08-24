<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilBasicSkill.php");

/**
 * Basic Skill Template
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilBasicSkillTemplate extends ilBasicSkill
{
	var $id;

	/**
	 * Constructor
	 * @access	public
	 */
	function __construct($a_id = 0)
	{
		parent::ilSkillTreeNode($a_id);
		$this->setType("sktp");
	}

	/**
	 * Copy basic skill template
	 */
	function copy()
	{
		$skill = new ilBasicSkillTemplate();
		$skill->setTitle($this->getTitle());
		$skill->setType($this->getType());
		$skill->setOrderNr($this->getOrderNr());
		$skill->create();

		$levels = $this->getLevelData();
		if (sizeof($levels))
		{
			foreach($levels as $item)
			{
				$skill->addLevel($item["title"], $item["description"]);
			}
		}
		$skill->update();

		return $skill;
	}
}
?>
