<?php



/**
 * QplAClozeCombiRes
 */
class QplAClozeCombiRes
{
    /**
     * @var int
     */
    private $combinationId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $gapFi = '0';

    /**
     * @var int
     */
    private $rowId = '0';

    /**
     * @var string|null
     */
    private $answer;

    /**
     * @var float|null
     */
    private $points;

    /**
     * @var bool|null
     */
    private $bestSolution;


    /**
     * Set combinationId.
     *
     * @param int $combinationId
     *
     * @return QplAClozeCombiRes
     */
    public function setCombinationId($combinationId)
    {
        $this->combinationId = $combinationId;

        return $this;
    }

    /**
     * Get combinationId.
     *
     * @return int
     */
    public function getCombinationId()
    {
        return $this->combinationId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplAClozeCombiRes
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
     * Set gapFi.
     *
     * @param int $gapFi
     *
     * @return QplAClozeCombiRes
     */
    public function setGapFi($gapFi)
    {
        $this->gapFi = $gapFi;

        return $this;
    }

    /**
     * Get gapFi.
     *
     * @return int
     */
    public function getGapFi()
    {
        return $this->gapFi;
    }

    /**
     * Set rowId.
     *
     * @param int $rowId
     *
     * @return QplAClozeCombiRes
     */
    public function setRowId($rowId)
    {
        $this->rowId = $rowId;

        return $this;
    }

    /**
     * Get rowId.
     *
     * @return int
     */
    public function getRowId()
    {
        return $this->rowId;
    }

    /**
     * Set answer.
     *
     * @param string|null $answer
     *
     * @return QplAClozeCombiRes
     */
    public function setAnswer($answer = null)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer.
     *
     * @return string|null
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set points.
     *
     * @param float|null $points
     *
     * @return QplAClozeCombiRes
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
     * Set bestSolution.
     *
     * @param bool|null $bestSolution
     *
     * @return QplAClozeCombiRes
     */
    public function setBestSolution($bestSolution = null)
    {
        $this->bestSolution = $bestSolution;

        return $this;
    }

    /**
     * Get bestSolution.
     *
     * @return bool|null
     */
    public function getBestSolution()
    {
        return $this->bestSolution;
    }
}
