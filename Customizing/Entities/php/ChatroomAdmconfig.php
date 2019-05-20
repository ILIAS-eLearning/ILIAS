<?php



/**
 * ChatroomAdmconfig
 */
class ChatroomAdmconfig
{
    /**
     * @var int
     */
    private $instanceId = '0';

    /**
     * @var string
     */
    private $serverSettings = '';

    /**
     * @var bool
     */
    private $defaultConfig = '0';

    /**
     * @var string
     */
    private $clientSettings = '';


    /**
     * Get instanceId.
     *
     * @return int
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * Set serverSettings.
     *
     * @param string $serverSettings
     *
     * @return ChatroomAdmconfig
     */
    public function setServerSettings($serverSettings)
    {
        $this->serverSettings = $serverSettings;

        return $this;
    }

    /**
     * Get serverSettings.
     *
     * @return string
     */
    public function getServerSettings()
    {
        return $this->serverSettings;
    }

    /**
     * Set defaultConfig.
     *
     * @param bool $defaultConfig
     *
     * @return ChatroomAdmconfig
     */
    public function setDefaultConfig($defaultConfig)
    {
        $this->defaultConfig = $defaultConfig;

        return $this;
    }

    /**
     * Get defaultConfig.
     *
     * @return bool
     */
    public function getDefaultConfig()
    {
        return $this->defaultConfig;
    }

    /**
     * Set clientSettings.
     *
     * @param string $clientSettings
     *
     * @return ChatroomAdmconfig
     */
    public function setClientSettings($clientSettings)
    {
        $this->clientSettings = $clientSettings;

        return $this;
    }

    /**
     * Get clientSettings.
     *
     * @return string
     */
    public function getClientSettings()
    {
        return $this->clientSettings;
    }
}
