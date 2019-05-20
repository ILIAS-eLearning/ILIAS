<?php



/**
 * TstRndQplTitle
 */
class TstRndQplTitle
{
    /**
     * @var int
     */
    private $titleId = '0';

    /**
     * @var int
     */
    private $qplFi = '0';

    /**
     * @var int
     */
    private $tstFi = '0';

    /**
     * @var string
     */
    private $qplTitle = '';


    /**
     * Get titleId.
     *
     * @return int
     */
    public function getTitleId()
    {
        return $this->titleId;
    }

    /**
     * Set qplFi.
     *
     * @param int $qplFi
     *
     * @return TstRndQplTitle
     */
    public function setQplFi($qplFi)
    {
        $this->qplFi = $qplFi;

        return $this;
    }

    /**
     * Get qplFi.
     *
     * @return int
     */
    public function getQplFi()
    {
        return $this->qplFi;
    }

    /**
     * Set tstFi.
     *
     * @param int $tstFi
     *
     * @return TstRndQplTitle
     */
    public function setTstFi($tstFi)
    {
        $this->tstFi = $tstFi;

        return $this;
    }

    /**
     * Get tstFi.
     *
     * @return int
     */
    public function getTstFi()
    {
        return $this->tstFi;
    }

    /**
     * Set qplTitle.
     *
     * @param string $qplTitle
     *
     * @return TstRndQplTitle
     */
    public function setQplTitle($qplTitle)
    {
        $this->qplTitle = $qplTitle;

        return $this;
    }

    /**
     * Get qplTitle.
     *
     * @return string
     */
    public function getQplTitle()
    {
        return $this->qplTitle;
    }
}
