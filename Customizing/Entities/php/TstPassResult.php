<?php



/**
 * TstPassResult
 */
class TstPassResult
{
    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var int
     */
    private $pass = '0';

    /**
     * @var float
     */
    private $points = '0';

    /**
     * @var float
     */
    private $maxpoints = '0';

    /**
     * @var int
     */
    private $questioncount = '0';

    /**
     * @var int
     */
    private $answeredquestions = '0';

    /**
     * @var int
     */
    private $workingtime = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int|null
     */
    private $hintCount = '0';

    /**
     * @var float|null
     */
    private $hintPoints = '0';

    /**
     * @var bool
     */
    private $obligationsAnswered = '1';

    /**
     * @var string|null
     */
    private $examId;


    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstPassResult
     */
    public function setActiveFi($activeFi)
    {
        $this->activeFi = $activeFi;

        return $this;
    }

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
     * Set pass.
     *
     * @param int $pass
     *
     * @return TstPassResult
     */
    public function setPass($pass)
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * Get pass.
     *
     * @return int
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Set points.
     *
     * @param float $points
     *
     * @return TstPassResult
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
     * Set maxpoints.
     *
     * @param float $maxpoints
     *
     * @return TstPassResult
     */
    public function setMaxpoints($maxpoints)
    {
        $this->maxpoints = $maxpoints;

        return $this;
    }

    /**
     * Get maxpoints.
     *
     * @return float
     */
    public function getMaxpoints()
    {
        return $this->maxpoints;
    }

    /**
     * Set questioncount.
     *
     * @param int $questioncount
     *
     * @return TstPassResult
     */
    public function setQuestioncount($questioncount)
    {
        $this->questioncount = $questioncount;

        return $this;
    }

    /**
     * Get questioncount.
     *
     * @return int
     */
    public function getQuestioncount()
    {
        return $this->questioncount;
    }

    /**
     * Set answeredquestions.
     *
     * @param int $answeredquestions
     *
     * @return TstPassResult
     */
    public function setAnsweredquestions($answeredquestions)
    {
        $this->answeredquestions = $answeredquestions;

        return $this;
    }

    /**
     * Get answeredquestions.
     *
     * @return int
     */
    public function getAnsweredquestions()
    {
        return $this->answeredquestions;
    }

    /**
     * Set workingtime.
     *
     * @param int $workingtime
     *
     * @return TstPassResult
     */
    public function setWorkingtime($workingtime)
    {
        $this->workingtime = $workingtime;

        return $this;
    }

    /**
     * Get workingtime.
     *
     * @return int
     */
    public function getWorkingtime()
    {
        return $this->workingtime;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstPassResult
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
     * Set hintCount.
     *
     * @param int|null $hintCount
     *
     * @return TstPassResult
     */
    public function setHintCount($hintCount = null)
    {
        $this->hintCount = $hintCount;

        return $this;
    }

    /**
     * Get hintCount.
     *
     * @return int|null
     */
    public function getHintCount()
    {
        return $this->hintCount;
    }

    /**
     * Set hintPoints.
     *
     * @param float|null $hintPoints
     *
     * @return TstPassResult
     */
    public function setHintPoints($hintPoints = null)
    {
        $this->hintPoints = $hintPoints;

        return $this;
    }

    /**
     * Get hintPoints.
     *
     * @return float|null
     */
    public function getHintPoints()
    {
        return $this->hintPoints;
    }

    /**
     * Set obligationsAnswered.
     *
     * @param bool $obligationsAnswered
     *
     * @return TstPassResult
     */
    public function setObligationsAnswered($obligationsAnswered)
    {
        $this->obligationsAnswered = $obligationsAnswered;

        return $this;
    }

    /**
     * Get obligationsAnswered.
     *
     * @return bool
     */
    public function getObligationsAnswered()
    {
        return $this->obligationsAnswered;
    }

    /**
     * Set examId.
     *
     * @param string|null $examId
     *
     * @return TstPassResult
     */
    public function setExamId($examId = null)
    {
        $this->examId = $examId;

        return $this;
    }

    /**
     * Get examId.
     *
     * @return string|null
     */
    public function getExamId()
    {
        return $this->examId;
    }
}
