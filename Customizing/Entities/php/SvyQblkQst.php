<?php



/**
 * SvyQblkQst
 */
class SvyQblkQst
{
    /**
     * @var int
     */
    private $qblkQstId = '0';

    /**
     * @var int
     */
    private $surveyFi = '0';

    /**
     * @var int
     */
    private $questionblockFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';


    /**
     * Get qblkQstId.
     *
     * @return int
     */
    public function getQblkQstId()
    {
        return $this->qblkQstId;
    }

    /**
     * Set surveyFi.
     *
     * @param int $surveyFi
     *
     * @return SvyQblkQst
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
     * Set questionblockFi.
     *
     * @param int $questionblockFi
     *
     * @return SvyQblkQst
     */
    public function setQuestionblockFi($questionblockFi)
    {
        $this->questionblockFi = $questionblockFi;

        return $this;
    }

    /**
     * Get questionblockFi.
     *
     * @return int
     */
    public function getQuestionblockFi()
    {
        return $this->questionblockFi;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return SvyQblkQst
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
