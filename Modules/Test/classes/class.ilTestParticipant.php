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
 * Class ilTestParticipant
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 */
class ilTestParticipant
{
    /**
     * @var string
     */
    protected $activeId;

    /**
     * @var string
     */
    protected $anonymousId;

    /**
     * @var string
     */
    protected $usrId;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $matriculation;

    /**
     * @var bool
     */
    protected $activeStatus;

    /**
     * @var string
     */
    protected $clientIp;

    /**
     * @var integer
     */
    protected $finishedTries;

    /**
     * @var bool
     */
    protected $testFinished;

    /**
     * @var bool
     */
    protected $unfinishedPasses;

    /**
     * @var ilTestParticipantScoring
     */
    protected $scoring;

    /**
     * ilTestParticipant constructor.
     * @param string $activeId
     * @param string $anonymousId
     * @param string $usrId
     * @param string $login
     * @param string $lastname
     * @param string $firstname
     * @param string $matriculation
     * @param string $clientIp
     * @param int $finishedTries
     * @param bool $testFinished
     * @param bool $unfinishedPasses
     */
    public function __construct()
    {
        $this->activeId = null;
        $this->anonymousId = null;
        $this->usrId = null;
        $this->login = null;
        $this->lastname = null;
        $this->firstname = null;
        $this->matriculation = null;
        $this->activeStatus = null;
        $this->clientIp = null;
        $this->finishedTries = null;
        $this->testFinished = null;
        $this->unfinishedPasses = null;
    }

    /**
     * @return string
     */
    public function getActiveId(): ?string
    {
        return $this->activeId;
    }

    /**
     * @param string $activeId
     */
    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    /**
     * @return string
     */
    public function getAnonymousId(): ?string
    {
        return $this->anonymousId;
    }

    /**
     * @param string $anonymousId
     */
    public function setAnonymousId($anonymousId)
    {
        $this->anonymousId = $anonymousId;
    }

    /**
     * @return string
     */
    public function getUsrId(): ?string
    {
        return $this->usrId;
    }

    /**
     * @param string $usrId
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;
    }

    /**
     * @return string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getMatriculation(): ?string
    {
        return $this->matriculation;
    }

    /**
     * @param string $matriculation
     */
    public function setMatriculation($matriculation)
    {
        $this->matriculation = $matriculation;
    }

    /**
     * @return bool
     */
    public function isActiveStatus(): ?bool
    {
        return $this->activeStatus;
    }

    /**
     * @param bool $activeStatus
     */
    public function setActiveStatus($activeStatus)
    {
        $this->activeStatus = $activeStatus;
    }

    /**
     * @return string
     */
    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    /**
     * @param string $clientIp
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;
    }

    /**
     * @return int
     */
    public function getFinishedTries(): ?int
    {
        return $this->finishedTries;
    }

    /**
     * @param int $finishedTries
     */
    public function setFinishedTries($finishedTries)
    {
        $this->finishedTries = $finishedTries;
    }

    /**
     * @return bool
     */
    public function isTestFinished(): ?bool
    {
        return $this->testFinished;
    }

    /**
     * @param bool $testFinished
     */
    public function setTestFinished($testFinished)
    {
        $this->testFinished = $testFinished;
    }

    /**
     * @return bool
     */
    public function hasUnfinishedPasses(): ?bool
    {
        return $this->unfinishedPasses;
    }

    /**
     * @param bool $unfinishedPasses
     */
    public function setUnfinishedPasses($unfinishedPasses)
    {
        $this->unfinishedPasses = $unfinishedPasses;
    }

    /**
     * @return ilTestParticipantScoring
     */
    public function getScoring(): ilTestParticipantScoring
    {
        return $this->scoring;
    }

    /**
     * @param ilTestParticipantScoring $scoring
     */
    public function setScoring(ilTestParticipantScoring $scoring)
    {
        $this->scoring = $scoring;
    }

    public function hasScoring(): bool
    {
        return $this->scoring instanceof ilTestParticipantScoring;
    }
}
