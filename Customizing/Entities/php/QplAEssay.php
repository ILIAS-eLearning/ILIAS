<?php



/**
 * QplAEssay
 */
class QplAEssay
{
    /**
     * @var int
     */
    private $answerId = '0';

    /**
     * @var int|null
     */
    private $questionFi;

    /**
     * @var string|null
     */
    private $answertext;

    /**
     * @var float|null
     */
    private $points;


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
     * @param int|null $questionFi
     *
     * @return QplAEssay
     */
    public function setQuestionFi($questionFi = null)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int|null
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
     * @return QplAEssay
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
     * @param float|null $points
     *
     * @return QplAEssay
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
}
