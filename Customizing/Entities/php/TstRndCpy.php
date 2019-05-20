<?php



/**
 * TstRndCpy
 */
class TstRndCpy
{
    /**
     * @var int
     */
    private $copyId = '0';

    /**
     * @var int
     */
    private $tstFi = '0';

    /**
     * @var int
     */
    private $qstFi = '0';

    /**
     * @var int
     */
    private $qplFi = '0';


    /**
     * Get copyId.
     *
     * @return int
     */
    public function getCopyId()
    {
        return $this->copyId;
    }

    /**
     * Set tstFi.
     *
     * @param int $tstFi
     *
     * @return TstRndCpy
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
     * Set qstFi.
     *
     * @param int $qstFi
     *
     * @return TstRndCpy
     */
    public function setQstFi($qstFi)
    {
        $this->qstFi = $qstFi;

        return $this;
    }

    /**
     * Get qstFi.
     *
     * @return int
     */
    public function getQstFi()
    {
        return $this->qstFi;
    }

    /**
     * Set qplFi.
     *
     * @param int $qplFi
     *
     * @return TstRndCpy
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
}
