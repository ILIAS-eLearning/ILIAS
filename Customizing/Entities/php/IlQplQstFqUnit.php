<?php



/**
 * IlQplQstFqUnit
 */
class IlQplQstFqUnit
{
    /**
     * @var int
     */
    private $unitId = '0';

    /**
     * @var string|null
     */
    private $unit;

    /**
     * @var float
     */
    private $factor = '0';

    /**
     * @var int
     */
    private $baseunitFi = '0';

    /**
     * @var int
     */
    private $categoryFi = '0';

    /**
     * @var int
     */
    private $sequence = '0';

    /**
     * @var int
     */
    private $questionFi = '0';


    /**
     * Get unitId.
     *
     * @return int
     */
    public function getUnitId()
    {
        return $this->unitId;
    }

    /**
     * Set unit.
     *
     * @param string|null $unit
     *
     * @return IlQplQstFqUnit
     */
    public function setUnit($unit = null)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit.
     *
     * @return string|null
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set factor.
     *
     * @param float $factor
     *
     * @return IlQplQstFqUnit
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;

        return $this;
    }

    /**
     * Get factor.
     *
     * @return float
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * Set baseunitFi.
     *
     * @param int $baseunitFi
     *
     * @return IlQplQstFqUnit
     */
    public function setBaseunitFi($baseunitFi)
    {
        $this->baseunitFi = $baseunitFi;

        return $this;
    }

    /**
     * Get baseunitFi.
     *
     * @return int
     */
    public function getBaseunitFi()
    {
        return $this->baseunitFi;
    }

    /**
     * Set categoryFi.
     *
     * @param int $categoryFi
     *
     * @return IlQplQstFqUnit
     */
    public function setCategoryFi($categoryFi)
    {
        $this->categoryFi = $categoryFi;

        return $this;
    }

    /**
     * Get categoryFi.
     *
     * @return int
     */
    public function getCategoryFi()
    {
        return $this->categoryFi;
    }

    /**
     * Set sequence.
     *
     * @param int $sequence
     *
     * @return IlQplQstFqUnit
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

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return IlQplQstFqUnit
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
}
