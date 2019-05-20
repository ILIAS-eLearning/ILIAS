<?php



/**
 * SvyQstOblig
 */
class SvyQstOblig
{
    /**
     * @var int
     */
    private $questionObligatoryId = '0';

    /**
     * @var int
     */
    private $surveyFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $obligatory = '1';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get questionObligatoryId.
     *
     * @return int
     */
    public function getQuestionObligatoryId()
    {
        return $this->questionObligatoryId;
    }

    /**
     * Set surveyFi.
     *
     * @param int $surveyFi
     *
     * @return SvyQstOblig
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
     * @return SvyQstOblig
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
     * Set obligatory.
     *
     * @param string|null $obligatory
     *
     * @return SvyQstOblig
     */
    public function setObligatory($obligatory = null)
    {
        $this->obligatory = $obligatory;

        return $this;
    }

    /**
     * Get obligatory.
     *
     * @return string|null
     */
    public function getObligatory()
    {
        return $this->obligatory;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyQstOblig
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
}
