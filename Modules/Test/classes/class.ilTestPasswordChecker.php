<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    }

    public function isPasswordProtectionPageRedirectRequired(): bool
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

    protected function isTestPasswordEnabled(): int
    {
        return strlen($this->testOBJ->getPassword());
    }

    protected function isPrivilegedParticipant(): bool
    {
        return $this->rbacsystem->checkAccess('write', $this->testOBJ->getRefId());
    }

    public function wrongUserEnteredPasswordExist(): bool
    {
        if (!strlen($this->getUserEnteredPassword())) {
            return false;
        }

        return !$this->isUserEnteredPasswordCorrect();
    }

    public function isUserEnteredPasswordCorrect(): bool
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

    protected function buildSessionKey(): string
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

    protected function getWrongEnteredPasswordLogMsg(): string
    {
        return $this->lng->txtlng(
            'assessment',
            'log_wrong_test_password_entered',
            ilObjAssessmentFolder::_getLogLanguage()
        );
    }
}
