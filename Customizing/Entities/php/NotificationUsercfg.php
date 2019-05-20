<?php



/**
 * NotificationUsercfg
 */
class NotificationUsercfg
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string
     */
    private $module = '';

    /**
     * @var string
     */
    private $channel = '';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return NotificationUsercfg
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
     * Set module.
     *
     * @param string $module
     *
     * @return NotificationUsercfg
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module.
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set channel.
     *
     * @param string $channel
     *
     * @return NotificationUsercfg
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get channel.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
