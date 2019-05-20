<?php



/**
 * QplQstCloze
 */
class QplQstCloze
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $textgapRating;

    /**
     * @var string|null
     */
    private $identicalScoring = '1';

    /**
     * @var int|null
     */
    private $fixedTextlen;

    /**
     * @var string|null
     */
    private $clozeText;

    /**
     * @var string
     */
    private $feedbackMode = 'gapQuestion';


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
     * Set textgapRating.
     *
     * @param string|null $textgapRating
     *
     * @return QplQstCloze
     */
    public function setTextgapRating($textgapRating = null)
    {
        $this->textgapRating = $textgapRating;

        return $this;
    }

    /**
     * Get textgapRating.
     *
     * @return string|null
     */
    public function getTextgapRating()
    {
        return $this->textgapRating;
    }

    /**
     * Set identicalScoring.
     *
     * @param string|null $identicalScoring
     *
     * @return QplQstCloze
     */
    public function setIdenticalScoring($identicalScoring = null)
    {
        $this->identicalScoring = $identicalScoring;

        return $this;
    }

    /**
     * Get identicalScoring.
     *
     * @return string|null
     */
    public function getIdenticalScoring()
    {
        return $this->identicalScoring;
    }

    /**
     * Set fixedTextlen.
     *
     * @param int|null $fixedTextlen
     *
     * @return QplQstCloze
     */
    public function setFixedTextlen($fixedTextlen = null)
    {
        $this->fixedTextlen = $fixedTextlen;

        return $this;
    }

    /**
     * Get fixedTextlen.
     *
     * @return int|null
     */
    public function getFixedTextlen()
    {
        return $this->fixedTextlen;
    }

    /**
     * Set clozeText.
     *
     * @param string|null $clozeText
     *
     * @return QplQstCloze
     */
    public function setClozeText($clozeText = null)
    {
        $this->clozeText = $clozeText;

        return $this;
    }

    /**
     * Get clozeText.
     *
     * @return string|null
     */
    public function getClozeText()
    {
        return $this->clozeText;
    }

    /**
     * Set feedbackMode.
     *
     * @param string $feedbackMode
     *
     * @return QplQstCloze
     */
    public function setFeedbackMode($feedbackMode)
    {
        $this->feedbackMode = $feedbackMode;

        return $this;
    }

    /**
     * Get feedbackMode.
     *
     * @return string
     */
    public function getFeedbackMode()
    {
        return $this->feedbackMode;
    }
}
