<?php



/**
 * QplATextsubset
 */
class QplATextsubset
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
     * @return QplATextsubset
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
     * @return QplATextsubset
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
     * @return QplATextsubset
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
     * @return QplATextsubset
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
     * @return QplATextsubset
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
