<?php



/**
 * NotificationData
 */
class NotificationData
{
    /**
     * @var int
     */
    private $notificationId = '0';

    /**
     * @var string
     */
    private $serialized = '';


    /**
     * Get notificationId.
     *
     * @return int
     */
    public function getNotificationId()
    {
        return $this->notificationId;
    }

    /**
     * Set serialized.
     *
     * @param string $serialized
     *
     * @return NotificationData
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
}
