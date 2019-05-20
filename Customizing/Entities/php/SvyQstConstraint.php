<?php



/**
 * SvyQstConstraint
 */
class SvyQstConstraint
{
    /**
     * @var int
     */
    private $questionConstraintId = '0';

    /**
     * @var int
     */
    private $surveyFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $constraintFi = '0';


    /**
     * Get questionConstraintId.
     *
     * @return int
     */
    public function getQuestionConstraintId()
    {
        return $this->questionConstraintId;
    }

    /**
     * Set surveyFi.
     *
     * @param int $surveyFi
     *
     * @return SvyQstConstraint
     */
    public function setSurveyFi($surveyFi)
    {
        $this->surveyFi = $surveyFi;

        return $this;
    }

    /**
     * Get surveyFi.
     *
     * @return int
     */
    public function getSurveyFi()
    {
        return $this->surveyFi;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return SvyQstConstraint
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
     * Set constraintFi.
     *
     * @param int $constraintFi
     *
     * @return SvyQstConstraint
     */
    public function setConstraintFi($constraintFi)
    {
        $this->constraintFi = $constraintFi;

        return $this;
    }

    /**
     * Get constraintFi.
     *
     * @return int
     */
    public function getConstraintFi()
    {
        return $this->constraintFi;
    }
}
