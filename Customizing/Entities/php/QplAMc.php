<?php



/**
 * QplAMc
 */
class QplAMc
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
    private $answertext;

    /**
     * @var string|null
     */
    private $imagefile;

    /**
     * @var float
     */
    private $points = '0';

    /**
     * @var float
     */
    private $pointsUnchecked = '0';

    /**
     * @var int
     */
    private $aorder = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


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
     * @return QplAMc
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
     * Set answertext.
     *
     * @param string|null $answertext
     *
     * @return QplAMc
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
     * Set imagefile.
     *
     * @param string|null $imagefile
     *
     * @return QplAMc
     */
    public function setImagefile($imagefile = null)
    {
        $this->imagefile = $imagefile;

        return $this;
    }

    /**
     * Get imagefile.
     *
     * @return string|null
     */
    public function getImagefile()
    {
        return $this->imagefile;
    }

    /**
     * Set points.
     *
     * @param float $points
     *
     * @return QplAMc
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
     * Set pointsUnchecked.
     *
     * @param float $pointsUnchecked
     *
     * @return QplAMc
     */
    public function setPointsUnchecked($pointsUnchecked)
    {
        $this->pointsUnchecked = $pointsUnchecked;

        return $this;
    }

    /**
     * Get pointsUnchecked.
     *
     * @return float
     */
    public function getPointsUnchecked()
    {
        return $this->pointsUnchecked;
    }

    /**
     * Set aorder.
     *
     * @param int $aorder
     *
     * @return QplAMc
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
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplAMc
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
