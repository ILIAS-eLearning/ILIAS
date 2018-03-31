<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestFixedParticipantsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilTestFixedParticipantsGUI
{
	/**
	 * Command Constants
	 */
	const CMD_SHOW = 'show';
	
	/**
	 * @var ilObjTest
	 */
	protected $testObj;
	
	/**
	 * @var ilTestAccess
	 */
	protected $testAccess;
	
	/**
	 * ilTestFixedParticipantsGUI constructor.
	 * @param ilObjTest $testObj
	 */
	public function __construct(ilObjTest $testObj)
	{
		$this->testObj = $testObj;
	}
	
	/**
	 * @return ilTestAccess
	 */
	public function getTestAccess()
	{
		return $this->testAccess;
	}
	
	/**
	 * @param ilTestAccess $testAccess
	 */
	public function setTestAccess($testAccess)
	{
		$this->testAccess = $testAccess;
	}
	
	/**
	 * Execute Command
	 */
	public function	executeCommand()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$command = $DIC->ctrl()->getCmd(self::CMD_SHOW).'Cmd';
		
		$this->{$command}();
	}
	
	/**
	 * Show Command
	 */
	public function showCmd()
	{
		
	}
}