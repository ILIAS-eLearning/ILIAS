<?php



/**
 * QplQstMc
 */
class QplQstMc
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $shuffle = '1';

    /**
     * @var string|null
     */
    private $allowImages = '0';

    /**
     * @var string|null
     */
    private $resizeImages = '0';

    /**
     * @var int|null
     */
    private $thumbSize;

    /**
     * @var bool
     */
    private $feedbackSetting = '1';

    /**
     * @var int|null
     */
    private $selectionLimit;


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
     * Set shuffle.
     *
     * @param string|null $shuffle
     *
     * @return QplQstMc
     */
    public function setShuffle($shuffle = null)
    {
        $this->shuffle = $shuffle;

        return $this;
    }

    /**
     * Get shuffle.
     *
     * @return string|null
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * Set allowImages.
     *
     * @param string|null $allowImages
     *
     * @return QplQstMc
     */
    public function setAllowImages($allowImages = null)
    {
        $this->allowImages = $allowImages;

        return $this;
    }

    /**
     * Get allowImages.
     *
     * @return string|null
     */
    public function getAllowImages()
    {
        return $this->allowImages;
    }

    /**
     * Set resizeImages.
     *
     * @param string|null $resizeImages
     *
     * @return QplQstMc
     */
    public function setResizeImages($resizeImages = null)
    {
        $this->resizeImages = $resizeImages;

        return $this;
    }

    /**
     * Get resizeImages.
     *
     * @return string|null
     */
    public function getResizeImages()
    {
        return $this->resizeImages;
    }

    /**
     * Set thumbSize.
     *
     * @param int|null $thumbSize
     *
     * @return QplQstMc
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
     * Set feedbackSetting.
     *
     * @param bool $feedbackSetting
     *
     * @return QplQstMc
     */
    public function setFeedbackSetting($feedbackSetting)
    {
        $this->feedbackSetting = $feedbackSetting;

        return $this;
    }

    /**
     * Get feedbackSetting.
     *
     * @return bool
     */
    public function getFeedbackSetting()
    {
        return $this->feedbackSetting;
    }

    /**
     * Set selectionLimit.
     *
     * @param int|null $selectionLimit
     *
     * @return QplQstMc
     */
    public function setSelectionLimit($selectionLimit = null)
    {
        $this->selectionLimit = $selectionLimit;

        return $this;
    }

    /**
     * Get selectionLimit.
     *
     * @return int|null
     */
    public function getSelectionLimit()
    {
        return $this->selectionLimit;
    }
}
