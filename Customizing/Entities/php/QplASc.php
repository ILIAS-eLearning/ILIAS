<?php



/**
 * QplASc
 */
class QplASc
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
     * @return QplASc
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
     * @return QplASc
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
     * @return QplASc
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
     * @return QplASc
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
     * @return QplASc
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
     * @return QplASc
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
