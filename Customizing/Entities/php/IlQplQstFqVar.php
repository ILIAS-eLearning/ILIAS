<?php



/**
 * IlQplQstFqVar
 */
class IlQplQstFqVar
{
    /**
     * @var int
     */
    private $variableId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $variable;

    /**
     * @var float
     */
    private $rangeMin = '0';

    /**
     * @var float
     */
    private $rangeMax = '0';

    /**
     * @var int
     */
    private $unitFi = '0';

    /**
     * @var int
     */
    private $stepDimMin = '0';

    /**
     * @var int
     */
    private $stepDimMax = '0';

    /**
     * @var int
     */
    private $varprecision = '0';

    /**
     * @var int
     */
    private $intprecision = '1';

    /**
     * @var string|null
     */
    private $rangeMinTxt;

    /**
     * @var string|null
     */
    private $rangeMaxTxt;


    /**
     * Get variableId.
     *
     * @return int
     */
    public function getVariableId()
    {
        return $this->variableId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return IlQplQstFqVar
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
     * Set variable.
     *
     * @param string|null $variable
     *
     * @return IlQplQstFqVar
     */
    public function setVariable($variable = null)
    {
        $this->variable = $variable;

        return $this;
    }

    /**
     * Get variable.
     *
     * @return string|null
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * Set rangeMin.
     *
     * @param float $rangeMin
     *
     * @return IlQplQstFqVar
     */
    public function setRangeMin($rangeMin)
    {
        $this->rangeMin = $rangeMin;

        return $this;
    }

    /**
     * Get rangeMin.
     *
     * @return float
     */
    public function getRangeMin()
    {
        return $this->rangeMin;
    }

    /**
     * Set rangeMax.
     *
     * @param float $rangeMax
     *
     * @return IlQplQstFqVar
     */
    public function setRangeMax($rangeMax)
    {
        $this->rangeMax = $rangeMax;

        return $this;
    }

    /**
     * Get rangeMax.
     *
     * @return float
     */
    public function getRangeMax()
    {
        return $this->rangeMax;
    }

    /**
     * Set unitFi.
     *
     * @param int $unitFi
     *
     * @return IlQplQstFqVar
     */
    public function setUnitFi($unitFi)
    {
        $this->unitFi = $unitFi;

        return $this;
    }

    /**
     * Get unitFi.
     *
     * @return int
     */
    public function getUnitFi()
    {
        return $this->unitFi;
    }

    /**
     * Set stepDimMin.
     *
     * @param int $stepDimMin
     *
     * @return IlQplQstFqVar
     */
    public function setStepDimMin($stepDimMin)
    {
        $this->stepDimMin = $stepDimMin;

        return $this;
    }

    /**
     * Get stepDimMin.
     *
     * @return int
     */
    public function getStepDimMin()
    {
        return $this->stepDimMin;
    }

    /**
     * Set stepDimMax.
     *
     * @param int $stepDimMax
     *
     * @return IlQplQstFqVar
     */
    public function setStepDimMax($stepDimMax)
    {
        $this->stepDimMax = $stepDimMax;

        return $this;
    }

    /**
     * Get stepDimMax.
     *
     * @return int
     */
    public function getStepDimMax()
    {
        return $this->stepDimMax;
    }

    /**
     * Set varprecision.
     *
     * @param int $varprecision
     *
     * @return IlQplQstFqVar
     */
    public function setVarprecision($varprecision)
    {
        $this->varprecision = $varprecision;

        return $this;
    }

    /**
     * Get varprecision.
     *
     * @return int
     */
    public function getVarprecision()
    {
        return $this->varprecision;
    }

    /**
     * Set intprecision.
     *
     * @param int $intprecision
     *
     * @return IlQplQstFqVar
     */
    public function setIntprecision($intprecision)
    {
        $this->intprecision = $intprecision;

        return $this;
    }

    /**
     * Get intprecision.
     *
     * @return int
     */
    public function getIntprecision()
    {
        return $this->intprecision;
    }

    /**
     * Set rangeMinTxt.
     *
     * @param string|null $rangeMinTxt
     *
     * @return IlQplQstFqVar
     */
    public function setRangeMinTxt($rangeMinTxt = null)
    {
        $this->rangeMinTxt = $rangeMinTxt;

        return $this;
    }

    /**
     * Get rangeMinTxt.
     *
     * @return string|null
     */
    public function getRangeMinTxt()
    {
        return $this->rangeMinTxt;
    }

    /**
     * Set rangeMaxTxt.
     *
     * @param string|null $rangeMaxTxt
     *
     * @return IlQplQstFqVar
     */
    public function setRangeMaxTxt($rangeMaxTxt = null)
    {
        $this->rangeMaxTxt = $rangeMaxTxt;

        return $this;
    }

    /**
     * Get rangeMaxTxt.
     *
     * @return string|null
     */
    public function getRangeMaxTxt()
    {
        return $this->rangeMaxTxt;
    }
}
