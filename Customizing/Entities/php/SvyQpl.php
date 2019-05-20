<?php



/**
 * SvyQpl
 */
class SvyQpl
{
    /**
     * @var int
     */
    private $idQuestionpool = '0';

    /**
     * @var int
     */
    private $objFi = '0';

    /**
     * @var string|null
     */
    private $isonline = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get idQuestionpool.
     *
     * @return int
     */
    public function getIdQuestionpool()
    {
        return $this->idQuestionpool;
    }

    /**
     * Set objFi.
     *
     * @param int $objFi
     *
     * @return SvyQpl
     */
    public function setObjFi($objFi)
    {
        $this->objFi = $objFi;

        return $this;
    }

    /**
     * Get objFi.
     *
     * @return int
     */
    public function getObjFi()
    {
        return $this->objFi;
    }

    /**
     * Set isonline.
     *
     * @param string|null $isonline
     *
     * @return SvyQpl
     */
    public function setIsonline($isonline = null)
    {
        $this->isonline = $isonline;

        return $this;
    }

    /**
     * Get isonline.
     *
     * @return string|null
     */
    public function getIsonline()
    {
        return $this->isonline;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyQpl
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
