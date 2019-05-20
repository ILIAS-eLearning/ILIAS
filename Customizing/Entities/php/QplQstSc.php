<?php



/**
 * QplQstSc
 */
class QplQstSc
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
     * @var bool|null
     */
    private $feedbackSetting = '2';


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
     * @return QplQstSc
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
     * @return QplQstSc
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
     * @return QplQstSc
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
     * @return QplQstSc
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
     * @param bool|null $feedbackSetting
     *
     * @return QplQstSc
     */
    public function setFeedbackSetting($feedbackSetting = null)
    {
        $this->feedbackSetting = $feedbackSetting;

        return $this;
    }

    /**
     * Get feedbackSetting.
     *
     * @return bool|null
     */
    public function getFeedbackSetting()
    {
        return $this->feedbackSetting;
    }
}
