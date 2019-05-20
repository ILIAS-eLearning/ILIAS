<?php



/**
 * DesktopItem
 */
class DesktopItem
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $itemId = '0';

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $parameters;


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return DesktopItem
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
     * Set itemId.
     *
     * @param int $itemId
     *
     * @return DesktopItem
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * Get itemId.
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return DesktopItem
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set parameters.
     *
     * @param string|null $parameters
     *
     * @return DesktopItem
     */
    public function setParameters($parameters = null)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters.
     *
     * @return string|null
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
