<?php



/**
 * TstAddtime
 */
class TstAddtime
{
    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var int
     */
    private $additionaltime = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get activeFi.
     *
     * @return int
     */
    public function getActiveFi()
    {
        return $this->activeFi;
    }

    /**
     * Set additionaltime.
     *
     * @param int $additionaltime
     *
     * @return TstAddtime
     */
    public function setAdditionaltime($additionaltime)
    {
        $this->additionaltime = $additionaltime;

        return $this;
    }

    /**
     * Get additionaltime.
     *
     * @return int
     */
    public function getAdditionaltime()
    {
        return $this->additionaltime;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstAddtime
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}
