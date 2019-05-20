<?php



/**
 * IlQplQstFqRes
 */
class IlQplQstFqRes
{
    /**
     * @var int
     */
    private $resultId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $result;

    /**
     * @var float
     */
    private $rangeMin = '0';

    /**
     * @var float
     */
    private $rangeMax = '0';

    /**
     * @var float
     */
    private $tolerance = '0';

    /**
     * @var int
     */
    private $unitFi = '0';

    /**
     * @var string|null
     */
    private $formula;

    /**
     * @var int
     */
    private $ratingSimple = '1';

    /**
     * @var float
     */
    private $ratingSign = '0.25';

    /**
     * @var float
     */
    private $ratingValue = '0.25';

    /**
     * @var float
     */
    private $ratingUnit = '0.25';

    /**
     * @var float
     */
    private $points = '0';

    /**
     * @var int
     */
    private $resprecision = '0';

    /**
     * @var int
     */
    private $resultType = '0';

    /**
     * @var string|null
     */
    private $rangeMinTxt;

    /**
     * @var string|null
     */
    private $rangeMaxTxt;


    /**
     * Get resultId.
     *
     * @return int
     */
    public function getResultId()
    {
        return $this->resultId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return IlQplQstFqRes
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
     * Set result.
     *
     * @param string|null $result
     *
     * @return IlQplQstFqRes
     */
    public function setResult($result = null)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result.
     *
     * @return string|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set rangeMin.
     *
     * @param float $rangeMin
     *
     * @return IlQplQstFqRes
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
     * @return IlQplQstFqRes
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
     * Set tolerance.
     *
     * @param float $tolerance
     *
     * @return IlQplQstFqRes
     */
    public function setTolerance($tolerance)
    {
        $this->tolerance = $tolerance;

        return $this;
    }

    /**
     * Get tolerance.
     *
     * @return float
     */
    public function getTolerance()
    {
        return $this->tolerance;
    }

    /**
     * Set unitFi.
     *
     * @param int $unitFi
     *
     * @return IlQplQstFqRes
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
     * Set formula.
     *
     * @param string|null $formula
     *
     * @return IlQplQstFqRes
     */
    public function setFormula($formula = null)
    {
        $this->formula = $formula;

        return $this;
    }

    /**
     * Get formula.
     *
     * @return string|null
     */
    public function getFormula()
    {
        return $this->formula;
    }

    /**
     * Set ratingSimple.
     *
     * @param int $ratingSimple
     *
     * @return IlQplQstFqRes
     */
    public function setRatingSimple($ratingSimple)
    {
        $this->ratingSimple = $ratingSimple;

        return $this;
    }

    /**
     * Get ratingSimple.
     *
     * @return int
     */
    public function getRatingSimple()
    {
        return $this->ratingSimple;
    }

    /**
     * Set ratingSign.
     *
     * @param float $ratingSign
     *
     * @return IlQplQstFqRes
     */
    public function setRatingSign($ratingSign)
    {
        $this->ratingSign = $ratingSign;

        return $this;
    }

    /**
     * Get ratingSign.
     *
     * @return float
     */
    public function getRatingSign()
    {
        return $this->ratingSign;
    }

    /**
     * Set ratingValue.
     *
     * @param float $ratingValue
     *
     * @return IlQplQstFqRes
     */
    public function setRatingValue($ratingValue)
    {
        $this->ratingValue = $ratingValue;

        return $this;
    }

    /**
     * Get ratingValue.
     *
     * @return float
     */
    public function getRatingValue()
    {
        return $this->ratingValue;
    }

    /**
     * Set ratingUnit.
     *
     * @param float $ratingUnit
     *
     * @return IlQplQstFqRes
     */
    public function setRatingUnit($ratingUnit)
    {
        $this->ratingUnit = $ratingUnit;

        return $this;
    }

    /**
     * Get ratingUnit.
     *
     * @return float
     */
    public function getRatingUnit()
    {
        return $this->ratingUnit;
    }

    /**
     * Set points.
     *
     * @param float $points
     *
     * @return IlQplQstFqRes
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
     * Set resprecision.
     *
     * @param int $resprecision
     *
     * @return IlQplQstFqRes
     */
    public function setResprecision($resprecision)
    {
        $this->resprecision = $resprecision;

        return $this;
    }

    /**
     * Get resprecision.
     *
     * @return int
     */
    public function getResprecision()
    {
        return $this->resprecision;
    }

    /**
     * Set resultType.
     *
     * @param int $resultType
     *
     * @return IlQplQstFqRes
     */
    public function setResultType($resultType)
    {
        $this->resultType = $resultType;

        return $this;
    }

    /**
     * Get resultType.
     *
     * @return int
     */
    public function getResultType()
    {
        return $this->resultType;
    }

    /**
     * Set rangeMinTxt.
     *
     * @param string|null $rangeMinTxt
     *
     * @return IlQplQstFqRes
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
     * @return IlQplQstFqRes
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
