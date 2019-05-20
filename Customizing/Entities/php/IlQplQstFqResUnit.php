<?php



/**
 * IlQplQstFqResUnit
 */
class IlQplQstFqResUnit
{
    /**
     * @var int
     */
    private $resultUnitId = '0';

    /**
     * @var string|null
     */
    private $result;

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $unitFi = '0';


    /**
     * Get resultUnitId.
     *
     * @return int
     */
    public function getResultUnitId()
    {
        return $this->resultUnitId;
    }

    /**
     * Set result.
     *
     * @param string|null $result
     *
     * @return IlQplQstFqResUnit
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
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return IlQplQstFqResUnit
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
     * Set unitFi.
     *
     * @param int $unitFi
     *
     * @return IlQplQstFqResUnit
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
}
