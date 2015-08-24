<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Awareness GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class ilAwarenessGUI
{
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	protected function __construct()
	{

	}

	/**
	 * Get instance
	 *
	 * @param
	 * @return
	 */
	static function getInstance()
	{
		return new ilAwarenessGUI();
	}

	/**
	 * Get main menu html
	 */
	function getMainMenuHTML()
	{
		global $ilUser;

		$tpl = new ilTemplate("tpl.awareness.html", true, true, "Services/Awareness");



		include_once("./Services/Awareness/classes/class.ilAwarenessAct.php");
		$act = ilAwarenessAct::getInstance($ilUser->getId());
		$users = $act->getAwarenessData();

		$tpl->setCurrentBlock("status_text");
		$tpl->setVariable("STATUS_TXT", count($users));
		$tpl->parseCurrentBlock();

		$cnt = 0;
		foreach ($users as $u)
		{
			$cnt++;
			$tpl->setCurrentBlock("user");
			if ($u->public_profile)
			{
				$tpl->setVariable("USERNAME", $u->lastname.", ".$u->firstname." [".$u->login."]");
			}
			else
			{
				$tpl->setVariable("USERNAME", "[".$u->login."]");
			}
			$tpl->setVariable("USERIMAGE", $u->img);
			$tpl->setVariable("CNT", $cnt);
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

}
?>