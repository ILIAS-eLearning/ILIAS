<?php



/**
 * NotificationOsd
 */
class NotificationOsd
{
    /**
     * @var int
     */
    private $notificationOsdId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string
     */
    private $serialized = '';

    /**
     * @var int
     */
    private $validUntil = '0';

    /**
     * @var int
     */
    private $timeAdded = '0';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var int
     */
    private $visibleFor = '0';


    /**
     * Get notificationOsdId.
     *
     * @return int
     */
    public function getNotificationOsdId()
    {
        return $this->notificationOsdId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return NotificationOsd
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set serialized.
     *
     * @param string $serialized
     *
     * @return NotificationOsd
     */
    public function setSerialized($serialized)
    {
        $this->serialized = $serialized;

        return $this;
    }

    /**
     * Get serialized.
     *
     * @return string
     */
    public function getSerialized()
    {
        return $this->serialized;
    }

    /**
     * Set validUntil.
     *
     * @param int $validUntil
     *
     * @return NotificationOsd
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * Get validUntil.
     *
     * @return int
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set timeAdded.
     *
     * @param int $timeAdded
     *
     * @return NotificationOsd
     */
    public function setTimeAdded($timeAdded)
    {
        $this->timeAdded = $timeAdded;

        return $this;
    }

    /**
     * Get timeAdded.
     *
     * @return int
     */
    public function getTimeAdded()
    {
        return $this->timeAdded;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return NotificationOsd
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
     * Set visibleFor.
     *
     * @param int $visibleFor
     *
     * @return NotificationOsd
     */
    public function setVisibleFor($visibleFor)
    {
        $this->visibleFor = $visibleFor;

        return $this;
    }

    /**
     * Get visibleFor.
     *
     * @return int
     */
    public function getVisibleFor()
    {
        return $this->visibleFor;
    }
}
