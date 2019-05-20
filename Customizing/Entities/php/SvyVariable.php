<?php



/**
 * SvyVariable
 */
class SvyVariable
{
    /**
     * @var int
     */
    private $variableId = '0';

    /**
     * @var int
     */
    private $categoryFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var float|null
     */
    private $value1;

    /**
     * @var float|null
     */
    private $value2;

    /**
     * @var int
     */
    private $sequence = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int
     */
    private $other = '0';

    /**
     * @var int|null
     */
    private $scale;


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
     * Set categoryFi.
     *
     * @param int $categoryFi
     *
     * @return SvyVariable
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
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return SvyVariable
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
     * Set value1.
     *
     * @param float|null $value1
     *
     * @return SvyVariable
     */
    public function setValue1($value1 = null)
    {
        $this->value1 = $value1;

        return $this;
    }

    /**
     * Get value1.
     *
     * @return float|null
     */
    public function getValue1()
    {
        return $this->value1;
    }

    /**
     * Set value2.
     *
     * @param float|null $value2
     *
     * @return SvyVariable
     */
    public function setValue2($value2 = null)
    {
        $this->value2 = $value2;

        return $this;
    }

    /**
     * Get value2.
     *
     * @return float|null
     */
    public function getValue2()
    {
        return $this->value2;
    }

    /**
     * Set sequence.
     *
     * @param int $sequence
     *
     * @return SvyVariable
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
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyVariable
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set other.
     *
     * @param int $other
     *
     * @return SvyVariable
     */
    public function setOther($other)
    {
        $this->other = $other;

        return $this;
    }

    /**
     * Get other.
     *
     * @return int
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * Set scale.
     *
     * @param int|null $scale
     *
     * @return SvyVariable
     */
    public function setScale($scale = null)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * Get scale.
     *
     * @return int|null
     */
    public function getScale()
    {
        return $this->scale;
    }
}
