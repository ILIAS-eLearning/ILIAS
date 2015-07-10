<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionSkillUsagesGUI
{
	const CMD_SHOW_USAGES = 'showUsages';

	/**
	 * @var ilCtrl
	 */
	private $ctrl;

	/**
	 * @var ilTemplate
	 */
	private $tpl;

	/**
	 * @var ilLanguage
	 */
	private $lng;

	/**
	 * @var ilDB
	 */
	private $db;
	
	/**
	 * @param ilCtrl $ctrl
	 * @param ilTemplate $tpl
	 * @param ilLanguage $lng
	 * @param ilDB $db
	 */
	public function __construct(ilCtrl $ctrl, ilTemplate $tpl, ilLanguage $lng, ilDB $db)
	{
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->db = $db;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd(self::CMD_SHOW_USAGES) . 'Cmd';

		$this->$cmd();
	}
	
	private function showUsagesCmd()
	{
		$this->tpl->setContent('<pre>'.__METHOD__ . '</pre><i>Not implemented yet!</i>');
	}
}