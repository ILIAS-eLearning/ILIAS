<?php

namespace ILIAS\Test\Participants;

use ilTestParticipant;

class Participant extends ilTestParticipant
{
    protected ?int $test_id = null;
    protected int $extra_time = 0;
    protected int $tries = 0;
    protected ?string $client_ip_from = null;
    protected ?string $client_ip_to = null;
    protected ?int $invitation_date = null;

    public function getExtraTime(): int
    {
        return $this->extra_time;
    }

    public function setExtraTime(int $extra_time): void
    {
        $this->extra_time = $extra_time;
    }

    public function getClientIpFrom(): ?string
    {
        return $this->client_ip_from;
    }

    public function setClientIpFrom(?string $client_ip_from): void
    {
        $this->client_ip_from = $client_ip_from;
    }

    public function getClientIpTo(): ?string
    {
        return $this->client_ip_to;
    }

    public function setClientIpTo(?string $client_ip_to): void
    {
        $this->client_ip_to = $client_ip_to;
    }

    public function getTestId(): ?int
    {
        return $this->test_id;
    }

    public function setTestId(?int $test_id): void
    {
        $this->test_id = $test_id;
    }

    public function getTries(): int
    {
        return $this->tries;
    }

    public function setTries(int $tries): void
    {
        $this->tries = $tries;
    }

    public function getInvitationDate(): ?int
    {
        return $this->invitation_date;
    }

    public function setInvitationDate(?int $invitation_date): void
    {
        $this->invitation_date = $invitation_date;
    }

    public function isInvitedParticipant(): bool
    {
        return $this->invitation_date > 0;
    }
}
