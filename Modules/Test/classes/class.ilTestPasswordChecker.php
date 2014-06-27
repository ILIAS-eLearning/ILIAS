<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestPasswordChecker
{
	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;
	
	public function __construct(ilRbacSystem $rbacsystem, ilObjUser $user, ilObjTest $testOBJ)
	{
		$this->rbacsystem = $rbacsystem;
		$this->user = $user;
		$this->testOBJ = $testOBJ;
		
		$this->initSession();
	}
	
	public function isPasswordProtectionPageRedirectRequired()
	{
		if( !$this->isTestPasswordEnabled() )
		{
			return false;
		}

		if( $this->isPrivilegedParticipant() )
		{
			return false;
		}

		if( $this->isUserEnteredPasswordCorrect() )
		{
			return false;
		}
		
		return true;
	}

	protected function isTestPasswordEnabled()
	{
		return strlen($this->testOBJ->getPassword());
	}

	protected function isPrivilegedParticipant()
	{
		return $this->rbacsystem->checkAccess('write', $this->testOBJ->getRefId());
	}

	public function isUserEnteredPasswordCorrect()
	{
		return $this->getUserEnteredPassword() == $this->testOBJ->getPassword();
	}

	public function setUserEnteredPassword($enteredPassword)
	{
		$_SESSION[$this->buildSessionKey()] = $enteredPassword;
	}
	
	protected function getUserEnteredPassword()
	{
		return $_SESSION[$this->buildSessionKey()];
	}

	protected function initSession()
	{
		if( !isset($_SESSION[$this->buildSessionKey()]) )
		{
			$_SESSION[$this->buildSessionKey()] = null;
		}
	}
	
	protected function buildSessionKey()
	{
		return 'tst_password_'.$this->testOBJ->getTestId();
	}
}