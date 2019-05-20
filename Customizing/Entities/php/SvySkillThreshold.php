<?php



/**
 * SvySkillThreshold
 */
class SvySkillThreshold
{
    /**
     * @var int
     */
    private $surveyId = '0';

    /**
     * @var int
     */
    private $baseSkillId = '0';

    /**
     * @var int
     */
    private $trefId = '0';

    /**
     * @var int
     */
    private $levelId = '0';

    /**
     * @var int
     */
    private $threshold = '0';


    /**
     * Set surveyId.
     *
     * @param int $surveyId
     *
     * @return SvySkillThreshold
     */
    public function setSurveyId($surveyId)
    {
        $this->surveyId = $surveyId;

        return $this;
    }

    /**
     * Get surveyId.
     *
     * @return int
     */
    public function getSurveyId()
    {
        return $this->surveyId;
    }

    /**
     * Set baseSkillId.
     *
     * @param int $baseSkillId
     *
     * @return SvySkillThreshold
     */
    public function setBaseSkillId($baseSkillId)
    {
        $this->baseSkillId = $baseSkillId;

        return $this;
    }

    /**
     * Get baseSkillId.
     *
     * @return int
     */
    public function getBaseSkillId()
    {
        return $this->baseSkillId;
    }

    /**
     * Set trefId.
     *
     * @param int $trefId
     *
     * @return SvySkillThreshold
     */
    public function setTrefId($trefId)
    {
        $this->trefId = $trefId;

        return $this;
    }

    /**
     * Get trefId.
     *
     * @return int
     */
    public function getTrefId()
    {
        return $this->trefId;
    }

    /**
     * Set levelId.
     *
     * @param int $levelId
     *
     * @return SvySkillThreshold
     */
    public function setLevelId($levelId)
    {
        $this->levelId = $levelId;

        return $this;
    }

    /**
     * Get levelId.
     *
     * @return int
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * Set threshold.
     *
     * @param int $threshold
     *
     * @return SvySkillThreshold
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * Get threshold.
     *
     * @return int
     */
    public function getThreshold()
    {
        return $this->threshold;
    }
}
