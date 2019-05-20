<?php



/**
 * SvySvyQst
 */
class SvySvyQst
{
    /**
     * @var int
     */
    private $surveyQuestionId = '0';

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
    private $sequence = '0';

    /**
     * @var string|null
     */
    private $heading;

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get surveyQuestionId.
     *
     * @return int
     */
    public function getSurveyQuestionId()
    {
        return $this->surveyQuestionId;
    }

    /**
     * Set surveyFi.
     *
     * @param int $surveyFi
     *
     * @return SvySvyQst
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
     * @return SvySvyQst
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
     * Set sequence.
     *
     * @param int $sequence
     *
     * @return SvySvyQst
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
     * Set heading.
     *
     * @param string|null $heading
     *
     * @return SvySvyQst
     */
    public function setHeading($heading = null)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * Get heading.
     *
     * @return string|null
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvySvyQst
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
