<?php



/**
 * QplQstKprim
 */
class QplQstKprim
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
     * @var int|null
     */
    private $thumbSize;

    /**
     * @var string
     */
    private $optLabel = 'right/wrong';

    /**
     * @var string|null
     */
    private $customTrue;

    /**
     * @var string|null
     */
    private $customFalse;

    /**
     * @var bool
     */
    private $scorePartsol = '0';

    /**
     * @var int
     */
    private $feedbackSetting = '1';


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
     * @return QplQstKprim
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
     * @return QplQstKprim
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
     * Set thumbSize.
     *
     * @param int|null $thumbSize
     *
     * @return QplQstKprim
     */
    public function setThumbSize($thumbSize = null)
    {
        $this->thumbSize = $thumbSize;

        return $this;
    }

    /**
     * Get thumbSize.
     *
     * @return int|null
     */
    public function getThumbSize()
    {
        return $this->thumbSize;
    }

    /**
     * Set optLabel.
     *
     * @param string $optLabel
     *
     * @return QplQstKprim
     */
    public function setOptLabel($optLabel)
    {
        $this->optLabel = $optLabel;

        return $this;
    }

    /**
     * Get optLabel.
     *
     * @return string
     */
    public function getOptLabel()
    {
        return $this->optLabel;
    }

    /**
     * Set customTrue.
     *
     * @param string|null $customTrue
     *
     * @return QplQstKprim
     */
    public function setCustomTrue($customTrue = null)
    {
        $this->customTrue = $customTrue;

        return $this;
    }

    /**
     * Get customTrue.
     *
     * @return string|null
     */
    public function getCustomTrue()
    {
        return $this->customTrue;
    }

    /**
     * Set customFalse.
     *
     * @param string|null $customFalse
     *
     * @return QplQstKprim
     */
    public function setCustomFalse($customFalse = null)
    {
        $this->customFalse = $customFalse;

        return $this;
    }

    /**
     * Get customFalse.
     *
     * @return string|null
     */
    public function getCustomFalse()
    {
        return $this->customFalse;
    }

    /**
     * Set scorePartsol.
     *
     * @param bool $scorePartsol
     *
     * @return QplQstKprim
     */
    public function setScorePartsol($scorePartsol)
    {
        $this->scorePartsol = $scorePartsol;

        return $this;
    }

    /**
     * Get scorePartsol.
     *
     * @return bool
     */
    public function getScorePartsol()
    {
        return $this->scorePartsol;
    }

    /**
     * Set feedbackSetting.
     *
     * @param int $feedbackSetting
     *
     * @return QplQstKprim
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
}
