<?php



/**
 * QplALome
 */
class QplALome
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $gapNumber = '0';

    /**
     * @var int
     */
    private $position = '0';

    /**
     * @var string|null
     */
    private $answerText;

    /**
     * @var float|null
     */
    private $points;

    /**
     * @var int|null
     */
    private $type;


    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplALome
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
     * Set gapNumber.
     *
     * @param int $gapNumber
     *
     * @return QplALome
     */
    public function setGapNumber($gapNumber)
    {
        $this->gapNumber = $gapNumber;

        return $this;
    }

    /**
     * Get gapNumber.
     *
     * @return int
     */
    public function getGapNumber()
    {
        return $this->gapNumber;
    }

    /**
     * Set position.
     *
     * @param int $position
     *
     * @return QplALome
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set answerText.
     *
     * @param string|null $answerText
     *
     * @return QplALome
     */
    public function setAnswerText($answerText = null)
    {
        $this->answerText = $answerText;

        return $this;
    }

    /**
     * Get answerText.
     *
     * @return string|null
     */
    public function getAnswerText()
    {
        return $this->answerText;
    }

    /**
     * Set points.
     *
     * @param float|null $points
     *
     * @return QplALome
     */
    public function setPoints($points = null)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return float|null
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set type.
     *
     * @param int|null $type
     *
     * @return QplALome
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int|null
     */
    public function getType()
    {
        return $this->type;
    }
}
