<?php



/**
 * SvyQuestSkill
 */
class SvyQuestSkill
{
    /**
     * @var int
     */
    private $qId = '0';

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
     * Get qId.
     *
     * @return int
     */
    public function getQId()
    {
        return $this->qId;
    }

    /**
     * Set surveyId.
     *
     * @param int $surveyId
     *
     * @return SvyQuestSkill
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
     * @return SvyQuestSkill
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
     * @return SvyQuestSkill
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
}
