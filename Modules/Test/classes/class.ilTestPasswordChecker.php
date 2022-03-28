<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';

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

    /**
     * @var ilLanguage
     */
    protected $lng;
    
    public function __construct(ilRbacSystem $rbacsystem, ilObjUser $user, ilObjTest $testOBJ, ilLanguage $lng)
    {
        $this->rbacsystem = $rbacsystem;
        $this->user = $user;
        $this->testOBJ = $testOBJ;
        $this->lng = $lng;
        
        $this->initSession();
    }
    
    public function isPasswordProtectionPageRedirectRequired() : bool
    {
        if (!$this->isTestPasswordEnabled()) {
            return false;
        }

        if ($this->isPrivilegedParticipant()) {
            return false;
        }

        if ($this->isUserEnteredPasswordCorrect()) {
            return false;
        }
        
        return true;
    }

    protected function isTestPasswordEnabled() : int
    {
        return strlen($this->testOBJ->getPassword());
    }

    protected function isPrivilegedParticipant() : bool
    {
        return $this->rbacsystem->checkAccess('write', $this->testOBJ->getRefId());
    }
    
    public function wrongUserEnteredPasswordExist() : bool
    {
        if (!strlen($this->getUserEnteredPassword())) {
            return false;
        }
        
        return !$this->isUserEnteredPasswordCorrect();
    }

    public function isUserEnteredPasswordCorrect() : bool
    {
        return $this->getUserEnteredPassword() == $this->testOBJ->getPassword();
    }

    public function setUserEnteredPassword($enteredPassword)
    {
        ilSession::set($this->buildSessionKey(), $enteredPassword);
    }
    
    protected function getUserEnteredPassword()
    {
        return ilSession::get($this->buildSessionKey());
    }

    protected function initSession()
    {
        if (ilSession::get($this->buildSessionKey()) !== null) {
            ilSession::clear($this->buildSessionKey());
        }
    }
    
    protected function buildSessionKey() : string
    {
        return 'tst_password_' . $this->testOBJ->getTestId();
    }
    
    public function logWrongEnteredPassword()
    {
        if (!ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            return;
        }
        
        ilObjAssessmentFolder::_addLog(
            $this->user->getId(),
            $this->testOBJ->getId(),
            $this->getWrongEnteredPasswordLogMsg(),
            null,
            null,
            true,
            $this->testOBJ->getRefId()
        );
    }
    
    protected function getWrongEnteredPasswordLogMsg() : string
    {
        return $this->lng->txtlng(
            'assessment',
            'log_wrong_test_password_entered',
            ilObjAssessmentFolder::_getLogLanguage()
        );
    }
}
