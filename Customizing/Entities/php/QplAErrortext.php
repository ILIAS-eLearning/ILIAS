<?php



/**
 * QplAErrortext
 */
class QplAErrortext
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
     * @var string
     */
    private $textWrong = '';

    /**
     * @var string|null
     */
    private $textCorrect;

    /**
     * @var float
     */
    private $points = '0';

    /**
     * @var int
     */
    private $sequence = '0';


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
     * @return QplAErrortext
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
     * Set textWrong.
     *
     * @param string $textWrong
     *
     * @return QplAErrortext
     */
    public function setTextWrong($textWrong)
    {
        $this->textWrong = $textWrong;

        return $this;
    }

    /**
     * Get textWrong.
     *
     * @return string
     */
    public function getTextWrong()
    {
        return $this->textWrong;
    }

    /**
     * Set textCorrect.
     *
     * @param string|null $textCorrect
     *
     * @return QplAErrortext
     */
    public function setTextCorrect($textCorrect = null)
    {
        $this->textCorrect = $textCorrect;

        return $this;
    }

    /**
     * Get textCorrect.
     *
     * @return string|null
     */
    public function getTextCorrect()
    {
        return $this->textCorrect;
    }

    /**
     * Set points.
     *
     * @param float $points
     *
     * @return QplAErrortext
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
     * Set sequence.
     *
     * @param int $sequence
     *
     * @return QplAErrortext
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence.
     *
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }
}
