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

declare(strict_types=1);

class ilTestParticipant
{
    protected ?int $active_id = null;
    protected ?int $anonymous_id = null;
    protected ?int $usr_id = null;
    protected ?string $login = null;
    protected ?string $lastname = null;
    protected ?string $firstname = null;
    protected ?string $matriculation = null;
    protected ?bool $active_status = null;
    protected ?string $client_id = null;
    protected ?int $finished_tries = null;
    protected ?bool $test_finished = null;
    protected ?bool $unfinished_passes = null;
    protected ?ilTestParticipantScoring $scoring = null;

    public function getActiveId(): ?int
    {
        return $this->active_id;
    }

    public function setActiveId(int $active_id): void
    {
        $this->active_id = $active_id;
    }

    public function getAnonymousId(): ?int
    {
        return $this->anonymous_id;
    }

    public function setAnonymousId(int $anonymous_id): void
    {
        $this->anonymous_id = $anonymous_id;
    }

    public function getUsrId(): ?int
    {
        return $this->usr_id;
    }

    public function setUsrId(int $usr_id): void
    {
        $this->usr_id = $usr_id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getMatriculation(): ?string
    {
        return $this->matriculation;
    }

    public function setMatriculation(string $matriculation): void
    {
        $this->matriculation = $matriculation;
    }

    public function isActiveStatus(): ?bool
    {
        return $this->active_status;
    }

    public function setActiveStatus(bool $active_status): void
    {
        $this->active_status = $active_status;
    }

    public function getClientIp(): ?string
    {
        return $this->client_id;
    }

    public function setClientIp(string $client_id): void
    {
        $this->client_id = $client_id;
    }

    public function getFinishedTries(): ?int
    {
        return $this->finished_tries;
    }

    public function setFinishedTries(int $finished_tries): void
    {
        $this->finished_tries = $finished_tries;
    }

    public function isTestFinished(): ?bool
    {
        return $this->test_finished;
    }

    public function setTestFinished(bool $test_finished): void
    {
        $this->test_finished = $test_finished;
    }

    public function hasUnfinishedPasses(): ?bool
    {
        return $this->unfinished_passes;
    }

    public function setUnfinishedPasses(bool $unfinished_passes): void
    {
        $this->unfinished_passes = $unfinished_passes;
    }

    public function getScoring(): ilTestParticipantScoring
    {
        return $this->scoring;
    }

    public function setScoring(ilTestParticipantScoring $scoring): void
    {
        $this->scoring = $scoring;
    }

    public function hasScoring(): bool
    {
        return $this->scoring instanceof ilTestParticipantScoring;
    }
}
