<?php



/**
 * QplQstErrortext
 */
class QplQstErrortext
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string
     */
    private $errortext = '';

    /**
     * @var float
     */
    private $textsize = '100';

    /**
     * @var float
     */
    private $pointsWrong = '-1';


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
     * Set errortext.
     *
     * @param string $errortext
     *
     * @return QplQstErrortext
     */
    public function setErrortext($errortext)
    {
        $this->errortext = $errortext;

        return $this;
    }

    /**
     * Get errortext.
     *
     * @return string
     */
    public function getErrortext()
    {
        return $this->errortext;
    }

    /**
     * Set textsize.
     *
     * @param float $textsize
     *
     * @return QplQstErrortext
     */
    public function setTextsize($textsize)
    {
        $this->textsize = $textsize;

        return $this;
    }

    /**
     * Get textsize.
     *
     * @return float
     */
    public function getTextsize()
    {
        return $this->textsize;
    }

    /**
     * Set pointsWrong.
     *
     * @param float $pointsWrong
     *
     * @return QplQstErrortext
     */
    public function setPointsWrong($pointsWrong)
    {
        $this->pointsWrong = $pointsWrong;

        return $this;
    }

    /**
     * Get pointsWrong.
     *
     * @return float
     */
    public function getPointsWrong()
    {
        return $this->pointsWrong;
    }
}
