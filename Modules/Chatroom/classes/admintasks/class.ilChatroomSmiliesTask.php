<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSmiliesTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomSmiliesTask extends ilChatroomTaskHandler
{
	/**
	 * @var ilChatroomObjectGUI
	 */
	private $gui;

	/**
	 * Constructor
	 *
	 * Sets $this->gui using given $gui
	 *
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		$this->gui = $gui;
	}

	/**
	 * Switches to visible mode and prepares template.
	 *
	 * @global ilTemplate $tpl
	 * @param string $method
	 */
	public function executeDefault($method)
	{
		global $tpl;

		$this->gui->switchToVisibleMode();
		$tpl->setVariable( 'ADM_CONTENT', 'hallo welt' );
	}
}