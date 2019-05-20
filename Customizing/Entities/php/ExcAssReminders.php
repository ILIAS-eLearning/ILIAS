<?php



/**
 * ExcAssReminders
 */
class ExcAssReminders
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $assId;

    /**
     * @var int
     */
    private $excId;

    /**
     * @var bool|null
     */
    private $status;

    /**
     * @var int|null
     */
    private $start;

    /**
     * @var int|null
     */
    private $end;

    /**
     * @var int|null
     */
    private $freq;

    /**
     * @var int|null
     */
    private $lastSend;

    /**
     * @var int|null
     */
    private $templateId;


    /**
     * Set type.
     *
     * @param string $type
     *
     * @return ExcAssReminders
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set assId.
     *
     * @param int $assId
     *
     * @return ExcAssReminders
     */
    public function setAssId($assId)
    {
        $this->assId = $assId;

        return $this;
    }

    /**
     * Get assId.
     *
     * @return int
     */
    public function getAssId()
    {
        return $this->assId;
    }

    /**
     * Set excId.
     *
     * @param int $excId
     *
     * @return ExcAssReminders
     */
    public function setExcId($excId)
    {
        $this->excId = $excId;

        return $this;
    }

    /**
     * Get excId.
     *
     * @return int
     */
    public function getExcId()
    {
        return $this->excId;
    }

    /**
     * Set status.
     *
     * @param bool|null $status
     *
     * @return ExcAssReminders
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set start.
     *
     * @param int|null $start
     *
     * @return ExcAssReminders
     */
    public function setStart($start = null)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start.
     *
     * @return int|null
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end.
     *
     * @param int|null $end
     *
     * @return ExcAssReminders
     */
    public function setEnd($end = null)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end.
     *
     * @return int|null
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set freq.
     *
     * @param int|null $freq
     *
     * @return ExcAssReminders
     */
    public function setFreq($freq = null)
    {
        $this->freq = $freq;

        return $this;
    }

    /**
     * Get freq.
     *
     * @return int|null
     */
    public function getFreq()
    {
        return $this->freq;
    }

    /**
     * Set lastSend.
     *
     * @param int|null $lastSend
     *
     * @return ExcAssReminders
     */
    public function setLastSend($lastSend = null)
    {
        $this->lastSend = $lastSend;

        return $this;
    }

    /**
     * Get lastSend.
     *
     * @return int|null
     */
    public function getLastSend()
    {
        return $this->lastSend;
    }

    /**
     * Set templateId.
     *
     * @param int|null $templateId
     *
     * @return ExcAssReminders
     */
    public function setTemplateId($templateId = null)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * Get templateId.
     *
     * @return int|null
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }
}
