<?php



/**
 * QplQstTextsubset
 */
class QplQstTextsubset
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $textgapRating;

    /**
     * @var int|null
     */
    private $correctanswers = '0';


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
     * Set textgapRating.
     *
     * @param string|null $textgapRating
     *
     * @return QplQstTextsubset
     */
    public function setTextgapRating($textgapRating = null)
    {
        $this->textgapRating = $textgapRating;

        return $this;
    }

    /**
     * Get textgapRating.
     *
     * @return string|null
     */
    public function getTextgapRating()
    {
        return $this->textgapRating;
    }

    /**
     * Set correctanswers.
     *
     * @param int|null $correctanswers
     *
     * @return QplQstTextsubset
     */
    public function setCorrectanswers($correctanswers = null)
    {
        $this->correctanswers = $correctanswers;

        return $this;
    }

    /**
     * Get correctanswers.
     *
     * @return int|null
     */
    public function getCorrectanswers()
    {
        return $this->correctanswers;
    }
}
