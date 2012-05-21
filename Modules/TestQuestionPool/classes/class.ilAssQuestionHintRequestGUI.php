<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintAbstractGUI.php';

/**
 * GUI class for management/output of hint requests during test session
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintRequestGUI extends ilAssQuestionHintAbstractGUI
{
	/**
	 * command constants
	 */
	const CMD_SHOW_LIST			= 'showList';
	const CMD_SHOW_HINT			= 'showHint';
	const CMD_CONFIRM_REQUEST	= 'confirmRequest';
	const CMD_PERFORM_REQUEST	= 'performRequest';
	const CMD_CANCEL_REQUEST	= 'cancelRequest';
	
	/**
	 * Execute Command
	 * 
	 * @access	public
	 * @global	ilCtrl	$ilCtrl
	 * @return	mixed 
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$cmd = $ilCtrl->getCmd(self::CMD_SHOW_LIST);
		$nextClass = $ilCtrl->getNextClass($this);

		switch($nextClass)
		{
			default:
				
				$cmd .= 'Cmd';
				return $this->$cmd();
				break;
		}
	}
	
	/**
	 * shows the list of allready requested hints
	 * 
	 * @access	private
	 */
	private function showListCmd()
	{
		// @todo: implement command
	}
	
	/**
	 * shows an allready requested hint
	 * 
	 * @access	private
	 */
	private function showHintCmd()
	{
		// @todo: implement command
	}
	
	/**
	 * shows a confirmation screen for a hint request
	 * 
	 * @access	private
	 */
	private function confirmRequestCmd()
	{
		// @todo: implement command
	}
	
	/**
	 * performs a hint request and redirects to showHint command 
	 * 
	 * @access	private
	 */
	private function performRequestCmd()
	{
		// @todo: implement command
	}
	
	/**
	 * gateway command method to jump back to test session output
	 * 
	 * @access	private
	 */
	private function cancelRequestCmd()
	{
		// @todo: implement command
	}
	
}
