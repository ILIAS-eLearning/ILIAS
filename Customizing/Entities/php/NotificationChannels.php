<?php



/**
 * NotificationChannels
 */
class NotificationChannels
{
    /**
     * @var string
     */
    private $channelName = '';

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
    private $class = '';

    /**
     * @var string
     */
    private $include = '';

    /**
     * @var string
     */
    private $configType = '';


    /**
     * Get channelName.
     *
     * @return string
     */
    public function getChannelName()
    {
        return $this->channelName;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return NotificationChannels
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
     * @return NotificationChannels
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
     * Set class.
     *
     * @param string $class
     *
     * @return NotificationChannels
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set include.
     *
     * @param string $include
     *
     * @return NotificationChannels
     */
    public function setInclude($include)
    {
        $this->include = $include;

        return $this;
    }

    /**
     * Get include.
     *
     * @return string
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * Set configType.
     *
     * @param string $configType
     *
     * @return NotificationChannels
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
