<?php



/**
 * QplQstSklAssigns
 */
class QplQstSklAssigns
{
    /**
     * @var int
     */
    private $objFi = '0';

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
    private $skillPoints = '0';

    /**
     * @var string|null
     */
    private $evalMode;


    /**
     * Set objFi.
     *
     * @param int $objFi
     *
     * @return QplQstSklAssigns
     */
    public function setObjFi($objFi)
    {
        $this->objFi = $objFi;

        return $this;
    }

    /**
     * Get objFi.
     *
     * @return int
     */
    public function getObjFi()
    {
        return $this->objFi;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplQstSklAssigns
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
     * @return QplQstSklAssigns
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
     * @return QplQstSklAssigns
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
     * Set skillPoints.
     *
     * @param int $skillPoints
     *
     * @return QplQstSklAssigns
     */
    public function setSkillPoints($skillPoints)
    {
        $this->skillPoints = $skillPoints;

        return $this;
    }

    /**
     * Get skillPoints.
     *
     * @return int
     */
    public function getSkillPoints()
    {
        return $this->skillPoints;
    }

    /**
     * Set evalMode.
     *
     * @param string|null $evalMode
     *
     * @return QplQstSklAssigns
     */
    public function setEvalMode($evalMode = null)
    {
        $this->evalMode = $evalMode;

        return $this;
    }

    /**
     * Get evalMode.
     *
     * @return string|null
     */
    public function getEvalMode()
    {
        return $this->evalMode;
    }
}
