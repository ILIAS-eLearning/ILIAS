<?php



/**
 * QplHintTracking
 */
class QplHintTracking
{
    /**
     * @var int
     */
    private $qhtrTrackId = '0';

    /**
     * @var int
     */
    private $qhtrActiveFi = '0';

    /**
     * @var int
     */
    private $qhtrPass = '0';

    /**
     * @var int
     */
    private $qhtrQuestionFi = '0';

    /**
     * @var int
     */
    private $qhtrHintFi = '0';


    /**
     * Get qhtrTrackId.
     *
     * @return int
     */
    public function getQhtrTrackId()
    {
        return $this->qhtrTrackId;
    }

    /**
     * Set qhtrActiveFi.
     *
     * @param int $qhtrActiveFi
     *
     * @return QplHintTracking
     */
    public function setQhtrActiveFi($qhtrActiveFi)
    {
        $this->qhtrActiveFi = $qhtrActiveFi;

        return $this;
    }

    /**
     * Get qhtrActiveFi.
     *
     * @return int
     */
    public function getQhtrActiveFi()
    {
        return $this->qhtrActiveFi;
    }

    /**
     * Set qhtrPass.
     *
     * @param int $qhtrPass
     *
     * @return QplHintTracking
     */
    public function setQhtrPass($qhtrPass)
    {
        $this->qhtrPass = $qhtrPass;

        return $this;
    }

    /**
     * Get qhtrPass.
     *
     * @return int
     */
    public function getQhtrPass()
    {
        return $this->qhtrPass;
    }

    /**
     * Set qhtrQuestionFi.
     *
     * @param int $qhtrQuestionFi
     *
     * @return QplHintTracking
     */
    public function setQhtrQuestionFi($qhtrQuestionFi)
    {
        $this->qhtrQuestionFi = $qhtrQuestionFi;

        return $this;
    }

    /**
     * Get qhtrQuestionFi.
     *
     * @return int
     */
    public function getQhtrQuestionFi()
    {
        return $this->qhtrQuestionFi;
    }

    /**
     * Set qhtrHintFi.
     *
     * @param int $qhtrHintFi
     *
     * @return QplHintTracking
     */
    public function setQhtrHintFi($qhtrHintFi)
    {
        $this->qhtrHintFi = $qhtrHintFi;

        return $this;
    }

    /**
     * Get qhtrHintFi.
     *
     * @return int
     */
    public function getQhtrHintFi()
    {
        return $this->qhtrHintFi;
    }
}
