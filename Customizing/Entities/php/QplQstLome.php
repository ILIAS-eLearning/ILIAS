<?php



/**
 * QplQstLome
 */
class QplQstLome
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var bool
     */
    private $shuffleAnswers = '0';

    /**
     * @var string
     */
    private $answerType = 'singleLine';

    /**
     * @var int
     */
    private $feedbackSetting = '1';

    /**
     * @var string|null
     */
    private $longMenuText;

    /**
     * @var bool|null
     */
    private $minAutoComplete = '3';

    /**
     * @var bool|null
     */
    private $identicalScoring = '1';


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
     * Set shuffleAnswers.
     *
     * @param bool $shuffleAnswers
     *
     * @return QplQstLome
     */
    public function setShuffleAnswers($shuffleAnswers)
    {
        $this->shuffleAnswers = $shuffleAnswers;

        return $this;
    }

    /**
     * Get shuffleAnswers.
     *
     * @return bool
     */
    public function getShuffleAnswers()
    {
        return $this->shuffleAnswers;
    }

    /**
     * Set answerType.
     *
     * @param string $answerType
     *
     * @return QplQstLome
     */
    public function setAnswerType($answerType)
    {
        $this->answerType = $answerType;

        return $this;
    }

    /**
     * Get answerType.
     *
     * @return string
     */
    public function getAnswerType()
    {
        return $this->answerType;
    }

    /**
     * Set feedbackSetting.
     *
     * @param int $feedbackSetting
     *
     * @return QplQstLome
     */
    public function setFeedbackSetting($feedbackSetting)
    {
        $this->feedbackSetting = $feedbackSetting;

        return $this;
    }

    /**
     * Get feedbackSetting.
     *
     * @return int
     */
    public function getFeedbackSetting()
    {
        return $this->feedbackSetting;
    }

    /**
     * Set longMenuText.
     *
     * @param string|null $longMenuText
     *
     * @return QplQstLome
     */
    public function setLongMenuText($longMenuText = null)
    {
        $this->longMenuText = $longMenuText;

        return $this;
    }

    /**
     * Get longMenuText.
     *
     * @return string|null
     */
    public function getLongMenuText()
    {
        return $this->longMenuText;
    }

    /**
     * Set minAutoComplete.
     *
     * @param bool|null $minAutoComplete
     *
     * @return QplQstLome
     */
    public function setMinAutoComplete($minAutoComplete = null)
    {
        $this->minAutoComplete = $minAutoComplete;

        return $this;
    }

    /**
     * Get minAutoComplete.
     *
     * @return bool|null
     */
    public function getMinAutoComplete()
    {
        return $this->minAutoComplete;
    }

    /**
     * Set identicalScoring.
     *
     * @param bool|null $identicalScoring
     *
     * @return QplQstLome
     */
    public function setIdenticalScoring($identicalScoring = null)
    {
        $this->identicalScoring = $identicalScoring;

        return $this;
    }

    /**
     * Get identicalScoring.
     *
     * @return bool|null
     */
    public function getIdenticalScoring()
    {
        return $this->identicalScoring;
    }
}
