<?php



/**
 * QplNumRange
 */
class QplNumRange
{
    /**
     * @var int
     */
    private $rangeId = '0';

    /**
     * @var string|null
     */
    private $lowerlimit = '0';

    /**
     * @var string|null
     */
    private $upperlimit = '0';

    /**
     * @var float
     */
    private $points = '0';

    /**
     * @var int
     */
    private $aorder = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get rangeId.
     *
     * @return int
     */
    public function getRangeId()
    {
        return $this->rangeId;
    }

    /**
     * Set lowerlimit.
     *
     * @param string|null $lowerlimit
     *
     * @return QplNumRange
     */
    public function setLowerlimit($lowerlimit = null)
    {
        $this->lowerlimit = $lowerlimit;

        return $this;
    }

    /**
     * Get lowerlimit.
     *
     * @return string|null
     */
    public function getLowerlimit()
    {
        return $this->lowerlimit;
    }

    /**
     * Set upperlimit.
     *
     * @param string|null $upperlimit
     *
     * @return QplNumRange
     */
    public function setUpperlimit($upperlimit = null)
    {
        $this->upperlimit = $upperlimit;

        return $this;
    }

    /**
     * Get upperlimit.
     *
     * @return string|null
     */
    public function getUpperlimit()
    {
        return $this->upperlimit;
    }

    /**
     * Set points.
     *
     * @param float $points
     *
     * @return QplNumRange
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return float
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set aorder.
     *
     * @param int $aorder
     *
     * @return QplNumRange
     */
    public function setAorder($aorder)
    {
        $this->aorder = $aorder;

        return $this;
    }

    /**
     * Get aorder.
     *
     * @return int
     */
    public function getAorder()
    {
        return $this->aorder;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplNumRange
     */
    public function setQuestionFi($questionFi)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplNumRange
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
