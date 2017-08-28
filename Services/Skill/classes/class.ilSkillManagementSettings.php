<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill management settings
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilSkillManagementSettings extends ilSetting
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct("skmg");
	}
	
	/**
	 * Activate skill management
	 *
	 * @param
	 * @return
	 */
	function activate($a_active)
	{
		$this->set("enable_skmg", (int) $a_active);
	}
	
	
	/**
	 * Is activated
	 */
	function isActivated()
	{
		return $this->get("enable_skmg");
	}
	
	/**
	 * Set hide profile values before self evaluations
	 *
	 * @param bool $a_val hide profile	
	 */
	function setHideProfileBeforeSelfEval($a_val)
	{
		$this->set("hide_profile_self_eval", (int) $a_val);
	}
	
	/**
	 * Get hide profile values before self evaluations
	 *
	 * @return bool hide profile
	 */
	function getHideProfileBeforeSelfEval()
	{
		return $this->get("hide_profile_self_eval");
	}
}

?>
