<?php



/**
 * QplACloze
 */
class QplACloze
{
    /**
     * @var int
     */
    private $answerId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $shuffle = '1';

    /**
     * @var string|null
     */
    private $answertext;

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
    private $gapId = '0';

    /**
     * @var string|null
     */
    private $clozeType = '0';

    /**
     * @var string|null
     */
    private $lowerlimit = '0';

    /**
     * @var string|null
     */
    private $upperlimit = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int
     */
    private $gapSize = '0';


    /**
     * Get answerId.
     *
     * @return int
     */
    public function getAnswerId()
    {
        return $this->answerId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplACloze
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
     * Set shuffle.
     *
     * @param string|null $shuffle
     *
     * @return QplACloze
     */
    public function setShuffle($shuffle = null)
    {
        $this->shuffle = $shuffle;

        return $this;
    }

    /**
     * Get shuffle.
     *
     * @return string|null
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * Set answertext.
     *
     * @param string|null $answertext
     *
     * @return QplACloze
     */
    public function setAnswertext($answertext = null)
    {
        $this->answertext = $answertext;

        return $this;
    }

    /**
     * Get answertext.
     *
     * @return string|null
     */
    public function getAnswertext()
    {
        return $this->answertext;
    }

    /**
     * Set points.
     *
     * @param float $points
     *
     * @return QplACloze
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
     * @return QplACloze
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
     * Set gapId.
     *
     * @param int $gapId
     *
     * @return QplACloze
     */
    public function setGapId($gapId)
    {
        $this->gapId = $gapId;

        return $this;
    }

    /**
     * Get gapId.
     *
     * @return int
     */
    public function getGapId()
    {
        return $this->gapId;
    }

    /**
     * Set clozeType.
     *
     * @param string|null $clozeType
     *
     * @return QplACloze
     */
    public function setClozeType($clozeType = null)
    {
        $this->clozeType = $clozeType;

        return $this;
    }

    /**
     * Get clozeType.
     *
     * @return string|null
     */
    public function getClozeType()
    {
        return $this->clozeType;
    }

    /**
     * Set lowerlimit.
     *
     * @param string|null $lowerlimit
     *
     * @return QplACloze
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
     * @return QplACloze
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
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplACloze
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

    /**
     * Set gapSize.
     *
     * @param int $gapSize
     *
     * @return QplACloze
     */
    public function setGapSize($gapSize)
    {
        $this->gapSize = $gapSize;

        return $this;
    }

    /**
     * Get gapSize.
     *
     * @return int
     */
    public function getGapSize()
    {
        return $this->gapSize;
    }
}
