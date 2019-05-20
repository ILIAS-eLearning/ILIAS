<?php



/**
 * IlBlockSetting
 */
class IlBlockSetting
{
    /**
     * @var string
     */
    private $type = ' ';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $blockId = '0';

    /**
     * @var string
     */
    private $setting = ' ';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set type.
     *
     * @param string $type
     *
     * @return IlBlockSetting
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlBlockSetting
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
     * Set blockId.
     *
     * @param int $blockId
     *
     * @return IlBlockSetting
     */
    public function setBlockId($blockId)
    {
        $this->blockId = $blockId;

        return $this;
    }

    /**
     * Get blockId.
     *
     * @return int
     */
    public function getBlockId()
    {
        return $this->blockId;
    }

    /**
     * Set setting.
     *
     * @param string $setting
     *
     * @return IlBlockSetting
     */
    public function setSetting($setting)
    {
        $this->setting = $setting;

        return $this;
    }

    /**
     * Get setting.
     *
     * @return string
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return IlBlockSetting
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
