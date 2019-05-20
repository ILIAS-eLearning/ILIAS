<?php



/**
 * QplAImagemap
 */
class QplAImagemap
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
     * @var string|null
     */
    private $coords;

    /**
     * @var string|null
     */
    private $area;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var float
     */
    private $pointsUnchecked = '0';


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
     * @return QplAImagemap
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
     * @return QplAImagemap
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
     * @return QplAImagemap
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
     * @return QplAImagemap
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
     * Set coords.
     *
     * @param string|null $coords
     *
     * @return QplAImagemap
     */
    public function setCoords($coords = null)
    {
        $this->coords = $coords;

        return $this;
    }

    /**
     * Get coords.
     *
     * @return string|null
     */
    public function getCoords()
    {
        return $this->coords;
    }

    /**
     * Set area.
     *
     * @param string|null $area
     *
     * @return QplAImagemap
     */
    public function setArea($area = null)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area.
     *
     * @return string|null
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplAImagemap
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
     * Set pointsUnchecked.
     *
     * @param float $pointsUnchecked
     *
     * @return QplAImagemap
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
}
