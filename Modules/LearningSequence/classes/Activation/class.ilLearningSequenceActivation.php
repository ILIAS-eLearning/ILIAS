<?php

declare(strict_types=1);

/**
 * Activation-Settings for an LSO
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLearningSequenceActivation
{
    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var bool
     */
    protected $online;

    /**
     * @var bool
     */
    protected $effective_online;

    /**
     * @var \DateTime | null
     */
    protected $activation_start;

    /**
     * @var \DateTime | null
     */
    protected $activation_end;


    public function __construct(
        int $ref_id,
        bool $online = false,
        bool $effective_online = false,
        \DateTime $activation_start = null,
        \DateTime $activation_end = null
    ) {
        $this->ref_id = $ref_id;
        $this->online = $online;
        $this->effective_online = $effective_online;
        $this->activation_start = $activation_start;
        $this->activation_end = $activation_end;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getIsOnline() : bool
    {
        return $this->online;
    }

    public function withIsOnline(bool $online) : ilLearningSequenceActivation
    {
        $clone = clone $this;
        $clone->online = $online;
        return $clone;
    }

    public function getEffectiveOnlineStatus() : bool
    {
        return $this->effective_online;
    }

    /**
     * @return \DateTime | null
     */
    public function getActivationStart()
    {
        return $this->activation_start;
    }

    public function withActivationStart(\DateTime $activation_start = null) : ilLearningSequenceActivation
    {
        $clone = clone $this;
        $clone->activation_start = $activation_start;
        return $clone;
    }

    /**
     * @return \DateTime | null
     */
    public function getActivationEnd()
    {
        return $this->activation_end;
    }

    public function withActivationEnd(\DateTime $activation_end = null) : ilLearningSequenceActivation
    {
        $clone = clone $this;
        $clone->activation_end = $activation_end;
        return $clone;
    }
}
