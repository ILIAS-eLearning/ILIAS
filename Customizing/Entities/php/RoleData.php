<?php



/**
 * RoleData
 */
class RoleData
{
    /**
     * @var int
     */
    private $roleId = '0';

    /**
     * @var bool
     */
    private $allowRegister = '0';

    /**
     * @var bool|null
     */
    private $assignUsers = '0';

    /**
     * @var string|null
     */
    private $authMode = 'default';

    /**
     * @var int
     */
    private $diskQuota = '0';

    /**
     * @var int|null
     */
    private $wspDiskQuota;


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
     * Set allowRegister.
     *
     * @param bool $allowRegister
     *
     * @return RoleData
     */
    public function setAllowRegister($allowRegister)
    {
        $this->allowRegister = $allowRegister;

        return $this;
    }

    /**
     * Get allowRegister.
     *
     * @return bool
     */
    public function getAllowRegister()
    {
        return $this->allowRegister;
    }

    /**
     * Set assignUsers.
     *
     * @param bool|null $assignUsers
     *
     * @return RoleData
     */
    public function setAssignUsers($assignUsers = null)
    {
        $this->assignUsers = $assignUsers;

        return $this;
    }

    /**
     * Get assignUsers.
     *
     * @return bool|null
     */
    public function getAssignUsers()
    {
        return $this->assignUsers;
    }

    /**
     * Set authMode.
     *
     * @param string|null $authMode
     *
     * @return RoleData
     */
    public function setAuthMode($authMode = null)
    {
        $this->authMode = $authMode;

        return $this;
    }

    /**
     * Get authMode.
     *
     * @return string|null
     */
    public function getAuthMode()
    {
        return $this->authMode;
    }

    /**
     * Set diskQuota.
     *
     * @param int $diskQuota
     *
     * @return RoleData
     */
    public function setDiskQuota($diskQuota)
    {
        $this->diskQuota = $diskQuota;

        return $this;
    }

    /**
     * Get diskQuota.
     *
     * @return int
     */
    public function getDiskQuota()
    {
        return $this->diskQuota;
    }

    /**
     * Set wspDiskQuota.
     *
     * @param int|null $wspDiskQuota
     *
     * @return RoleData
     */
    public function setWspDiskQuota($wspDiskQuota = null)
    {
        $this->wspDiskQuota = $wspDiskQuota;

        return $this;
    }

    /**
     * Get wspDiskQuota.
     *
     * @return int|null
     */
    public function getWspDiskQuota()
    {
        return $this->wspDiskQuota;
    }
}
