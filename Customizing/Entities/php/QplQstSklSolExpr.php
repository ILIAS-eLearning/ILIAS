<?php



/**
 * QplQstSklSolExpr
 */
class QplQstSklSolExpr
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $skillBaseFi = '0';

    /**
     * @var int
     */
    private $skillTrefFi = '0';

    /**
     * @var int
     */
    private $orderIndex = '0';

    /**
     * @var string
     */
    private $expression = ' ';

    /**
     * @var int
     */
    private $points = '0';


    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplQstSklSolExpr
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
     * Set skillBaseFi.
     *
     * @param int $skillBaseFi
     *
     * @return QplQstSklSolExpr
     */
    public function setSkillBaseFi($skillBaseFi)
    {
        $this->skillBaseFi = $skillBaseFi;

        return $this;
    }

    /**
     * Get skillBaseFi.
     *
     * @return int
     */
    public function getSkillBaseFi()
    {
        return $this->skillBaseFi;
    }

    /**
     * Set skillTrefFi.
     *
     * @param int $skillTrefFi
     *
     * @return QplQstSklSolExpr
     */
    public function setSkillTrefFi($skillTrefFi)
    {
        $this->skillTrefFi = $skillTrefFi;

        return $this;
    }

    /**
     * Get skillTrefFi.
     *
     * @return int
     */
    public function getSkillTrefFi()
    {
        return $this->skillTrefFi;
    }

    /**
     * Set orderIndex.
     *
     * @param int $orderIndex
     *
     * @return QplQstSklSolExpr
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }

    /**
     * Get orderIndex.
     *
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->orderIndex;
    }

    /**
     * Set expression.
     *
     * @param string $expression
     *
     * @return QplQstSklSolExpr
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * Get expression.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Set points.
     *
     * @param int $points
     *
     * @return QplQstSklSolExpr
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }
}
