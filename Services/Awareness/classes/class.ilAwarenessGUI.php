<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Awareness GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessGUI
{
	/**
	 * Constructor
	 */
	protected function __construct()
	{
		$this->ref_id = (int) $_GET["ref_id"];
	}

	/**
	 * Get instance
	 *
	 * @return ilAwarenessGUI awareness gui object
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

		$awrn_set = new ilSetting("awrn");
		if (!$awrn_set->get("awrn_enabled", false))
		{
			return "";
		}

		$tpl = new ilTemplate("tpl.awareness.html", true, true, "Services/Awareness");

		include_once("./Services/Awareness/classes/class.ilAwarenessAct.php");
		$act = ilAwarenessAct::getInstance($ilUser->getId());
		$act->setRefId($this->ref_id);
		$users = $act->getAwarenessData();

		$act->notifyOnNewOnlineContacts();

		if (count($users) > 0)
		{
			$tpl->setCurrentBlock("status_text");
			$tpl->setVariable("STATUS_TXT", count($users));
			$tpl->parseCurrentBlock();
		}

		$ucnt = 0;
		foreach ($users as $u)
		{
			$ucnt++;

			$fcnt = 0;
			foreach ($u->features as $f)
			{
				$fcnt++;
				if ($fcnt == 1)
				{
					$tpl->touchBlock("arrow");
					//$tpl->setCurrentBlock("arrow");
					//$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("feature");
				$tpl->setVariable("FEATURE_HREF", $f->href);
				$tpl->setVariable("FEATURE_TEXT", $f->text);
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("user");
			if ($u->public_profile)
			{
				$tpl->setVariable("UNAME", $u->lastname.", ".$u->firstname);
			}
			else
			{
				$tpl->setVariable("UNAME", "&nbsp;");
			}
			$tpl->setVariable("UACCOUNT", $u->login);

			$tpl->setVariable("USERIMAGE", $u->img);
			$tpl->setVariable("CNT", $ucnt);
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}
}
?>