<?php



/**
 * TstQstSolved
 */
class TstQstSolved
{
    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var bool
     */
    private $solved = '0';


    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstQstSolved
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
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return TstQstSolved
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
     * Set solved.
     *
     * @param bool $solved
     *
     * @return TstQstSolved
     */
    public function setSolved($solved)
    {
        $this->solved = $solved;

        return $this;
    }

    /**
     * Get solved.
     *
     * @return bool
     */
    public function getSolved()
    {
        return $this->solved;
    }
}
