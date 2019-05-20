<?php



/**
 * NotificationListener
 */
class NotificationListener
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
     * @var int
     */
    private $senderId = '0';

    /**
     * @var bool
     */
    private $disabled = '0';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return NotificationListener
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
     * @return NotificationListener
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
     * Set senderId.
     *
     * @param int $senderId
     *
     * @return NotificationListener
     */
    public function setSenderId($senderId)
    {
        $this->senderId = $senderId;

        return $this;
    }

    /**
     * Get senderId.
     *
     * @return int
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * Set disabled.
     *
     * @param bool $disabled
     *
     * @return NotificationListener
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Get disabled.
     *
     * @return bool
     */
    public function getDisabled()
    {
        return $this->disabled;
    }
}
