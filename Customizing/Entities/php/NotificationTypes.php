<?php



/**
 * NotificationTypes
 */
class NotificationTypes
{
    /**
     * @var string
     */
    private $typeName = '';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $notificationGroup = '';

    /**
     * @var string
     */
    private $configType = '';


    /**
     * Get typeName.
     *
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return NotificationTypes
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return NotificationTypes
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set notificationGroup.
     *
     * @param string $notificationGroup
     *
     * @return NotificationTypes
     */
    public function setNotificationGroup($notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;

        return $this;
    }

    /**
     * Get notificationGroup.
     *
     * @return string
     */
    public function getNotificationGroup()
    {
        return $this->notificationGroup;
    }

    /**
     * Set configType.
     *
     * @param string $configType
     *
     * @return NotificationTypes
     */
    public function setConfigType($configType)
    {
        $this->configType = $configType;

        return $this;
    }

    /**
     * Get configType.
     *
     * @return string
     */
    public function getConfigType()
    {
        return $this->configType;
    }
}
