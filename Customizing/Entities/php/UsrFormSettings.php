<?php



/**
 * UsrFormSettings
 */
class UsrFormSettings
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string
     */
    private $settings = '';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return UsrFormSettings
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set id.
     *
     * @param string $id
     *
     * @return UsrFormSettings
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set settings.
     *
     * @param string $settings
     *
     * @return UsrFormSettings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings.
     *
     * @return string
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
