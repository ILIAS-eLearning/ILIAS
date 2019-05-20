<?php



/**
 * LsoActivation
 */
class LsoActivation
{
    /**
     * @var int
     */
    private $refId;

    /**
     * @var bool
     */
    private $online = '0';

    /**
     * @var bool
     */
    private $effectiveOnline = '0';

    /**
     * @var int|null
     */
    private $activationStartTs;

    /**
     * @var int|null
     */
    private $activationEndTs;


    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set online.
     *
     * @param bool $online
     *
     * @return LsoActivation
     */
    public function setOnline($online)
    {
        $this->online = $online;

        return $this;
    }

    /**
     * Get online.
     *
     * @return bool
     */
    public function getOnline()
    {
        return $this->online;
    }

    /**
     * Set effectiveOnline.
     *
     * @param bool $effectiveOnline
     *
     * @return LsoActivation
     */
    public function setEffectiveOnline($effectiveOnline)
    {
        $this->effectiveOnline = $effectiveOnline;

        return $this;
    }

    /**
     * Get effectiveOnline.
     *
     * @return bool
     */
    public function getEffectiveOnline()
    {
        return $this->effectiveOnline;
    }

    /**
     * Set activationStartTs.
     *
     * @param int|null $activationStartTs
     *
     * @return LsoActivation
     */
    public function setActivationStartTs($activationStartTs = null)
    {
        $this->activationStartTs = $activationStartTs;

        return $this;
    }

    /**
     * Get activationStartTs.
     *
     * @return int|null
     */
    public function getActivationStartTs()
    {
        return $this->activationStartTs;
    }

    /**
     * Set activationEndTs.
     *
     * @param int|null $activationEndTs
     *
     * @return LsoActivation
     */
    public function setActivationEndTs($activationEndTs = null)
    {
        $this->activationEndTs = $activationEndTs;

        return $this;
    }

    /**
     * Get activationEndTs.
     *
     * @return int|null
     */
    public function getActivationEndTs()
    {
        return $this->activationEndTs;
    }
}
