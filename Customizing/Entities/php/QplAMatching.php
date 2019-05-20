<?php



/**
 * QplAMatching
 */
class QplAMatching
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
     * @var int
     */
    private $termFi = '0';

    /**
     * @var float
     */
    private $points = '0';

    /**
     * @var int
     */
    private $definitionFi = '0';


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
     * @return QplAMatching
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
     * Set termFi.
     *
     * @param int $termFi
     *
     * @return QplAMatching
     */
    public function setTermFi($termFi)
    {
        $this->termFi = $termFi;

        return $this;
    }

    /**
     * Get termFi.
     *
     * @return int
     */
    public function getTermFi()
    {
        return $this->termFi;
    }

    /**
     * Set points.
     *
     * @param float $points
     *
     * @return QplAMatching
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
     * Set definitionFi.
     *
     * @param int $definitionFi
     *
     * @return QplAMatching
     */
    public function setDefinitionFi($definitionFi)
    {
        $this->definitionFi = $definitionFi;

        return $this;
    }

    /**
     * Get definitionFi.
     *
     * @return int
     */
    public function getDefinitionFi()
    {
        return $this->definitionFi;
    }
}
