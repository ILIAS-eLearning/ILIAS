<?php declare(strict_types=1);

/**
 * Global Settings of the Learning Sequence
 */
class LSGlobalSettings
{
    /**
     * @var float
     */
    protected $polling_interval_seconds;

    public function __construct(float $polling_interval_seconds)
    {
        $this->polling_interval_seconds = $polling_interval_seconds;
    }

    public function getPollingIntervalSeconds() : float
    {
        return $this->polling_interval_seconds;
    }

    public function getPollingIntervalMilliseconds() : int
    {
        $interval = $this->getPollingIntervalSeconds() * 1000;
        return (int) $interval;
    }

    public function withPollingIntervalSeconds(float $seconds) : LSGlobalSettings
    {
        $clone = clone $this;
        $clone->polling_interval_seconds = $seconds;
        return $clone;
    }
}
