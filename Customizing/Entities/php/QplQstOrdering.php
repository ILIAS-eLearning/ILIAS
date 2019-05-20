<?php



/**
 * QplQstOrdering
 */
class QplQstOrdering
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $orderingType = '1';

    /**
     * @var int
     */
    private $thumbGeometry = '100';

    /**
     * @var int|null
     */
    private $elementHeight;

    /**
     * @var int
     */
    private $scoringType = '0';

    /**
     * @var float
     */
    private $reducedPoints = '0';


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
     * Set orderingType.
     *
     * @param string|null $orderingType
     *
     * @return QplQstOrdering
     */
    public function setOrderingType($orderingType = null)
    {
        $this->orderingType = $orderingType;

        return $this;
    }

    /**
     * Get orderingType.
     *
     * @return string|null
     */
    public function getOrderingType()
    {
        return $this->orderingType;
    }

    /**
     * Set thumbGeometry.
     *
     * @param int $thumbGeometry
     *
     * @return QplQstOrdering
     */
    public function setThumbGeometry($thumbGeometry)
    {
        $this->thumbGeometry = $thumbGeometry;

        return $this;
    }

    /**
     * Get thumbGeometry.
     *
     * @return int
     */
    public function getThumbGeometry()
    {
        return $this->thumbGeometry;
    }

    /**
     * Set elementHeight.
     *
     * @param int|null $elementHeight
     *
     * @return QplQstOrdering
     */
    public function setElementHeight($elementHeight = null)
    {
        $this->elementHeight = $elementHeight;

        return $this;
    }

    /**
     * Get elementHeight.
     *
     * @return int|null
     */
    public function getElementHeight()
    {
        return $this->elementHeight;
    }

    /**
     * Set scoringType.
     *
     * @param int $scoringType
     *
     * @return QplQstOrdering
     */
    public function setScoringType($scoringType)
    {
        $this->scoringType = $scoringType;

        return $this;
    }

    /**
     * Get scoringType.
     *
     * @return int
     */
    public function getScoringType()
    {
        return $this->scoringType;
    }

    /**
     * Set reducedPoints.
     *
     * @param float $reducedPoints
     *
     * @return QplQstOrdering
     */
    public function setReducedPoints($reducedPoints)
    {
        $this->reducedPoints = $reducedPoints;

        return $this;
    }

    /**
     * Get reducedPoints.
     *
     * @return float
     */
    public function getReducedPoints()
    {
        return $this->reducedPoints;
    }
}
