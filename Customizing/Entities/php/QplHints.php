<?php



/**
 * QplHints
 */
class QplHints
{
    /**
     * @var int
     */
    private $qhtHintId = '0';

    /**
     * @var int
     */
    private $qhtQuestionFi = '0';

    /**
     * @var int
     */
    private $qhtHintIndex = '0';

    /**
     * @var float
     */
    private $qhtHintPoints = '0';

    /**
     * @var string|null
     */
    private $qhtHintText;


    /**
     * Get qhtHintId.
     *
     * @return int
     */
    public function getQhtHintId()
    {
        return $this->qhtHintId;
    }

    /**
     * Set qhtQuestionFi.
     *
     * @param int $qhtQuestionFi
     *
     * @return QplHints
     */
    public function setQhtQuestionFi($qhtQuestionFi)
    {
        $this->qhtQuestionFi = $qhtQuestionFi;

        return $this;
    }

    /**
     * Get qhtQuestionFi.
     *
     * @return int
     */
    public function getQhtQuestionFi()
    {
        return $this->qhtQuestionFi;
    }

    /**
     * Set qhtHintIndex.
     *
     * @param int $qhtHintIndex
     *
     * @return QplHints
     */
    public function setQhtHintIndex($qhtHintIndex)
    {
        $this->qhtHintIndex = $qhtHintIndex;

        return $this;
    }

    /**
     * Get qhtHintIndex.
     *
     * @return int
     */
    public function getQhtHintIndex()
    {
        return $this->qhtHintIndex;
    }

    /**
     * Set qhtHintPoints.
     *
     * @param float $qhtHintPoints
     *
     * @return QplHints
     */
    public function setQhtHintPoints($qhtHintPoints)
    {
        $this->qhtHintPoints = $qhtHintPoints;

        return $this;
    }

    /**
     * Get qhtHintPoints.
     *
     * @return float
     */
    public function getQhtHintPoints()
    {
        return $this->qhtHintPoints;
    }

    /**
     * Set qhtHintText.
     *
     * @param string|null $qhtHintText
     *
     * @return QplHints
     */
    public function setQhtHintText($qhtHintText = null)
    {
        $this->qhtHintText = $qhtHintText;

        return $this;
    }

    /**
     * Get qhtHintText.
     *
     * @return string|null
     */
    public function getQhtHintText()
    {
        return $this->qhtHintText;
    }
}
