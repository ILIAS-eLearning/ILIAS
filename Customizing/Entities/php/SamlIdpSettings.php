<?php



/**
 * SamlIdpSettings
 */
class SamlIdpSettings
{
    /**
     * @var int
     */
    private $idpId = '0';

    /**
     * @var bool
     */
    private $isActive = '0';

    /**
     * @var bool
     */
    private $allowLocalAuth = '0';

    /**
     * @var int
     */
    private $defaultRoleId = '0';

    /**
     * @var string|null
     */
    private $uidClaim;

    /**
     * @var string|null
     */
    private $loginClaim;

    /**
     * @var bool
     */
    private $syncStatus = '0';

    /**
     * @var bool
     */
    private $accountMigrStatus = '0';

    /**
     * @var string|null
     */
    private $entityId;


    /**
     * Get idpId.
     *
     * @return int
     */
    public function getIdpId()
    {
        return $this->idpId;
    }

    /**
     * Set isActive.
     *
     * @param bool $isActive
     *
     * @return SamlIdpSettings
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set allowLocalAuth.
     *
     * @param bool $allowLocalAuth
     *
     * @return SamlIdpSettings
     */
    public function setAllowLocalAuth($allowLocalAuth)
    {
        $this->allowLocalAuth = $allowLocalAuth;

        return $this;
    }

    /**
     * Get allowLocalAuth.
     *
     * @return bool
     */
    public function getAllowLocalAuth()
    {
        return $this->allowLocalAuth;
    }

    /**
     * Set defaultRoleId.
     *
     * @param int $defaultRoleId
     *
     * @return SamlIdpSettings
     */
    public function setDefaultRoleId($defaultRoleId)
    {
        $this->defaultRoleId = $defaultRoleId;

        return $this;
    }

    /**
     * Get defaultRoleId.
     *
     * @return int
     */
    public function getDefaultRoleId()
    {
        return $this->defaultRoleId;
    }

    /**
     * Set uidClaim.
     *
     * @param string|null $uidClaim
     *
     * @return SamlIdpSettings
     */
    public function setUidClaim($uidClaim = null)
    {
        $this->uidClaim = $uidClaim;

        return $this;
    }

    /**
     * Get uidClaim.
     *
     * @return string|null
     */
    public function getUidClaim()
    {
        return $this->uidClaim;
    }

    /**
     * Set loginClaim.
     *
     * @param string|null $loginClaim
     *
     * @return SamlIdpSettings
     */
    public function setLoginClaim($loginClaim = null)
    {
        $this->loginClaim = $loginClaim;

        return $this;
    }

    /**
     * Get loginClaim.
     *
     * @return string|null
     */
    public function getLoginClaim()
    {
        return $this->loginClaim;
    }

    /**
     * Set syncStatus.
     *
     * @param bool $syncStatus
     *
     * @return SamlIdpSettings
     */
    public function setSyncStatus($syncStatus)
    {
        $this->syncStatus = $syncStatus;

        return $this;
    }

    /**
     * Get syncStatus.
     *
     * @return bool
     */
    public function getSyncStatus()
    {
        return $this->syncStatus;
    }

    /**
     * Set accountMigrStatus.
     *
     * @param bool $accountMigrStatus
     *
     * @return SamlIdpSettings
     */
    public function setAccountMigrStatus($accountMigrStatus)
    {
        $this->accountMigrStatus = $accountMigrStatus;

        return $this;
    }

    /**
     * Get accountMigrStatus.
     *
     * @return bool
     */
    public function getAccountMigrStatus()
    {
        return $this->accountMigrStatus;
    }

    /**
     * Set entityId.
     *
     * @param string|null $entityId
     *
     * @return SamlIdpSettings
     */
    public function setEntityId($entityId = null)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId.
     *
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
}
