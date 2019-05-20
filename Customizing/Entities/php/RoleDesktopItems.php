<?php



/**
 * RoleDesktopItems
 */
class RoleDesktopItems
{
    /**
     * @var int
     */
    private $roleItemId = '0';

    /**
     * @var int
     */
    private $roleId = '0';

    /**
     * @var int
     */
    private $itemId = '0';

    /**
     * @var string|null
     */
    private $itemType;


    /**
     * Get roleItemId.
     *
     * @return int
     */
    public function getRoleItemId()
    {
        return $this->roleItemId;
    }

    /**
     * Set roleId.
     *
     * @param int $roleId
     *
     * @return RoleDesktopItems
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Get roleId.
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set itemId.
     *
     * @param int $itemId
     *
     * @return RoleDesktopItems
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
     * Set itemType.
     *
     * @param string|null $itemType
     *
     * @return RoleDesktopItems
     */
    public function setItemType($itemType = null)
    {
        $this->itemType = $itemType;

        return $this;
    }

    /**
     * Get itemType.
     *
     * @return string|null
     */
    public function getItemType()
    {
        return $this->itemType;
    }
}
